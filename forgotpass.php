<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
include_once("common_utils/functions.php");
include_once("common_utils/session.php");

$app;
$errorMessages = array();
$userLink;

if($_SERVER['REQUEST_METHOD'] === 'GET') {
//    setcookie("citc_rec", "", time() - 3600);
//    session_destroy();
}

// Handle GET requests (Clicked recovery email or link)
if (isset($_GET["u_email"]) && isset($_GET["rec_key"])) {
    include_once("volunteerSignUp.php");
    $app = new VolunteerAppCreator();

    if ($app->passwordRecoveryEntryExists($_GET["u_email"], $_GET["rec_key"])) {
        $userEmail = $_GET["u_email"];
        $session = new Session("citc_rec");
        $step = 3;
        $session->step = 3;
    } else {
        echo "<div class='notification center'>
                <h3 class='red-color'>Recovery Failure.</h3>
                <p>
                    Could not process your request.
                </p>
                <div class='notification-action'>
                    The URL user/key is invalid. Or the recovery key has
                     expired please try again.
                </div>
            </div>";
    }
}

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    Utils::printCode(print_r($_SESSION));
    include_once("common_utils/formValidator.php");
    include_once("volunteerSignUp.php");

    $session = new Session("citc_rec");
    $userEmail = isset($_SESSION["u_email"]) ? $_SESSION["u_email"] : "";
    $userQA = isset($_SESSION["u_qa"]) ? $_SESSION["u_qa"] : "";
    $userLink = isset($_SESSION["rec_link"]) ? $_SESSION["rec_link"] : "";
    $step = isset($_SESSION["step"]) ? $_SESSION["step"] : 0;

    // Determine which page to display
    switch ($step) {
        case 0:
            $u_email = Utils::normalize($_POST["u_email"]);
            $fields = array(
                "User Email" => array("email" => $u_email)
            );

            if (validateFields($fields)) {
                $app = new VolunteerAppCreator();

                if ($app->findUser($u_email)) {
                    $step = 1;
                    $session->step = 1;
                    $userQA = $app->findUserSecuritySelection($u_email)->fetch_assoc();
                    $session->u_email = $u_email;
                    $session->u_qa = $userQA;
                } else {
                    $errorMessages["User Email"] = "We can't find a user by that email address. Please try again.";
                }
            }
            break;
        case 1:
            $u_answer = Utils::normalize($_POST["sec_a"]);
            $fields = array(
                "Answer" => array("non_empty_text" => $u_answer)
            );

            if (validateFields($fields)) {
                $u_answer = Utils::normalize($_POST["sec_a"]);
                if (Utils::equalIgnoreCase($u_answer, $userQA["security_a"])) {
                    $app = new VolunteerAppCreator();
                    $key = Utils::generateUniqueKey($userEmail);
                    $app->createPasswordRecoveryEntry($userEmail, $key,
                        date("Y-m-d h:i:s", strtotime("+1 hours")));
                    $userLink = "?u_email=$userEmail&rec_key=$key";
                    $session->rec_link = $userLink;

                    // Ensure email is sent only once no matter how many 
                    // refreshes
                    if (!isset($_SESSION["emailsent"]) || $_SESSION["emailsent"] == false) {
                        // Send email
                        include_once("common_utils/email.php");
                        $emailer = new EmailTransport("CITC Forgotten Password",
                            "Please click the link to recover your password:",
                            "webmaster@christmasinthecity.org");
                        $retVal = $emailer->sendMail($userEmail);
                        $session->emailsent = $retVal;
                        $step = 2;
                        $session->step = 2;
                    }
                } else {
                    $errorMessages["Answer"] = "Answer is incorrect! Please try again.";
                }
            }
            break;
        case 3:
            $new_pass = Utils::normalize($_POST["n_pass"]);
            $validator = new FormValidator();
            $fields = array( #   "Password" => array("pass" => $new_pass)
            );

            if ($validator->validate($fields)) {
                $app = new VolunteerAppCreator(date("Y"));
                $app->updateUserPassword($userEmail, $new_pass);
                session_destroy();
                setcookie("citc_rec", "", time() - 3600);
                echo "<p class='message' style='text-align: center; font-size: 16px;;
                color:#37935c'>Password has been updated.</p>";
            } else {
                $GLOBALS["errorMessages"] = $validator->getErrors();
            }
            break;
    }
}

