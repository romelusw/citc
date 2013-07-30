<?php
// Report running errors only (ignoring notices)
error_reporting(E_ERROR | E_WARNING | E_PARSE);

include_once("common_utils/functions.php");
include_once("volunteerSignUp.php");
include_once("common_utils/email.php");

$config = parse_ini_file("conf/citc_config.ini");
define("displaySize", $config["pagination_size"]);
$reqInfo = Utils::retrieveRequestInfo();

// Ensure user is valid
require("verifyUser.php");
// $app = new VolunteerAppCreator();

switch($reqInfo["method"]) {
    case "POST":
        if (isset($reqInfo["editVolunteerDates"])) {
            Utils::printCode(print_r($reqInfo["editVolunteerDates"], true));
        } else if (isset($reqInfo["acceptUsers"])) {
            $users = explode("|", $reqInfo["acceptUsers"]);
            $volDay = strtotime($reqInfo["volunteerDate"]);
            $currPage = $reqInfo["page"];
            foreach ($users as $uemail) {
                // Send Email
                $app->processVolunteer($uemail, date("Y-m-d", $volDay), 1);
            }
            echo $app->displayVolunteersByDate(date("Y-m-d", $volDay), $currPage * displaySize);
        }
    break;

    case "DELETE":
        if (isset($reqInfo["denyUsers"])) {
            $users = explode("|", $reqInfo["denyUsers"]);
            $volDay = strtotime($reqInfo["volunteerDate"]);
            $currPage = $reqInfo["page"];
            foreach ($users as $uemail) {
                // Send Email
                $app->processVolunteer($uemail, date("Y-m-d", $volDay), 0);
            }
            echo $app->displayVolunteersByDate(date("Y-m-d", $volDay), $currPage * displaySize);
        }
    break;

    // 'GET' requests are forbidden.
    case "GET":
        if (isset($_GET["specificDate"])) {
            $dateTime = strtotime($_GET["specificDate"]);

            $result = "<div id='volCalendar'><h3>Volunteer Action</h3>";
            $result .= $app->displayVolunteerCalendar(date("m", $dateTime), date("Y", $dateTime));
            $result .= "</div>";

            $result .= "<div id='specificDate'>";
            $result .= $app->displayVolunteersByDate(date("Y-m-d", $dateTime), (isset($reqInfo["page"]) ? $reqInfo["page"] * displaySize : 0));
            $result .= "<div class='actionContainer' id='volList'><ol 
            class='itemsToModify list' id='vol_itemsToModify'></ol><ul 
            class='actions'><li><button data-reqType='post' 
            data-action='acceptUsers' class='actionButton'>Accept</button>
            <button data-reqType='delete' data-action='denyUsers' 
            class='actionButton'>Deny</button></li></ul><span class='clear'>
            </span><div id='test'></div></div></div>";

            $result .= "<div id='volunteerDates'><h3>Volunteer Positions</h3>";
            $result .= $app->displayVolPositions(date("Y-m-d", $dateTime));
            $result .= "</div>";
            echo $result;
        } else if(isset($_GET["positionDate"])) {
            echo $app->displayActiveVolPositions($_GET["positionDate"]);
        }
    break;

    // The requested method is not supported.
    default:
        http_response_code(405);
    break;
}