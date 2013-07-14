<?php
// Report running errors only (ignoring notices)
error_reporting(E_ERROR | E_WARNING | E_PARSE);

include_once("common_utils/functions.php");
include_once("volunteerSignUp.php");
include_once("common_utils/email.php");

$reqInfo = Utils::retrieveRequestInfo();
$app;
switch($reqInfo["method"]) {
    case "POST":
        if (isset($reqInfo["editVolunteerDates"])) {
            $app = new VolunteerAppCreator();
            Utils::printCode(print_r($reqInfo["editVolunteerDates"], true));
        } else if (isset($reqInfo["acceptUsers"])) {
            $app = new VolunteerAppCreator();
            $users = explode("|", $reqInfo["acceptUsers"]);
            $volDay = strtotime($reqInfo["volunteerDate"]);
            error_log("VolDay: $volDay");
            foreach ($users as $uemail) {
                // Send Email
                // $app->processVolunteer($uemail, $volDay, 1);
            }
            echo $app->displayVolunteersByDate(date("Y-m-d", $volDay));
        }
    break;

    case "DELETE":
        if (isset($reqInfo["denyUsers"])) {
            $app = new VolunteerAppCreator();
            $users = explode("|", $reqInfo["denyUsers"]);
            $volDay = strtotime($reqInfo["volunteerDate"]);
            error_log("VolDay: $volDay");
            foreach ($users as $uemail) {
                // Send Email
                // $app->processVolunteer($uemail, $volDay, 0);
            }
            echo $app->displayVolunteersByDate(date("Y-m-d", $volDay));
        }
    break;

    // 'GET' requests are forbidden.
    case "GET":
        http_response_code(403);
    break;

    // The requested method is not supported.
    default:
        http_response_code(405);
    break;
}