<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);

include_once("common_utils/functions.php");
include_once("volunteerSignUp.php");
include_once("common_utils/email.php");
include_once("common_utils/HTTPMethods.php");

$config = parse_ini_file("conf/citc_config.ini");
define("displaySize", $config["pagination_size"]);
$reqInfo = Utils::retrieveRequestInfo();

switch ($reqInfo["method"]) {
    case HTTPMethods::POST:
        // Ensure user is valid
        require("verifyUser.php");

        if (isset($reqInfo["acceptUsers"])) {
            $users = explode("|", $reqInfo["acceptUsers"]);
            $volDay = strtotime($reqInfo["volunteerDate"]);
            $currPage = $reqInfo["page"];
            foreach ($users as $uemail) {
                // Send Email
                include_once("common_utils/email.php");
                $emailer = new EmailTransport("VolunteerCITC You have been accepted!",
                    file_get_contents("emailers/acceptance_noncustom.html"),
                    "volunteer@christmasinthecity.org");
//                    Utils::replaceTokens("{%}", array(),
//                        file_get_contents("emailers/acceptance.html")),
//                        "webmaster@christmasinthecity.org");
                $retVal = $emailer->sendMail($uemail);
                $app->processVolunteer($uemail, date("Y-m-d", $volDay), 1);
            }
            echo $app->displayRegisteredVolunteers(date("Y-m-d", $volDay),
                $currPage * displaySize);
        } else {
            if (isset($reqInfo["modifyDesc"])) {
                $app->updatePositionTitle(trim($_POST["updateTxt"]),
                    $_POST["volPos"], $_POST["volDay"]);
            }
        }
        break;

    case HTTPMethods::DELETE:
        // Ensure user is valid
        require("verifyUser.php");

        if (isset($reqInfo["denyUsers"])) {
            $users = explode("|", $reqInfo["denyUsers"]);
            $volDay = strtotime($reqInfo["volunteerDate"]);
            $currPage = $reqInfo["page"];
            foreach ($users as $uemail) {
                $app->processVolunteer($uemail, date("Y-m-d", $volDay), 0);
            }
            echo $app->displayRegisteredVolunteers(date("Y-m-d", $volDay),
                $currPage * displaySize);
        }
        break;

    case HTTPMethods::GET:
        $app = new VolunteerAppCreator();
        if (isset($_GET["specificDate"])) {
            $dateTime = strtotime($_GET["specificDate"]);

            $result = "<div id='volCalendar'><h2>Manage Volunteers</h2>";
            $result .= $app->displayEventCalendar(date("m", $dateTime),
                date("Y", $dateTime));
            $result .= "</div>";

            $result .= "<div id='specificDate'>";
            $result .= $app->displayRegisteredVolunteers(date("Y-m-d", $dateTime),
                (isset($reqInfo["page"]) ? $reqInfo["page"] * displaySize : 0));
            $result .= "<div class='actionContainer' id='volList'><ol 
            class='itemsToModify list' id='vol_itemsToModify'></ol><ul 
            class='actions'><li><button data-reqType='post' 
            data-action='acceptUsers' class='actionButton'>Accept</button>
            <button data-reqType='delete' data-action='denyUsers' 
            class='actionButton'>Deny</button></li></ul><span class='clear'>
            </span></div></div>";

            $result .= "<div id='volunteerDates'><h2>Volunteer Positions</h2>";
            $result .= $app->displayVolPositions(date("Y-m-d", $dateTime));
            $result .= "</div>";
            echo $result;
        } else if (isset($_GET["positionDate"])) {
            echo $app->displayActiveVolPositions($_GET["positionDate"]);
        }
        break;

    // The requested method is not supported.
    default:
        http_response_code(405);
        break;
}