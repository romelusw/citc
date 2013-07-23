<?php
// Report running errors only (ignoring notices)
error_reporting(E_ERROR | E_WARNING | E_PARSE);

include_once("common_utils/functions.php");
include_once("volunteerSignUp.php");
include_once("common_utils/email.php");
$config = parse_ini_file("conf/citc_config.ini");
define("displaySize", $config["pagination_size"]);
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
            $app = new VolunteerAppCreator();
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
        http_response_code(403);
    break;

    // The requested method is not supported.
    default:
        http_response_code(405);
    break;
}