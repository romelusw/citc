<?php 
include_once("functions.php");

define("admin", 0);
define("norm_users", 1);

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
     * Creates the relevant volunteer specific tables, which stores information
     * about (Volunteer times, Contact info, Auditing, etc)
     */
    function buildVolunteerMgmtTables() {
       // TABLES
       $this->connection->runQuery("CREATE TABLE IF NOT EXISTS `pass_rec` (
           `rec_id` int(11) NOT NULL AUTO_INCREMENT,
           `email` varchar(60) NOT NULL,
           `keyval` varchar(32) NOT NULL,
           `expires` datetime NOT NULL,
           UNIQUE KEY `distinct_users_email` (`email`),
           PRIMARY KEY (`rec_id`))");

       $this->connection->runQuery("CREATE TABLE IF NOT EXISTS `volunteers` (
           `vol_id` int(11) NOT NULL AUTO_INCREMENT,
           `fname` varchar(15) NOT NULL,
           `lname` varchar(20) NOT NULL,
           `email` varchar(254) NOT NULL,
           `phone` varchar(10) NOT NULL,
           `volunteer_day` date NOT NULL,
           `time_in` time NOT NULL,
           `time_out` time NOT NULL,
           `accepted` int(2) NOT NULL DEFAULT 0,
           UNIQUE KEY `distinct_users_key` (`email`, `volunteer_day`),
           PRIMARY KEY (`vol_id`))");

       $this->connection->runQuery("CREATE TABLE IF NOT EXISTS `volunteer_audit` (
           `aud_id` int(11) NOT NULL AUTO_INCREMENT,
           `vol_day` date NOT NULL,
           `curr_registered` int(4) NOT NULL DEFAULT 0,
           `max_registered` int(4) NOT NULL DEFAULT 100,
           PRIMARY KEY (`aud_id`),
       UNIQUE KEY `vol_day` (`vol_day`))");

       $this->connection->runQuery("CREATE TABLE IF NOT EXISTS `users` (
           `uid` int(11) NOT NULL AUTO_INCREMENT,
           `fname` varchar(100) NOT NULL,
           `lname` varchar(100) NOT NULL,
           `email` varchar(60) NOT NULL,
           `password` varchar(60) NOT NULL,
           `security_q` varchar(70) NOT NULL,
           `security_a` varchar(30) NOT NULL,
           `created` datetime NOT NULL,
           `lastloggedin` datetime NOT NULL,
           `token` varchar(60) NOT NULL DEFAULT '',
           `usertype` int(11) NOT NULL DEFAULT " .norm_users. ",
           PRIMARY KEY (`uid`),
       UNIQUE KEY `email` (`email`))");
       
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
        // Sanitize the input value
        $fname = $this->connection->cleanSQLInputs($fname);
        $lname = $this->connection->cleanSQLInputs($lname);
        $email = $this->connection->cleanSQLInputs($email);
        $pass = $this->connection->cleanSQLInputs($passw);
        $secQ = $this->connection->cleanSQLInputs($secQ);
        $secA = $this->connection->cleanSQLInputs($secA);

        $now = date("Y-m-d H:i:s");
        $pass = Utils::hashPassword($passw);
        return $this->connection->runQuery("INSERT INTO users 
            (fname, lname, email, password, security_q, security_a, created,
             lastloggedin, token) 
            VALUES ('$fname', '$lname', '$email', '$pass', '$secQ','$secA',
            '$now', '$now', '$token')");
    }

    /**
     * Retrieves the list of registered users
     * @return (MySqli_Result) the query result
     */
    function retrieveUsers() {
        return $this->connection->runQuery("SELECT fname, lname, email, created,
                lastloggedin, usertype FROM users "); 
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
        // Sanitize the input value
        $email = $this->connection->cleanSQLInputs($uemail);

        $this->connection->runQuery("UPDATE users 
                SET lastloggedin = '". date("Y-m-d H:i:s") . 
                "' WHERE  email = '" . $email . "' 
                LIMIT 1");
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
        $email = $this->connection->cleanSQLInputs($uemail);
        $token = $this->connection->cleanSQLInputs($utoken);

        return $this->connection->runQuery("SELECT count(*) 
                FROM   users 
                WHERE  email = '$email' 
                AND token = '$token' 
                LIMIT  1")->fetch_row()[0] == 1;
    } 

    /**
     * Updates the users token value
     *
     * @param (String) $uemail the user's email
     * @param (String) $utoken the new token to set
     */
    function updateUserToken($uemail, $utoken) {
        // Sanitize the input value
        $email = $this->connection->cleanSQLInputs($uemail);
        $token = $this->connection->cleanSQLInputs($utoken);

        $this->connection->runQuery("UPDATE users 
                SET    token = '$token' 
                WHERE  email = '$email' 
                LIMIT 1"); 
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
     * Checks if a user exists with the proper credentials
     *
     * @param (String) $uemail the users email address
     * @param (String) $upassword the users password
     * @return flag indicating if the user is proper
     */
    function userIsValid($uemail, $upass) {
        // Sanitize the input value
        $email = $this->connection->cleanSQLInputs($uemail);
        $passw = $this->connection->cleanSQLInputs($upass);
        $passw = Utils::hashPassword($passw); 

        // If there is at least a user with that email we can continue prodding
        if($this->findUser($email)) {
            // Find a match for the email and password
            $result = $this->connection->runQuery("SELECT email, password 
                FROM   users 
                WHERE  email = '$email' 
                AND password = '$passw' 
                LIMIT  1");
            return $result->num_rows == 1;
        }
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

        $this->connection->runQuery("INSERT INTO pass_rec 
            (email, keyval, expires) 
            VALUES ('$email', '$key', '$expires') ON DUPLICATE KEY
            UPDATE keyval=values(keyval), expires=values(expires)"); 

        // Since MySQL cannot execute delete statements on the table that we are 
        // inserting on, a trigger is not a possibility. So we manually run this 
        // purging of old records query after each insert
        $this->connection->runQuery("DELETE FROM pass_rec WHERE expires <= DATE_FORMAT(NOW(), '%Y-%m-%d %l:%i:%s');");
    }

    /**
     * Updates a user's password upon successful recovery
     * @param (String) $email the users email address
     * @param (String) $newpass the new password to set
     */
    function recoverUpdatePassword($email, $newpass) {
        // Sanitize the input value
        $email = $this->connection->cleanSQLInputs($email);
        $newpass = $this->connection->cleanSQLInputs($newpass);
        $newpass = Utils::hashPassword($newpass);

        $this->connection->runQuery("DELETE FROM pass_rec
            WHERE  email = '$email' ");
        $this->connection->runQuery("UPDATE users 
            SET    password = '$newpass' 
            WHERE  email = '$email' 
            LIMIT 1 ");
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
        $key = $this->connection->cleanSQLInputs($key);

        return $this->connection->runQuery("SELECT count(*) 
            FROM   pass_rec 
            WHERE  email = '$email' 
            AND keyval = '$key' LIMIT 1")->fetch_row()[0] == 1;
    }

    /**
     * Changes the user type to admin
     *
     * @param (String) $uemail the user's email
     */
    function makeUserAdmin($uemail) {
        // Sanitize the user input
        $email = $this->connection->cleanSQLInputs($uemail);

        $this->connection->runQuery("UPDATE users 
            SET    usertype = ".admin." 
            WHERE  email = '$email' 
            LIMIT 1"); 
    }

    /**
     * Determines if a user has admin priveleges
     *
     * @param (String) $uemail the user's email
     * @return a flag indicating if the user is of admin type
     */
    function isUserAdmin($uemail) {
        // Sanitize the input value
        $email = $this->connection->cleanSQLInputs($uemail);

        return $this->connection->runQuery("SELECT usertype 
                FROM   users 
                WHERE  email = '$email' 
                LIMIT  1")->fetch_row()[0] == admin;
    }

    /**
     * Inserts new volunteers
     * @param (String) $fname the users first name
     * @param (String) $lname the users last name
     * @param (String) $email the users email
     * @param (String) $phone the users phone number
     * @param (Date) $v_day the users check in date
     * @param (TimeStamp) $tin the users check in time
     * @param (TimeStamp) $tout the users check out time
     * @return (Mysqli_result) the result of the query
     */
    function insertVolunteer($fname, $lname, $email, $phone, $v_day, $tin, $tout) {
        // Sanitize the input value
        $fname = $this->connection->cleanSQLInputs($fname);
        $lname = $this->connection->cleanSQLInputs($lname);
        $email = $this->connection->cleanSQLInputs($email);
        $phone = $this->connection->cleanSQLInputs($phone);
        $v_day = $this->connection->cleanSQLInputs($v_day);
        $tin = $this->connection->cleanSQLInputs($tin);
        $tout = $this->connection->cleanSQLInputs($tout);

        return $this->connection->runQuery("INSERT INTO volunteers 
            (fname, lname, email, phone, volunteer_day, time_in, time_out)
            VALUES ('$fname', '$lname', '$email', '$phone', '$v_day', '$tin',
            '$tout') "); 
    }

    /**
     * Finds all volunteers registered for a specific party date
     *
     * @param (Date) $udate the date to reference
     * @return (MySqli_Result) the query result
     */
    function findRegisteredDateUsers($udate) {
        // Sanitize the input value
        $date = $this->connection->cleanSQLInputs($udate);

        return $this->connection->runQuery("SELECT fname, lname, email, phone,
            time_in, time_out, accepted FROM volunteers WHERE volunteer_day = '$date' ");
    }

    /**
     * Inserts a party date
     *
     * @param (Date) $udate the date to add
     * @param (int) $ucount the number of maximum registered guests
     */
    function insertVolunteerDate($udate, $ucount) {
        // Sanitize the user input
        $date = $this->connection->cleanSQLInputs($udate);
        $count = $this->connection->cleanSQLInputs($ucount);

        $this->connection->runQuery("INSERT INTO volunteer_audit 
            (vol_day, max_registered) 
            VALUES ('$date', '$count')");
    }

    /**
     * Retrieves volunteer days as well as their current registration info
     *
     * @return (MySqli_Result) the query result
     */
    function retrieveVolunteerDatesInfo() {
        return $this->connection->runQuery("SELECT vol_day, curr_registered,
            max_registered FROM volunteer_audit");
    }

    /**
     * Retrieves volunteer dates for a specific year
     *
     * @param (String) $year the year to look for the volunteer dates
     * @return (Array) the list of volunteer dates for the given year
     */
    function retrieveVolunteerDates($year) {
        // Sanitize the user input
        $year = $this->connection->cleanSQLInputs($year);

        $result = array();
        $vol_days = $this->connection->runQuery("SELECT DISTINCT vol_day 
            FROM   volunteer_audit 
            WHERE  Year(vol_day) = '$year'");

        while ($row = $vol_days->fetch_array()) {
            array_push($result, $row["vol_day"]);
        }
        return $result;
    }

    /**
     * Builds an HTML table Calendar for volunteers who have registered
     *
     * @param (String) $month the month to render the calendar
     * @param (String) $year the year to render the calendar
     * @param (Array) $eventDays the days there are events
     * @return (String) the calendar represented in string format
     */
    function buildVolunteerCalendar($month, $year, $eventDays) {
        // Sanitize the user input
        $month = $this->connection->cleanSQLInputs($month);
        $year = $this->connection->cleanSQLInputs($year);

        // Unix timestamp for the next event
        $eventTimestamp = mktime(0,0,0,$month,1,$year);
        // Number of days in the month
        $daysInMonth = date("t", $eventTimestamp);
        // First Day of the month in numeric form
        $firstDay = getdate($eventTimestamp)['wday'];
        $rendereredTable ="<table><tr><th colspan='7'>" .
            date('F', $eventTimestamp) . " $year </th></tr>";

        // Column Headers
        $rendereredTable .= "<tr><td>Sunday</td><td>Monday</td><td>Tuesday</td>"
            ."<td>Wednesday</td><td>Thursday</td><td>Friday</td><td>Saturday"
            ."</td></tr>";

        // Build Rows
        for ($i = 0; $i < $daysInMonth + $firstDay; $i++) {
            $currDay = $i - $firstDay + 1;
            if($i % 7 == 0 ) $resultTable .= "<tr>";
            if($i < $firstDay) $resultTable .= "<td>&nbsp;</td>";
            else {
                $day = $currDay;
                $date = date("Y-m-d",  mktime(0,0,0,$month,$currDay,$year));
                if (in_array($date, $eventDays)) { 
                    $rendereredTable .= "<td class='active'>".
                    "<a href=".$_SERVER['PHP_SELF']."?specificDate=$date>$day"
                    . "</a></td>";
                } else {
                    $rendereredTable .= "<td>$day</td>";
                }
            }
            if($i % 7 == 6 || $currDay == $daysInMonth) $rendereredTable .= "</tr>";
        }
        $rendereredTable .=  "</table>";
        return $rendereredTable;
    }
}
