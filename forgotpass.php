<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
include_once("common_utils/functions.php");
include_once("common_utils/session.php");

$app;
$errorMessages = array();
$userLink;

// Handle GET requests
if (isset($_GET["u_email"]) && isset($_GET["rec_key"])) {
    include_once("volunteerSignUp.php");
    $app = new VolunteerAppCreator();

    if($app->passwordRecoveryEntryExists($_GET["u_email"], $_GET["rec_key"])) {
        $userEmail = $_GET["u_email"];
        $session = new Session("citc_rec");
        $step = 3;
        $session->step = 3;
    } else {
        echo "The URL user/key is invalid. Or the url has expired please try again";
    }
}

// Handle POST requests
if ($_POST) {
    include_once("common_utils/formValidator.php");
    include_once("volunteerSignUp.php");

    $session = new Session("citc_rec");
    $userEmail = isset($_SESSION["u_email"]) ? $_SESSION["u_email"]: "";
    $userQA = isset($_SESSION["u_qa"]) ? $_SESSION["u_qa"]: "";
    $userLink = isset($_SESSION["rec_link"]) ? $_SESSION["rec_link"] : "";
    $step = isset($_SESSION["step"]) ? $_SESSION["step"] : 0;

    // Determine which page to display
    switch($step) {
        case 0:
            $u_email = Utils::normalize($_POST["u_email"]);
            $fields = array(
                "User Email" => array("email" => $u_email)
            );

            if(validateFields($fields)) {
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

            if(validateFields($fields)) {
                $u_answer = Utils::normalize($_POST["sec_a"]); 
                if (Utils::equalIgnoreCase($u_answer, $userQA["security_a"])) {
                    $app = new VolunteerAppCreator();
                    $key = Utils::generateUniqueKey($userEmail);
                    $app->createPasswordRecoveryEntry($userEmail, $key, date("Y-m-d h:i:s", strtotime("+1 hours")));
                    $userLink = "?u_email=$userEmail&rec_key=$key";
                    $session->rec_link = $userLink;

                    // Ensure email is sent only once no matter how many 
                    // refreshes
                    if (!isset($_SESSION["emailsent"]) || $_SESSION["emailsent"] == false) {
                        // Send email
                        include_once("common_utils/email.php");
                        $emailer = new EmailTransport("Forgotten Password",
                            "Hello World", "webmaster@christmasinthecity.org");
                        $retVal = $emailer->sendMail($userEmail);
                        $session->emailsent = $retVal;
                        $step = 2;
                        $session->step = 2;
                    }
                }else {
                    $errorMessages["Answer"] = "Answer is incorrect! Please try again.";
                }
            }
            break;
        case 3:
            $new_pass = Utils::normalize($_POST["n_pass"]);
            $validator = new FormValidator();
            $fields = array(
             #   "Password" => array("pass" => $new_pass)
            );

            if($validator->validate($fields)) {
                $app = new VolunteerAppCreator(date("Y"));
                $app->updateUserPassword($userEmail, $new_pass);
                session_destroy();
                setcookie("citc_rec", "", time() - 3600);
                echo "<p>password has been updated</p>";
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
    }else {
        $GLOBALS["errorMessages"] = $validator->getErrors();
    }
    return $results;
}
?>

    <?php $pageTitle = "Volunteer Forgot Password"; include("header.php"); ?>

    <body>
        <div class="centerForm">
        <?php switch($step) { case 0: ?>
        <h1>Forgot Your Password eh?</h1>
        <form class="card" method="post" action="<?= $_SERVER["PHP_SELF"]; ?>">
            <p>Forgot your password eh? Please let me know your email address to help you reset your password.</p>
            <?= Utils::generateUIError($errorMessages['User Email']);?>
            <label>
                <div class="lft"><i class="icon-envelope-alt"></i><span class="caveat">*</span></div>
                <input type="text" class="formField" name="u_email" placeholder="Email Address"/>
            </label>
            <input type="submit" class="formButton" value="Submit"/>
        </form>
        <?php break; case 1: ?>
        <h1>Lets find out if you are <br/> who you say you are.</h1>
        <form class="card" method="post" action="<?= $_SERVER["PHP_SELF"]; ?>">
            <p>Please answer the security question: <span class="sec_q"><?= ucwords($userQA["security_q"]); ?></span></p>
            <?= Utils::generateUIError($errorMessages['Answer']);?>
            <label>Answer:<span class="caveat">*</span>
                <input type="text" class="formField" name="sec_a"/>
            </label>
            <input type="submit" class="formButton" value="Submit"/>
        </form> 
        <?php break; case 2: ?>
            <p>An email was sent to '<?= $userEmail ?>'.Click on the link to reset the password.
                <a href="<?= $userLink ?>" title="Click to reset your password">Reset Password</a>
            </p>
        <?php break; case 3: ?>
        <h1>Reset your password with a new one. Dont Forget it this time!</h1>
        <form class="card" method="post" action="<?= $_SERVER["PHP_SELF"]; ?>">
            <?= Utils::generateUIError($errorMessages['Password']);?>
            <label>
                <div class="lft"><i class="icon-key"></i><span class="caveat">*</span></div>
                <input type="password" class="formField" placeholder="New Password" name="n_pass"/>
            </label>
            <label>
                <div class="lft"><i class="icon-key" style="color:blue;"></i><span class="caveat">*</span></div>
                <input type="password" class="formField" placeholder="Confirm New Password"/>
                <input type="submit" class="formButton" value="Submit"/>
            </label>
        </form> 
        <?php break; } ?>
        </div>
    <?php include("footer.php"); ?>