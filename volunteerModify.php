<?php
// Report running errors only (ignoring notices)
error_reporting(E_ERROR | E_WARNING | E_PARSE);

include_once("common_utils/functions.php");

$reqInfo = Utils::retrieveRequestInfo();
switch($reqInfo["method"]) {
    case "POST":
        if (isset($reqInfo["editVolunteerDates"])) {
            Utils::printCode(print_r($reqInfo["editVolunteerDates"], true));
        } else if (isset($reqInfo["acceptUsers"])) {
            Utils::printCode(print_r($reqInfo["acceptUsers"], true));
        }
    break;

    case "DELETE":
        Utils::printCode(print_r($reqInfo["denyUsers"] ,true));
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