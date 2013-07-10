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
            $app = new VolunteerAppCreator(date("Y"));
            Utils::printCode(print_r($reqInfo["editVolunteerDates"], true));
        } else if (isset($reqInfo["acceptUsers"])) {
            $app = new VolunteerAppCreator(date("Y"));
            $users = explode("|", $reqInfo["acceptUsers"]);
            foreach ($users as $uemail) {
                // Send Email
                $app->processVolunteer($uemail, 1);
            }
        }
    break;

    case "DELETE":
        if (isset($reqInfo["denyUsers"])) {
            $app = new VolunteerAppCreator(date("Y"));
            $users = explode("|", $reqInfo["denyUsers"]);
            foreach ($users as $uemail) {
                // Send Email
                $app->processVolunteer($uemail, 0);
            }
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