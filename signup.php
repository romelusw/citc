<?php
// Report running errors only (ignoring notices)
error_reporting(E_ERROR | E_WARNING | E_PARSE);

include_once("functions.php");

// Global Variables
$errMsgs;
$show = "form";

// Handle POST requests
if ($_POST) {
    include_once("formValidator.php");
    include_once("volunteerSignUp.php");

    $vol_firstName = Utils::normalize($_POST["vol_firstName"]);
    $vol_lastName = Utils::normalize($_POST["vol_lastName"]);
    $vol_email = Utils::normalize($_POST["vol_email"]);
    $vol_volPhone = str_replace("-", "", $_POST["vol_Phone"]);
    $vol_volDay = $_POST["volDay"];
    $vol_checkIn = $_POST["checkIn"];
    $vol_checkOut = $_POST["checkOut"];

    $validator = new FormValidator();
    $fields = array(
        "First Name" => array("non_empty_text" => $vol_firstName),
        "Last Name" => array("non_empty_text" => $vol_lastName),
        "Email" => array("email" => $vol_email),
        "Volunteer Day" => array("non_empty_text" => $vol_volDay)
    );

    if($validator->validate($fields)) {
        $app = new VolunteerAppCreator(date("Y"));
        $result = $app->insertVolunteer($vol_firstName, $vol_lastName,
            $vol_email, $vol_volPhone, $vol_volDay, $vol_checkIn, $vol_checkOut);

        // Determine which view to show based on the result
        switch($app->connection->error_list[0]["sqlstate"]) {
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
            break;
        }
    } else {
        $GLOBALS["errMsgs"] = $validator->getErrors();
    }
}
?>
    <?php include("header.php"); ?>

    <body>
        <? switch($show) { case "form": ?>
        <form class="card" action="<? $_SERVER["PHP_SELF"] ?>" method="post">
            <?php echo "<p class='error_msg'>" . $errMsgs['First Name'] ."</p>" . PHP_EOL; ?>
            <label>First Name
                <span class="caveat">*</span>
                <input type="text" name="vol_firstName" value="<?= $_POST["vol_firstName"]; ?>"/>
            </label>

            <?php echo "<p class='error_msg'>" . $errMsgs['Last Name'] ."</p>" . PHP_EOL; ?>
            <label>Last Name
                <span class="caveat">*</span>
                <input type="text" name="vol_lastName" value="<?= $_POST["vol_lastName"] ?>"/>
            </label>

            <?php echo "<p class='error_msg'>" . $errMsgs['Email'] ."</p>" . PHP_EOL; ?>
            <label>Email
                <span class="caveat">*</span>
                <input type="email" name="vol_email" value="<?= $_POST["vol_email"] ?>"/>
            </label>

            <label>Telephone
                <span class="caveat">*</span>
                <input type="tel" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}" name="vol_Phone" title="888-888-8888"/>
            </label>

            <?php echo "<p class='error_msg'>" . $errMsgs['Volunteer Day'] ."</p>" . PHP_EOL; ?>
            <label>Volunteer Day
                <span class="caveat">*</span>
                <input type="date" name="volDay"/>
            </label>

            <label>Check In
                <span class="caveat">*</span>
                <input type="time" name="checkIn"/>
            </label>

            <label>Check Out
                <span class="caveat">*</span>
                <input type="time" name="checkOut"/>
            </label>

            <input type="submit" value="submit"/>
        </form>
        <? break; case "spaceAvailable": ?>
        <p>Thank you for signing up! We will be getting back to you shortly informing you whether you have been chosen as a volunteer for this years party.</p>
        <p>Please be on the lookout for an email in your inbox!</p>
        <? break; case "spaceNotAvailable": ?>
        <p>Unfortunately the party date chosen is full. Please try another date or come back next year.</p>
        <? break; case "invalidPartyDate" ?>
        <p>The Date specified is not a valid party date.</p>
        <? break; case "dupRegistration" ?>
        <p>You have already registered for this date. Please choose another party date that you have not yet registered for.</p>
        <? break; } ?>
    </body>
</html>
