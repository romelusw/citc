<?php
// Report simple running errors
error_reporting(E_ERROR | E_WARNING | E_PARSE);

include_once("common_utils/functions.php");
include_once("common_utils/session.php");
include_once("volunteerSignUp.php");

// Global variables
$isAdmin;
$app;
$config = parse_ini_file("conf/citc_config.ini");
$sess = new Session("citc_s");
$u_email;
$app = new VolunteerAppCreator();
// if (isset($_SESSION["recognized"])) {
//     $u_email = $_SESSION["user"];
//     $app = new VolunteerAppCreator();
//     $isAdmin = $_SESSION["admin"];
//     $app->updateUserLastLogin($u_email);
// } elseif (isset($_COOKIE["citc_rem"])) {
//     session_destroy();
//     setcookie(session_name(), "", time() - 3600);
//     $parsed = preg_split("/[_]/", htmlspecialchars($_COOKIE["citc_rem"]));
//     $u_email = $parsed[0];
//     $u_token = $parsed[1];
//     $app = new VolunteerAppCreator();

//     if($app->userTokenIsValid($u_email, $u_token)) {
//         $token = md5(uniqid());
//         $isAdmin = $app->isUserAdmin($u_email);
//         $app->updateUserToken($u_email, $token);
//         setcookie("citc_rem", $u_email."_".$token, strtotime($config["rem_me_token_exp"]), "/", "", false, true);
//     } else {
//         error_log($_SERVER["REMOTE_ADDR"] . " Potential Hacker!");
//     }
// } else {
//     Utils::redirect("index.php");
// }
if (isset($_GET["specificDate"])) {
    $dateTime = strtotime($_GET["specificDate"]);
    $result = "<div id='volCalendar'>";
    $result .= $app->buildVolunteerCalendar(date("m", $dateTime), date("Y", $dateTime));
    $result .= "</div>";
    $result .= "<div id='specificDate'>";
    $result .= $app->displayVolunteersByDate(date("Y-m-d", $dateTime), (isset($_GET["page"]) ? $_GET["page"] * 10 : 0), 10);
    $result .= "<div class='actionContainer' id='volList'>
                    <ol class='itemsToModify list' id='vol_itemsToModify'></ol>
                    <ul class='actions'>
                        <li>
                            <button data-reqType='post' data-action='acceptUsers' class='actionButton'>Accept</button>
                            <button data-reqType='delete' data-action='denyUsers' class='actionButton'>Deny</button>
                        </li>
                    </ul>
                    <span class='clear'></span><div id='test'></div>
                </div>";
    $result .= "</div>";
    echo $result;
    exit();
}
?>

<?php $pageTitle = "Volunteer Administation"; include("header.php"); ?>
    <body>
        <div id="content">
            <? if (!$isAdmin): ?>
            <?php endif; ?>
            <div id="tooltip">
                <span id="tip_arrow"></span>
                <h3>Past Volunteer Events can be reviewed easily</h3>
                <p>This section displays all the parties created older than the current year.</p>
                <button class="btn" step="0">Next</button>
            </div>
            <div id="overlay">
                <div id="newPartyForm">
                   <? if (isset($_POST["pdate"]) && isset($_POST["pmaxreg"])) {
                        $app->insertVolunteerDate($_POST["pdate"], $_POST["pmaxreg"]);
                      }
                   ?>
                   <script type="text/javascript">
                        $(function() {
                            var disabledDays = "<?= $app->getDate(); ?>".split(" ");
                            $( "#dateCal" ).datepicker({
                                dateFormat: "yy-mm-dd",
                                minDate: new Date(<?= time() * 1000 ?>),
                                constrainInput: true,
                                beforeShowDay: undefinedEventDay
                            });

                            function undefinedEventDay(date) {
                                var d = new Date(date);
                                var formattedDate = d.getFullYear() + "-" + (d.getMonth()+1) + "-" + d.getDate();
                                if(disabledDays.indexOf(formattedDate) > -1){
                                    return[false];
                                } else {
                                    return [true];
                                }
                            }
                        });
                   </script>

                   <form class="card animate" action="<?= $_SERVER['PHP_SELF']?>" method="post">
                      <label>Insert Volunteer Date</label>
                      <input type="text" name="pdate" id="dateCal" readonly='true' placeholder="Click to choose date"/>
                      <input type="number" name="pmaxreg" min="1" max="1000"/>
                      <input type="submit" value="submit"/>
                      <button id="close_form" onclick="return false;">Close</button>
                   </form>
                </div>
            </div>
            <section id="headerbar">
                <? $message = "";
                   $hour = date("G");
                    switch ($hour) {
                        case $hour <= 11: // 12am - 11am
                            $message = "Good Morning, ";
                        break;
                        case $hour <= 17: // 12pm - 5pm
                            $message = "Good Afternoon, ";
                        break;
                        case $hour <= 23: // 6pm - 11pm
                            $message = "Good Evening, ";
                        break;
                    }
                ?>
                <p><?= $message . "<span style='font-weight:bold;'>" . $u_email . "</span>" ?> <a href="logout.php" title="Logout" class="button">Logout</a></p>
                <span class="clear"></span>
            </section>
            <section id="sidebar">
                <div class="sidebar_list">
                    <h3>Past Volunteer Events</h3>
                    <?= $app->displayPastEventByYear(); ?>
                </div>
                <div class="sidebar_list">
                    <h3 id="c_1">Current Volunteer Events</h3>
                    <?= $app->displayCurrentYearsEvent(); ?>
                    <button class='btn' id="add_event">+ Add</button>
                </div>
            </section>
            <section id="mainbody">
                <div id="left">
                    <h3 id="c_2">Volunteer Action</h3>
                    <div id="volCalendar">
                        <?= $app->buildVolunteerCalendar(date("m"), date("Y")); ?>
                    </div>
                    <div id='specificDate'></div>
                    <span class="clear"></span><div id="test"></div>
                </div>
                <div id="right">
                    <h3 id="c_3">Volunteer Positions</h3>
                    <div id="volunteerDates">
                        <?= $app->displayVolPositions(); ?>
                        <button class='btn' id="add_event">+ Add</button>
                        <div class="actionContainer">
                            <ol class="itemsToModify list" id="volDates_itemsToModify">
                            </ol>
                            <ul class="actions">
                                <li>
                                    <button data-reqType="post" data-action="editVolunteerDates" class="actionButton">Edit</button>
                                </li>
                            </ul>
                            <span class="clear"></span>
                        </div>
                    </div>
                </div>
            </section>
            <span class="clear"></span>
        </div>
<? include("footer.php"); ?>
