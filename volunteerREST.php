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
            $uemails = explode("|", $reqInfo["acceptUsers"]);
            $positions = explode("|", $reqInfo["positions"]);
            $volDay = strtotime($reqInfo["volunteerDate"]);
            $volDate = date("Y-m-d", $volDay);
            $currPage = $reqInfo["page"];

            if(sizeof($uemails) != sizeof($positions)) {
                throw new LengthException("Length mismatch between emails and positions");
            }

            for ($i = 0; $i < sizeof($uemails); $i++) {
                // Send Email
                include_once("common_utils/email.php");
                $volDetails = $app->retrieveVolunteerDetails($volDate, $uemails[$i], $positions[$i])->fetch_assoc();
                $noValue = "~~~~";
                $grpSize = intval($volDetails["group_size"]) > 1 ? "<b>". ($volDetails["group_size"] . "</b> volunteers") : "this year";
                $starttime = strlen($volDetails["starttime"]) > 0 ? "<b>". date("g:i a", strtotime($volDetails["starttime"])) . "</b>" : $noValue;
                $groupname = strlen($volDetails["group_name"]) > 0 ? "," . "<b>". $volDetails["group_name"] . "</b>" : $noValue;

                $emailer = new EmailTransport(
                    "VolunteerCITC You have been accepted!",
                    Utils::replaceTokens("{%}", array($grpSize, $positions[$i],
                            "<b>". date("l F jS, Y", $volDay) . "</b>", $starttime, $groupname),
                        file_get_contents("emailers/acceptance.html")), "volunteer@christmasinthecity.org");

                $app->processVolunteer($uemails[$i], $volDate, $positions[$i], 1);
                $retVal = $emailer->sendMail($uemails[$i]);
            }
            echo $app->displayRegisteredVolunteers($volDate, $currPage * displaySize);
        }
        break;

    case HTTPMethods::DELETE:
        // Ensure user is valid
        require("verifyUser.php");

        if (isset($reqInfo["denyUsers"])) {
            $uemails = explode("|", $reqInfo["denyUsers"]);
            $positions = explode("|", $reqInfo["positions"]);
            $volDay = strtotime($reqInfo["volunteerDate"]);
            $currPage = $reqInfo["page"];

            if(sizeof($uemails) != sizeof($positions)) {
                throw new LengthException("Length mismatch between emails and positions");
            }

            for ($i = 0; $i < sizeof($uemails); $i++) {
                $app->processVolunteer($uemails[$i], date("Y-m-d", $volDay), $positions[$i], 0);
            }
            echo $app->displayRegisteredVolunteers(date("Y-m-d", $volDay), $currPage * displaySize);
        }
        break;

    case HTTPMethods::GET:
        $app = new VolunteerAppCreator();
        if (isset($_GET["specificDate"])) {
            $dateTime = strtotime($_GET["specificDate"]);
            $volDay = date("Y-m-d", $dateTime);

            $result = "<div id='volCalendar'><h2>Manage Volunteers</h2>";
            $result .= $app->displayEventCalendar(date("m", $dateTime),
                date("Y", $dateTime));
            $result .= "</div>";

            $result .= "<div id='specificDate'>";
            $result .= $app->displayRegisteredVolunteers($volDay,
                (isset($reqInfo["page"]) ? $reqInfo["page"] * displaySize : 0));
            $result .= "<div class='actionContainer' id='volList'><ol 
            class='itemsToModify list' id='vol_itemsToModify'></ol><ul 
            class='actions'><li><button data-reqType='post' data-action='acceptUsers'
            data-volDay='" . $volDay . "'class='actionButton'>Accept</button>
            <button data-reqType='delete' data-action='denyUsers' data-volDay='"
            . $volDay . "' class='actionButton'>Deny</button></li></ul><span
            class='clear'></span></div></div>";

            $result .= "<div id='volunteerDates'><h2>Volunteer Positions</h2>";
            $result .= $app->displayVolPositions($volDay);
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
