<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
include_once("common_utils/functions.php");
include_once("volunteerSignUp.php");

// Global Variables
$errMsgs = array();
$show = "form";
$app = new VolunteerAppCreator();

if ($app->eventsFull()) $show = "noparty";

// Handle POST requests
if ($_POST) {
    include_once("common_utils/formValidator.php");

    $vol_firstName = Utils::normalize($_POST["vol_firstName"]);
    $vol_lastName = Utils::normalize($_POST["vol_lastName"]);
    $vol_email = Utils::normalize($_POST["vol_email"]);
    $vol_volPhone = str_replace("-", "", $_POST["vol_Phone"]);
    $vol_volDay = $_POST["volDay"];
    $vol_isGroup = $_POST["vol_isGroup"] == "on";
    $vol_groupSize = $_POST["vol_groupSize"];
    $vol_pos = $_POST["vol_position"];

    $validator = new FormValidator();
    $fields = array(
        "First Name" => array("non_empty_text" => $vol_firstName),
        "Last Name" => array("non_empty_text" => $vol_lastName),
        "Email" => array("email" => $vol_email),
        "Volunteer Day" => array("non_empty_text" => $vol_volDay)
    );

    if ($validator->validate($fields)) {
        $result = $app->createVolunteer($vol_firstName, $vol_lastName,
            $vol_email, $vol_volPhone, $vol_volDay, $vol_isGroup,
            $vol_groupSize, $vol_pos);

        // Determine which view to show based on the result
        switch ($result[0]["sqlstate"]) {
            case "70000":
                $show = "invalidPartyDate";
                break;
            case "70001":
                $show = "spaceNotAvailable";
                break;
            case "23000":
                $show = "dupRegistration";
                break;
            default:
                $show = "spaceAvailable";
                // Send email
                include_once("common_utils/email.php");
                $emailer = new EmailTransport("Volunteer Registration",
                    "Hello World",
                    "webmaster@christmasinthecity.org");
                $retVal = $emailer->sendMail($vol_email);
                break;
        }
    } else {
        $GLOBALS["errMsgs"] = $validator->getErrors();
    }
}
?>
<?php $pageTitle = "Volunteer Sign Up Form";
include("header.php"); ?>

<body>
<div class="centerForm">
<? switch ($show) {
case "form": ?>
    <img style="display:block; margin:0px auto"
         src="http://christmasinthecity.org/wp-content/uploads/CITC-Logo.png"/>
    <h1>Sign up to be a Volunteer!</h1>
    <form class="card" id="signupForm" action="<? $_SERVER["PHP_SELF"] ?>"
          method="post">
        <fieldset id="f1">
            <div class="formFieldSection">
                <?= Utils::generateUIError($errorMessages['First Name']); ?>
                <input type="text" id="signup-fname" class="formField validate"
                       name="vol_firstName" placeholder="First Name"
                       value="<?= $_POST["vol_firstName"]; ?>"/>
                <label for="signup-fname">
                    <i class="icon-user"></i>
                    <span class="caveat">*</span>
                </label>

                <?= Utils::generateUIError($errorMessages['Last Name']); ?>
                <input type="text" class="formField validate"
                       name="vol_lastName" placeholder="Last Name"
                       value="<?= $_POST["vol_lastName"] ?>"/>
            </div>

            <div class="formFieldSection">
                <?= Utils::generateUIError($errorMessages['Email']); ?>
                <label for="signup-email"><i class="icon-envelope-alt"></i><span
                        class="caveat">*</span></label>
                <input type="email" id="signup-lname" class="formField validate"
                       name="vol_email" placeholder="Email Address"
                       value="<?= $_POST["vol_email"] ?>"/>
            </div>

            <div class="formFieldSection">
                <label for="signup-tel"><i class="icon-phone"></i><span
                        class="caveat">*</span></label>
                <input type="tel" id="signup-tel" class="formField validate"
                       pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}"
                       placeholder="888-888-8888" name="vol_Phone"
                       title="888-888-8888"/>
            </div>
        </fieldset>

        <fieldset id="f2">
            <div class="formFieldSection">
                <?= Utils::generateUIError($errorMessages['Volunteer Day']); ?>
                <i class="icon-calendar">&nbsp;</i>&nbsp;Volunteer Day<span
                    class="caveat">*</span>
            </div>
            <div id="signUpSelect">
                <select name="volDay" id="volunteerDay" class="validate">
                    <?= $app->displayAvailVolDateOptions(); ?>
                </select>
            </div>
        </fieldset>

        <fieldset id="f3">
            <div class="formFieldSection">
                <?= Utils::generateUIError($errorMessages['Volunteer Position']); ?>
                <label for="signup-pos">
                    <i class="icon-suitcase"></i> Position(s)
                    <span class="caveat">*</span>
                </label>
                <input type="hidden" id="signup-pos" name="vol_position"
                       class="validate"/>
                <ul id="positionList"></ul>
            </div>
        </fieldset>

        <fieldset id="f4">
            <div class="formFieldSection">
                <input type="checkbox" id="vol_isGroup"
                       name="vol_isGroup" <?= $_POST['vol_isGroup'] == 'on' ? 'checked' : '' ?>/>
                <label for="vol_isGroup">Coming as a group?</label>
            </div>
            <label>Number of volunteers within the group:</label>

            <div class="counter">
                <input name="pmaxreg[]" class="formField" type="text"
                       placeholder="0"/>

                <div class="counter-incrementers">
                    <button class="subCount" data-increment="-1">
                        <i class="icon-minus"></i>
                    </button>
                    <button class="addCount" data-increment="1">
                        <i class="icon-plus"></i>
                    </button>
                </div>
            </div>

            <div class="formFieldSection">
                <input type="submit" class="formButton right" value="submit"/>
            </div>
        </fieldset>
    </form>
    <div class="clear"></div>
</div>
    <? break;
    case "spaceAvailable":
        ?>
        <p class='disclaimer'>Thank you for signing up! We will be getting back
            to
            you shortly informing you whether you have been chosen as a
            volunteer
            for this years party.Please be on the lookout for an email in your
            inbox!</p>
        <? break;
    case "spaceNotAvailable":
        ?>
        <p class='error'>Unfortunately the party date chosen is full. Please
            try another date or come back next year.</p>
        <? break;
    case "invalidPartyDate";
        ?>
        <p class='error'>The Date specified is not a valid party date.</p>
        <? break;
    case "dupRegistration";
        ?>
        <p class='error'>You have already registered for this date. Please
            choose another party date that you have not yet registered for.</p>
        <? break;
    case "noparty";
        ?>
        <div class="error">
            <h3>All Filled Up</h3>

            <p>Unfortunately there are no volunteer spots left this year. We
                thank you for your support and hope to have you retry for next
                year</p>
        </div>
        <? break;
} ?>
<?php include("footer.php"); ?>