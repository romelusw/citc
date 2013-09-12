<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
include_once("common_utils/functions.php");
include_once("volunteerSignUp.php");

// Global Variables
$errMsgs = array();
$show = "form";
$app = new VolunteerAppCreator();

if($app->eventsFull()) $show = "noparty";

// Handle POST requests
if ($_POST) {
    include_once("common_utils/formValidator.php");

    $vol_firstName = Utils::normalize($_POST["vol_firstName"]);
    $vol_lastName = Utils::normalize($_POST["vol_lastName"]);
    $vol_email = Utils::normalize($_POST["vol_email"]);
    $vol_volPhone = str_replace("-", "", $_POST["vol_Phone"]);
    $vol_volDay = $_POST["volDay"];
    $vol_checkIn = $_POST["checkIn"];
    $vol_checkOut = $_POST["checkOut"];
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

    if($validator->validate($fields)) {
        $result = $app->createVolunteer($vol_firstName, $vol_lastName,
            $vol_email, $vol_volPhone, $vol_volDay, $vol_checkIn,
            $vol_checkOut, $vol_isGroup, $vol_groupSize, $vol_pos);

        // Determine which view to show based on the result
        switch($result[0]["sqlstate"]) {
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
    <?php $pageTitle = "Volunteer Sign Up Form"; include("header.php"); ?>

    <body>
        <div class="centerForm">
        <? switch($show) { case "form": ?>
            <h1>Sign up to be a Volunteer!</h1>
            <form class="card" id="signupForm" action="<? $_SERVER["PHP_SELF"] ?>" method="post">
            <fieldset id="f1">
                <?= Utils::generateUIError($errorMessages['First Name']);?>
                <label for="vol_firstName">
                    <div class="lft"><i class="icon-user"></i><span class="caveat">*</span></div>
                    <input type="text" class="formField" name="vol_firstName" placeholder="First Name" value="<?= $_POST["vol_firstName"]; ?>"/>
                </label>

                <?= Utils::generateUIError($errorMessages['Last Name']);?>
                <label class="pairsWithAbove">
                    <div class="lft"><span class="empty_icon"></span></div>
                    <input type="text" class="formField" name="vol_lastName" placeholder="Last Name" value="<?= $_POST["vol_lastName"] ?>"/>
                </label>

                <?= Utils::generateUIError($errorMessages['Email']);?>
                <label>
                    <div class="lft"><i class="icon-envelope-alt"></i><span class="caveat">*</span></div>
                    <input type="email" class="formField" name="vol_email" placeholder="Email Address" value="<?= $_POST["vol_email"] ?>"/>
                </label>

                <label>
                    <div class="lft"><i class="icon-phone"></i><span class="caveat">*</span></div>
                    <input type="tel" class="formField" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}" placeholder="888-888-8888" name="vol_Phone" title="888-888-8888"/>
                </label>
            </fieldset>

            <fieldset id="f2">
                <?= Utils::generateUIError($errorMessages['Volunteer Day']);?>
                <label><i class="icon-calendar"></i> Volunteer Day<span class="caveat">*</span></label>
                <div id="signUpSelect">
                    <select name="volDay" id="volunteerDay" placeholder="Choose a Position">
                        <?= $app->displayAvailVolDateOptions(); ?>
                    </select>
                </div>
            </fieldset>

            <fieldset id="f3">
                <?= Utils::generateUIError($errorMessages['Volunteer Position']);?>
                <label><i class="icon-suitcase"></i> Position<span class="caveat">*</span></label>
                <input type="hidden" id="chosen" name="vol_position"/>
                <ul id="volunteerPosition"></ul>
            </fieldset>

            <fieldset id="f4">
                <label>Coming as a group?</label>
                <input type="checkbox" name="vol_isGroup" <?= $_POST['vol_isGroup'] == 'on' ? 'checked' : ''?>/>

                <label>Number of volunteers within the group:</label>
                <div class="counter">
                    <button class="subCount" data-increment=-1><i class="icon-minus"></i></button>
                    <input name="vol_groupSize" type="text" value="<?= $_POST["vol_groupSize"]; ?>"/>
                    <button class="addCount" data-increment=1><i class="icon-plus"></i></button>
                </div>

                <label>Check In</label>
                <span class="caveat">*</span>
                <input type="time" class="formField" name="checkIn"/>

                <label>Check Out</label>
                <span class="caveat">*</span>
                <input type="time" class="formField" name="checkOut"/>
                <input type="submit" class="formButton" value="submit"/>
            </fieldset>
            </form>
            <div class="clear"></div>
        </div>
        <? break; case "spaceAvailable": ?>
        <p class='disclaimer'>Thank you for signing up! We will be getting back to
            you shortly informing you whether you have been chosen as a volunteer
            for this years party.Please be on the lookout for an email in your inbox!</p>
        <? break; case "spaceNotAvailable": ?>
        <p class='error'>Unfortunately the party date chosen is full. Please
            try another date or come back next year.</p>
        <? break; case "invalidPartyDate"; ?>
        <p class='error'>The Date specified is not a valid party date.</p>
        <? break; case "dupRegistration"; ?>
        <p class='error'>You have already registered for this date. Please
            choose another party date that you have not yet registered for.</p>
        <? break; case "noparty"; ?>
        <div class="error">
            <h3>All Filled Up</h3>
            <p>Unfortunately there are no volunteer spots left this year. We
                thank you for your support and hope to have you retry for next year</p>
        </div>
        <? break; } ?>
<?php include("footer.php"); ?>