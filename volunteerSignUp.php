<?php 
include_once("common_utils/functions.php");

define("admin", 0);
define("norm_users", 1);
$config = parse_ini_file("conf/citc_config.ini");
define("displaySize", $config["pagination_size"]);

/**
 * An Object used to create the volunteer application
 *
 * @author Woody Romelus
 */
class VolunteerAppCreator {

    public $connection;

    /**
     * Default Constructor
     */
    public function __construct() {
        include("citc_db.php");
        $this->connection = $conn;

        // If the tables necessary are not there we construct them
        if(!$this->connection->table_exists("pass_rec")) {
            $this->buildVolunteerMgmtTables();
        }
    }

    /**
     * .........................................................................
     * ......................... Functional Methods ............................
     * .........................................................................
     */

    /**
     * Creates the relevant volunteer specific tables, which stores information
     * about (Volunteer times, Contact info, Auditing, etc)
     */
    function buildVolunteerMgmtTables() {
       // TABLES
       $this->connection->runQuery("CREATE TABLE IF NOT EXISTS `pass_rec` (
           `rec_id` int NOT NULL AUTO_INCREMENT,
           `email` varchar(60) NOT NULL,
           `keyval` varchar(32) NOT NULL,
           `expires` datetime NOT NULL,
           UNIQUE KEY `distinct_users_email` (`email`),
           PRIMARY KEY (`rec_id`))");

       $this->connection->runQuery("CREATE TABLE IF NOT EXISTS `volunteers` (
           `vol_id` int NOT NULL AUTO_INCREMENT,
           `fname` varchar(20) NOT NULL,
           `lname` varchar(20) NOT NULL,
           `email` varchar(254) NOT NULL,
           `phone` varchar(10) NOT NULL,
           `volunteer_day` date NOT NULL,
           `time_in` time NOT NULL,
           `time_out` time NOT NULL,
           `accepted` tinyint NOT NULL DEFAULT -1,
           `is_group` tinyint NOT NULL DEFAULT 0,
           `group_size` int NOT NULL DEFAULT 0,
           `position` varchar(100) NOT NULL,
           UNIQUE KEY `distinct_users_key` (`email`, `volunteer_day`),
           PRIMARY KEY (`vol_id`))");

       $this->connection->runQuery("CREATE TABLE IF NOT EXISTS `volunteer_audit` (
           `aud_id` int NOT NULL AUTO_INCREMENT,
           `vol_day` date NOT NULL,
           `curr_registered` int NOT NULL DEFAULT 0,
           `max_registered` int NOT NULL DEFAULT 100,
           PRIMARY KEY (`aud_id`),
       UNIQUE KEY `vol_day` (`vol_day`))");

       $this->connection->runQuery("CREATE TABLE IF NOT EXISTS `users` (
           `uid` int NOT NULL AUTO_INCREMENT,
           `fname` varchar(100) NOT NULL,
           `lname` varchar(100) NOT NULL,
           `email` varchar(60) NOT NULL,
           `password` varchar(60) NOT NULL,
           `security_q` varchar(70) NOT NULL,
           `security_a` varchar(30) NOT NULL,
           `created` datetime NOT NULL,
           `lastloggedin` datetime NOT NULL,
           `token` varchar(60) NOT NULL DEFAULT '',
           `usertype` int NOT NULL DEFAULT " .norm_users. ",
           PRIMARY KEY (`uid`),
       UNIQUE KEY `email` (`email`))");

       $this->connection->runQuery("CREATE TABLE IF NOT EXISTS `volunteer_positions` (
           `pid` int NOT NULL AUTO_INCREMENT,
           `title` varchar(100) NOT NULL,
           `description` varchar(100) NOT NULL DEFAULT '',
           `max_users` int NOT NULL DEFAULT '0',
           `date` date NOT NULL,
           PRIMARY KEY (`pid`),
           UNIQUE KEY `distinct_positions (`titls`, `date`))");
       
       // TRIGGERS
       // PHP 5.4.10 does not support the use of 'DELIMITER' such that we can
       // combine the queries, hence we break the queries into separate ones
       $this->connection->runQuery("DROP TRIGGER IF EXISTS `vol_insert_trigger`;");
       $this->connection->runQuery("CREATE TRIGGER `vol_insert_trigger` BEFORE INSERT ON volunteers 
           FOR EACH ROW BEGIN
               DECLARE `curr_reg` INT; 
               DECLARE `max_reg` INT; 

               IF ((SELECT (SELECT vol_day FROM `volunteer_audit` WHERE vol_day = NEW.volunteer_day) as validDates) IS NULL) THEN
                   SIGNAL SQLSTATE '70000'
                   SET MESSAGE_TEXT = 'The specified party date is not a registered party date.';
               END IF;

               SET `curr_reg` = (SELECT curr_registered FROM `volunteer_audit` WHERE vol_day = new.volunteer_day);
               SET `max_reg` = (SELECT max_registered FROM `volunteer_audit` WHERE vol_day = new.volunteer_day);

               IF( (`curr_reg` + 1) <= `max_reg`) THEN 
                   UPDATE volunteer_audit 
                   SET    curr_registered = curr_registered + 1 
                   WHERE  vol_day = new.volunteer_day;
               ELSE
                   SIGNAL SQLSTATE '70001'
                   SET MESSAGE_TEXT = 'Party is Full of volunteers.';
               END IF;
           END;");

       $this->connection->runQuery("DROP TRIGGER IF EXISTS `vol_delete_trigger`;");
       $this->connection->runQuery("CREATE TRIGGER `vol_delete_trigger` BEFORE DELETE ON volunteers 
           FOR EACH row BEGIN
               UPDATE volunteer_audit 
               SET    curr_registered = curr_registered - 1 
               WHERE  vol_day = old.volunteer_day; 
           END;");
    }

    /**
     * Creates a new entry for the new user
     *
     * @param (String) $fname the users first name
     * @param (String) $lname the users last name
     * @param (String) $email the users email address
     * @param (String) $passw the users password
     * @param (String) $secQ the users security question
     * @param (String) $secA the users security answer
     * @param (String) $token the users token
     *
     * @return (MySqli_Result) the result of the query
     */
    function insertNewUser($fname, $lname, $email, $passw, $secQ, $secA, $token) {

        $now = date("Y-m-d H:i:s");
        $pass = Utils::hashPassword($passw);
        return $this->connection->runPreparedQuery("INSERT INTO users (fname,
            lname, email, password, security_q, security_a, created, lastloggedin, token) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", array($fname, $lname, $email,
                $pass, $secQ, $secA, $now, $now, $token));
    }

    /**
     * Checks if a user exists with the proper credentials
     *
     * @param (String) $uemail the users email address
     * @param (String) $upassword the users password
     * @return flag indicating if the user is proper
     */
    function userIsValid($uemail, $upass) {
        // Sanitize the user input
        $uemail = $this->connection->cleanSQLInputs($uemail);
        $upass = $this->connection->cleanSQLInputs($upass);
        $upass = Utils::hashPassword($upass); 

        // If there is at least a user with that email we can continue prodding
        if($this->findUser($uemail)) {
            // Find a match for the email and password
            $result = $this->connection->runQuery("SELECT email, password 
                FROM   users
                WHERE  email = '$uemail'
                AND password = '$upass'
                LIMIT 1");
            return $result->num_rows == 1;
        }
    }

    /**
     * Determines if a user is a registered user based on a unique token value
     *
     * @param (String) $uemail the email for the user.
     * @param (String) $utoken the token for the user.
     * @return a flag indicating if the user is registered.
     */
    function userTokenIsValid($uemail, $utoken) {
        // Sanitize the input value
        $uemail = $this->connection->cleanSQLInputs($uemail);
        $utoken = $this->connection->cleanSQLInputs($utoken);

        return $this->connection->runQuery("SELECT count(*) 
            FROM   users 
            WHERE  email = '$uemail'
            AND token = '$utoken'
            LIMIT  1")->fetch_row()[0] == 1;
    }


    /**
     * Retrieves a user's security question and answer
     *
     * @param (String) $email the users email address
     * @return (MySqli_Result) the query result
     */
    function findUserSecuritySelection($email) {
        // Sanitize the input value
        $email = $this->connection->cleanSQLInputs($email);

        return $this->connection->runQuery("SELECT security_q, security_a 
            FROM   users 
            WHERE  email = '$email'
            LIMIT  1");
    }

    /**
     * Updates the users token value
     *
     * @param (String) $uemail the user's email
     * @param (String) $utoken the new token to set
     */
    function updateUserToken($uemail, $utoken) {
        return $this->connection->runPreparedQuery("UPDATE users
            SET    token = ? 
            WHERE  email = ? 
            LIMIT 1", array($utoken, $uemail)); 
    }

    /**
     * Tries to find a user specified by their email address
     *
     * @param (String) $email the user's email address
     * @return (Boolean) flag indicating if user has been found
     */
    function findUser($email) {
        // Sanitize the input value
        $email = $this->connection->cleanSQLInputs($email);

        return $this->connection->runQuery("SELECT count(*) 
            FROM   users 
            WHERE  email = '$email'
            LIMIT  1")->fetch_row()[0] > 0;
    }

    /**
     * Updates the last time the user logged in.
     *
     * @param (String) $uemail the user's email
     */
    function updateUserLastLogin($uemail) {
        $this->connection->runPreparedQuery("UPDATE users 
            SET lastloggedin = '". date("Y-m-d H:i:s")
            . "' WHERE  email = ? LIMIT 1", array($uemail));
    }

    /**
     * Determines if a user has admin priveleges
     *
     * @param (String) $uemail the user's email
     * @return a flag indicating if the user is of admin type
     */
    function isUserAdmin($uemail) {
        // Sanitize the input value
        $uemail = $this->connection->cleanSQLInputs($uemail);

        return $this->connection->runQuery("SELECT usertype
            FROM   users
            WHERE  email = '$uemail'
            LIMIT  1")->fetch_row()[0] == admin;
    }

    /**
     * Displays the events for the current year
     *
     * @return (String) the events list
     */
    function getThisYearsEvents() {
        $return = "";
        $result = $this->connection->runQuery("SELECT vol_day
            FROM volunteer_audit
            WHERE Year(vol_day) = Year(now())");

        while ($row = $result->fetch_row()) {
            $return .= $row[0] . " ";
        }
        return trim($return);
    }

    /**
     * Retrieves all past volunteer event years
     *
     * @return (MySQLi_Result) the result of the query
     */
    function retrievePastEventYears($flag = false) {
        $query = "SELECT DISTINCT vol_day
            FROM volunteer_audit
            WHERE Year(vol_day) < Year(now()) ";
        $query = ($flag == true) ? $query .= "GROUP BY MONTH(vol_day)" : $query;

        return $this->connection->runQuery($query);
    }

    /**
     * Retrieves volunteer dates for a specific year
     *
     * @param (String) $year the year to look for the volunteer dates
     * @return (Array) the list of volunteer dates for the given year
     */
    function retrieveVolunteerDates($year) {
        $result = array();
        $vol_days = $this->retrieveEventDates($year);

        while ($row = $vol_days->fetch_array()) {
            array_push($result, $row["vol_day"]);
        }
        return $result;
    }

    /**
     * Retrieves all of this years volunteer events
     *
     * @param (String) $year the year to filter the dates against
     * @return (MySQLi_Result) the result of the query
     */
    function retrieveEventDates($year) {
        // Sanitize the user input
        $year = $this->connection->cleanSQLInputs($year);

        return $this->connection->runQuery("SELECT DISTINCT vol_day
            FROM volunteer_audit
            WHERE Year(vol_day) = '$year'
            AND curr_registered < max_registered");
    }

    /**
     * Finds all volunteers registered for a specific party date
     *
     * @param (Date) $udate the date to reference
     * @return (MySqli_Result) the query result
     */
    function findRegisteredDateUsers($udate, $strt, $end) {
        // Sanitize the user input
        $udate = $this->connection->cleanSQLInputs($udate);

        return $this->connection->runQuery("SELECT fname, lname, email,
            phone, time_in, time_out, is_group, group_size, position, accepted
            FROM volunteers
            WHERE volunteer_day = '$udate'
            ORDER BY lname LIMIT $strt, $end");
    }

    /**
     * Function description
     *
     * @param (Date) the date to reference the search to
     * @return (Type) Return description
     */
    function retrieveVolPositions($date) {
      return $this->connection->runQuery("SELECT title, description,
          (SELECT COUNT(*)
              FROM volunteers
              WHERE position = title AND volunteer_day = date),
          max_users
          FROM (SELECT title, description, max_users, date
              FROM volunteers
              RIGHT JOIN (volunteer_positions)
              ON volunteers.volunteer_day = volunteer_positions.date) AS t1
              WHERE date = '$date'
          GROUP BY date, title
          ORDER BY date, title");
    }

    /**
     * Function description
     *
     * @param (Date) the date to reference the search to
     * @return (Type) Return description
     */
    function retrieveActiveVolPositions($date) {
      return $this->connection->runQuery("SELECT title, description, reg_num,
          max_users
          FROM
              (SELECT title, description,
                (SELECT Count(*)
                   FROM volunteers
                   WHERE POSITION = title
                   AND volunteer_day = date) AS reg_num, max_users, date
              FROM volunteer_positions
              LEFT JOIN (volunteers)
              ON volunteer_positions.date = volunteers.volunteer_day
              AND volunteer_positions.title = volunteers.POSITION) AS t1
          WHERE reg_num < max_users
          AND date = '$date'
          ORDER BY title");
    }

    /**
     * Inserts a party date
     *
     * @param (Date) $udate the date to add
     * @param (int) $ucount the number of maximum registered guests
     */
    function insertVolunteerDate($udate, $ucount) {
        $this->connection->runPreparedQuery("INSERT INTO volunteer_audit 
            (vol_day, max_registered) 
            VALUES (?, ?)", array($udate, $ucount));
    }

    /**
     * Inserts new volunteer positions
     *
     * @param (String) $title the title for the position
     * @param (String) $description the desciprition for the position
     * @param (String) $max the maximum allowed number of volunteers
     * @param (String) $date the date to associate the position to
     */
    function insertNewVolPosition($title, $description, $max, $date) {
        $this->connection->runPreparedQuery("INSERT INTO volunteer_positions
           (title, description, max_users, date) VALUES (?, ?, ?, ?)",
           array($title, $description, $max, $date));
    }

    /**
     * Determines if there are any available volunteer dates left for the current
     * year
     *
     * @return (Boolean) flag indicating if there are any available slots left.
     */
    function checkForAvailableVolDates() {
        return $this->connection->runQuery("SELECT vol_day
            FROM volunteer_audit
            WHERE curr_registered < max_registered
            AND Year(vol_day) = Year(NOW())")->num_rows == 0;
    }

    /**
     * Inserts new volunteers
     *
     * @param (String) $fname the users first name
     * @param (String) $lname the users last name
     * @param (String) $email the users email
     * @param (String) $phone the users phone number
     * @param (Date) $v_day the users check in date
     * @param (TimeStamp) $tin the users check in time
     * @param (TimeStamp) $tout the users check out time
     * @return (Mysqli_result) the result of the query
     */
    function insertVolunteer($fname, $lname, $email, $phone, $v_day, $tin, $tout, $isGrp, $grpSize) {
        return $this->connection->runPreparedQuery("INSERT INTO volunteers 
            (fname, lname, email, phone, volunteer_day, time_in, time_out, is_group, group_size)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", array($fname, $lname, $email, $phone,
                $v_day, $tin, $tout, $isGrp, $grpSize)); 
    }

    /**
     * Accepts/Rejects a volunteer for the day they signed up for
     *
     * @param (String) $uemail the user's email address
     * @param (String) $vday volunteers volunteer day
     * @param (Boolean) $flag indicating to accept or reject the volunteer
     * @return (MySQli_Result) the query result
     */
    function processVolunteer($uemail, $vday, $flag) {
        return $this->connection->runPreparedQuery("UPDATE volunteers
            SET accepted = ?
            WHERE email = ?
            AND volunteer_day = ?", array($flag, $uemail, $vday));
    }

    /**
     * Determines if a recovery entry exists
     *
     * @param (String) $email the users email address
     * @param (String) $key the recovery key
     * @return (Boolean) flag indicating if an entry was found
     */
    function recoveryEntryExists($email, $key) {
        // Sanitize the input value
        $email = $this->connection->cleanSQLInputs($email);

        return $this->connection->runQuery("SELECT count(*) 
            FROM   pass_rec 
            WHERE  email = '$email'
            AND keyval = '$key' LIMIT 1")->fetch_row()[0] == 1;
    }

    /**
     * Updates a user's password upon successful recovery
     *
     * @param (String) $email the users email address
     * @param (String) $newpass the new password to set
     */
    function recoverUpdatePassword($email, $newpass) {
        $newpass = Utils::hashPassword($newpass);

        $this->connection->runPreparedQuery("DELETE FROM pass_rec
            WHERE  email = ?", array($email));
        $this->connection->runPreparedQuery("UPDATE users
            SET    password = ?
            WHERE  email = ?
            LIMIT 1 ", array($email, $newpass));
    }

    /**
     * Inserts a new recovery entry for user passwords
     *
     * @param (String) $email the user's email address
     * @param (String) $key the recovery key to refer to
     * @param (String) $expires the expiry of the entry
     */
    function insertUserRecEntry($email, $key, $expires) {
        $email = $this->connection->cleanSQLInputs($email);

        $this->connection->runQuery("INSERT INTO pass_rec (email, keyval, expires)
            VALUES ('$email', '$key', '$expires') ON DUPLICATE KEY
            UPDATE keyval = values(keyval), expires = values(expires)");

        // Since MySQL cannot execute delete statements on the table that we are 
        // inserting on, a trigger is not a possibility. So we manually run this 
        // purging of old records query after each insert
        $this->connection->runQuery("DELETE FROM pass_rec WHERE expires <=
            DATE_FORMAT(NOW(), '%Y-%m-%d %l:%i:%s');");
    }

    /**
     * .........................................................................
     * ........................... Display Methods .............................
     * .........................................................................
     */

    /**
     * Diplays the list of available dates for volunteers to choose from
     *
     * @return (String) the html representation of the date in an options list.
     */
    function displaySignUpDates() {
        $result = "";
        $row = $this->retrieveEventDates(date("Y"));
        while($ans = $row->fetch_row()) {
            $evtDate = strtotime($ans[0]);
            $result .= "<option value='" .date("Y-m-d", $evtDate)."'>"
            . date("l, F jS, Y", $evtDate) . "</option>";
        }
        return $result;
    }

    /**
     * Displays the volunteer positions not yet filled
     *
     * @return (String) the HTML representation for all the positions
     */
    function displayActiveVolPositions($date) {
        $result = "<ul>";
        $row = $this->retrieveActiveVolPositions($date);

        if ($row->num_rows < 1) {
            return $this->displayNotice("No Positions created");
        }

        while($ans = $row->fetch_row()) {
            $result .= "<li>"
                    . "<h4>" . $ans[0] ."</h4>"
                    . "<p>" . $ans[1] ."</p>"
                    . "<span>" . $ans[2] ."</span>"
                    . "<span>" . $ans[3] ."</span>"
                    . "</li>";
        }

        return $result . "</ul>";
    }

    /**
     * Displays the volunteer positions
     *
     * @return (String) the HTML representation for all the positions
     */
    function displayVolPositions($date) {
        $result;
        $row = $this->retrieveVolPositions($date);

        if ($row->num_rows < 1) {
            return $this->displayNotice("No Positions created");
        }

        $result = "<table class='vol_table'><tr class='def_cursor'>
            <th colspan='3'>Volunteer Positions</th></tr><tr class='def_cursor'>
            <td>Title</td><td>Description</td><td>Registered</td></tr>";

        while($ans = $row->fetch_row()) {
            $title = $ans[0];
            $desc = $ans[1];
            $divisor = $ans[2];
            $dividend = $ans[3];
            $result .= "<tr><td class='special'>$title</td><td>$desc</td>
            <td>$divisor / $dividend</td></tr>";
        }

        $result .= "</table>";
        return $result;
    }

    /**
     * Displays a notice
     *
     * @param (String) $body the content to diplay
     * @return (String) the HTML representation
     */
    function displayNotice($body) {
        return "<p class='message'>
                   <span class='caveat'>*</span>
                   $body
               </p>";
    }

    /**
     * Displays a list of all the past events by year
     *
     * @return (String) the volunteer events list
     */
    function displayCurrentYearsEvent() {
        $result = "<ul>";
        $row = $this->retrieveEventDates(date("Y"));
        if($row->num_rows > 0) {
            while($ans = $row->fetch_row()) {
                $result .= "<li data-date='$ans[0]'>"
                .date("l -- F jS, Y", strtotime($ans[0])). "</li>";
            }
            $result .= "</ul>";
        } else {
            $result = $this->displayNotice("No Current Events");
        }
        return $result;
    }

    /**
     * Displays a list of all the past events by year
     *
     * @return (String) the volunteer events list
     */
    function displayPastEventByYear() {
        $result = "<ul>";
        $row = $this->retrievePastEventYears();
        if($row->num_rows > 0) {
            while($ans = $row->fetch_row()) {
                $result .= "<li data-date='$ans[0]'>"
                .date("l F jS, Y", strtotime($ans[0])). "</li>";
            }
            $result .= "</ul>";
        } else {
            $result = $this->displayNotice("No Past Events");
        }
        return $result;
    }

    /**
     * Displays a table of the registered volunteers for a specific date
     *
     * @param (String) $date the date to find volunteers for
     * @return (String) a table representation of the volunteers for the
     *                  specified data
     */
    function displayVolunteersByDate($date, $start) {
        // Sanitize the user input
        $date = $this->connection->cleanSQLInputs($date);

        $resultTable = "<table id='vol_spec_date' class='selectable vol_table'>
        <tr class='def_cursor'><th colspan='9'>" . date("l jS \of F Y",
        strtotime($date)). " &middot; Volunteers</th></tr><tr class='def_cursor'>
        <td>Name</td><td>Email</td><td>Phone</td><td>Time in</td><td>Time Out</td>
        <td>Is Group?</td><td>Group Size</td><td>Position</td><td>Status</td></tr>";

        $count = $this->connection->runQuery("SELECT count(*)
            FROM volunteers
            WHERE volunteer_day = '$date'")->fetch_row()[0];

        if($count < 1) {
          return $this->displayNotice("No registrants.");
        }

        $result = $this->findRegisteredDateUsers($date, $start, displaySize);
        while ($row = $result->fetch_row()) {
            $volunteerAccepted;
            $class;
            switch ($row[9]) {
                case -1:
                    $class = "";
                    $volunteerAccepted = "Pending ...";
                    break;
                case 0:
                    $class = "class='disabled'";
                    $volunteerAccepted = "Denied";
                    break;
                case 1:
                    $class = "class='granted'";
                    $volunteerAccepted = "Accepted";
                    break;
            }

            $resultTable .= "<tr $class data-dataElem='$row[2]'
            data-box='vol_itemsToModify' data-dateVol='$date'>
            <td class='special'>" .ucwords($row[0]) ." ". ucwords($row[1])
            . "</td><td>" .$row[2]. "</td><td>(" .substr($row[3],0,3).") "
            .substr($row[3],3,3)."-".substr($row[3],6,4). "</td><td>"
            .date("g:i A", strtotime($row[4])). "</td><td>"
            .date("g:i A", strtotime($row[5])). "</td><td>"
            .(empty($row[6]) ? "False": "True"). "</td><td>" .$row[7]. "</td><td>" . $row[8]. "</td><td>"
            .$volunteerAccepted. "</td></tr>";
        }
        $resultTable .= "</table>";
        $resultTable .= "<div id='pagination'><ul>";
        for($i = 0; $i < floor($count / displaySize); $i++) {
            $q = "?specificDate=$date&page=$i";
            $class = ($start / displaySize) == $i ? " class='active'" : "";
            $resultTable .= "<li><button $class data-link=" .$_SERVER['PHP_SELF']. 
            "$q>".($i + 1)."</button></li>";
        }
        return $resultTable .= "</span></ul><span class='clear'></div>";;
    }

    /**
     * Builds an HTML table Calendar for volunteers who have registered
     *
     * @param (String) $month the month to render the calendar
     * @param (String) $year the year to render the calendar
     * @return (String) the calendar represented in string format
     */
    function displayVolunteerCalendar($month, $year) {
        // Sanitize the user input
        $month = $this->connection->cleanSQLInputs($month);
        $year = $this->connection->cleanSQLInputs($year);

        if($this->retrieveEventDates($year)->num_rows < 1) {
            return $this->displayNotice("No Events Scheduled");
        }

        // Unix timestamp for the event month
        $eventTimestamp = mktime(0,0,0,$month,1,$year);
        // Number of days in the month
        $daysInMonth = date("t", $eventTimestamp);
        // First Day of the month in numeric form
        $firstNumericDayOfMonth = getdate($eventTimestamp)['wday'];
        $rendereredTable ="<table id='vol_cal' class='vol_table'><tr>
            <th colspan='7'>" . date('F', $eventTimestamp) . " $year </th></tr>";

        // Column Headers
        $rendereredTable .= "<tr><td>Sunday</td><td>Monday</td><td>Tuesday</td>"
            ."<td>Wednesday</td><td>Thursday</td><td>Friday</td><td>Saturday"
            ."</td></tr>";

        // Build Rows
        $numOfCellsNeeded = $daysInMonth + $firstNumericDayOfMonth;
        $eventDays = $this->retrieveVolunteerDates($year);
        for ($i = 0; $i < $numOfCellsNeeded; $i++) {
            // Begining of the month
            if($i % 7 == 0 ) $rendereredTable .= "<tr>";

            // Haven't reached first day yet
            if($i < $firstNumericDayOfMonth) {
                $rendereredTable .= "<td>&nbsp;</td>";
            } else {
                $todayTimeStamp = mktime(0,0,0,$month,$i - $firstNumericDayOfMonth + 1,$year);
                $today = date("j", $todayTimeStamp);
                $todayDate = date("Y-m-d", $todayTimeStamp);

                if (in_array($todayDate, $eventDays)) { 
                    $rendereredTable .= "<td class='active'>$today</td>";
                } else {
                    $rendereredTable .= "<td class='fade'>$today</td>";
                }
                if($i % 7 == 6 || $today == $daysInMonth) $rendereredTable .= "</tr>";
            }
        }
        $rendereredTable .=  "</table>";
        return $rendereredTable;
    }
}