/**
 * Validate user input fields and retrieve errors if they exist.
 *
 * @param $fields the user input fields
 * @return bool if all the fields were verified successfully
 */
function validateFields($fields) {
    $validator = new FormValidator();
    $results = $validator->validate($fields);

    if ($results) {
        // Nothing to do
    } else {
        $GLOBALS["errorMessages"] = $validator->getErrors();
    }
    return $results;
}
?>

<?php $pageTitle = "Volunteer Forgot Password"; include("header.php"); ?>

<div class="generic-form center">
    <?php switch ($step) {
        case 0: ?>
            <img class="center" src="http://christmasinthecity.org/wp-content/uploads/CITC-Logo.png"/>
            <h1>Forgot Your Password eh?</h1>
            <form class="card" method="post"
                  action="<?= $_SERVER["PHP_SELF"]; ?>">
                <p>Let me know your email address to help you reset your
                    password.</p>

                <div class="form-field-section">
                    <?= Utils::generateUIError($errorMessages['User Email']); ?>
                    <label for="forgot-pass-email">
                        <i class="icon-envelope-alt"></i>
                        <span class="require-icon">*</span>
                    </label>
                    <input type="text" id="forgot-pass-email" class="form-field"
                           name="u_email" placeholder="Email Address"/>

                    <input type="submit" class="form-button right"
                           value="Submit"/>
                </div>
                <div class="clear"></div>
            </form>
            <?php break;
        case 1: ?>
            <img class="center" src="http://christmasinthecity.org/wp-content/uploads/CITC-Logo.png"/>
            <h1>Lets find out if you are <br/> who you say you are.</h1>
            <form class="card" method="post" action="<?= $_SERVER["PHP_SELF"]; ?>">
                <p>Please answer the security question:
                    <span class="sec_q">
                        <?= ucwords($userQA["security_q"]); ?>
                    </span>
                </p>

                <div class="form-field-section">
                    <?= Utils::generateUIError($errorMessages['Answer']); ?>
                    <label for="sec_a">
                        <i class="icon-lightbulb"></i>
                        <span class="require-icon">*</span>
                    </label>
                    <input type="text" id="sec_a" class="form-field" name="sec_a"
                           placeholder="Security Answer"/>

                    <input type="submit" class="form-button right" value="Submit"/>
                </div>
                <div class="clear"></div>
            </form>
            <?php break;
        case 2: ?>
            <div class='notification center'>
                <h3>Password has been reset!</h3>
                <p>
                    Check the inbox @
                    <span class="bold">'<?= $userEmail ?>'</span>
                    to restore your password.
                </p>
                <div class="notification-action">
                    <i class="icon-reply"></i>
                    <span>
                        Or you can reset the password now
                        <a href="<?= $userLink ?>" title="Click to reset your password">
                            Reset Password.
                        </a>
                    </span>
                </div>
            </div>
            <?php break;
        case 3: ?>
            <h1>Reset your password. Don't Forget it this time!</h1>
            <form class="card" method="post"
                  action="<?= $_SERVER["PHP_SELF"]; ?>">
                <p>Create a new password for your account.</p>

                <div class="form-field-section">
                    <?= Utils::generateUIError($errorMessages['Password']); ?>
                    <label for="n_pass">
                        <i class="icon-key"></i>
                        <span class="require-icon">*</span>
                    </label>
                    <input type="password" id="n_pass" class="form-field"
                           placeholder="New Password" name="n_pass"/>
                    <input type="password" id="n_pass2" class="form-field"
                           placeholder="Confirm New Password"/>

                    <input type="submit" class="form-button right"
                           value="Submit"/>
                </div>
                <div class="clear"></div>
            </form>
            <?php break;
    } ?>
</div>
<?php include("footer.php"); ?>