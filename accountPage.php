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

if (isset($_SESSION["recognized"])) {
    $app = new VolunteerAppCreator();
    $isAdmin = $_SESSION["admin"];
    $app->updateUserLastLogin($_SESSION["user"]);
} elseif (isset($_COOKIE["citc_rem"])) {
    session_destroy();
    setcookie(session_name(), "", time() - 3600);
    $parsed = preg_split("/[_]/", htmlspecialchars($_COOKIE["citc_rem"]));
    $u_email = $parsed[0];
    $u_token = $parsed[1];
    $app = new VolunteerAppCreator();

    if($app->userTokenIsValid($u_email, $u_token)) {
        $token = md5(uniqid());
        $isAdmin = $app->isUserAdmin($u_email);
        $app->updateUserToken($u_email, $token);
        setcookie("citc_rem", $u_email."_".$token, strtotime($config["rem_me_token_exp"]), "/", "", false, true);
    } else {
        error_log($_SERVER["REMOTE_ADDR"] . " Potential Hacker!");
    }
} else {
    Utils::redirect("index.php");
}
if (isset($_GET["specificDate"])) {
    $result = $app->displayVolunteersByDate($_GET["specificDate"]);
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
    echo $result;
    exit();
}
?>

<?php include("header.php"); ?>
    <body>
        <div id="content">
            <? if (!$isAdmin): ?>
            <?php endif; ?>

            <div id="sidebar">
                <div class="sidebar_list">
                    <h3>Past Volunteer Events</h3>
                    <ul>
                        <?= $app->displayPastEventByYear(); ?>
                    </ul>
                </div>
                <div class="sidebar_list">
                    <h3>Current Volunteer Events</h3>
                    <ul>
                        <?= $app->displayCurrentYearsEvent(); ?>
                    </ul>
                    <button style="width:100%" id="add_event">+ Add</button>
                </div>
            </div>

            <div id="activeBody">
                <!--<a href="logout.php" title="Logout">Logout</a>-->
                <div class="">
                    <div id="overlay">
                    <div id="newPartyForm">
                        <? if (isset($_POST["pdate"]) && isset($_POST["pmaxreg"])) {
                            $app->insertVolunteerDate($_POST["pdate"], $_POST["pmaxreg"]);
                        }?>
                        <script type="text/javascript">
                            $(function() {
                                $( "#dateCal" ).datepicker({
                                    dateFormat: "yy-mm-dd",
                                    minDate: new Date("<?= date('Y-m-d'); ?>")
                                });
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
                    <div style="width:60%; float:left;">
                    <div id="volCalendar">
                        <?= $app->buildVolunteerCalendar(12, 2013); ?>
                    </div>
                    <div id='specificDate'></div>
                    <span class="clear"></span><div id="test"></div>
                </div>
                    <div id="volunteerDates" style="width: 403px;">
                        <?= $app->displayVolunteerDates(2013); ?>
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
            </div>
            <span class="clear"></span>
        </div>
<? include("footer.php"); ?>
