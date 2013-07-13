<?php
// Report simple running errors
error_reporting(E_ERROR | E_WARNING | E_PARSE);

include_once("common_utils/functions.php");
include_once("common_utils/session.php");

// Global variables
$app;
$errMsgs;
$userLink;

// Handle GET requests
if (isset($_GET["u_email"]) && isset($_GET["rec_key"])) {
    include_once("volunteerSignUp.php");
    $app = new VolunteerAppCreator();

    if ($app->recoveryEntryExists($_GET["u_email"], $_GET["rec_key"])) {
        $userEmail = $_GET["u_email"];
        $sess = new Session("citc_rec");
        $step = 3;
        $sess->step = 3;
    } else {
        echo "The URL user/key is invalid. Or the url has expired please try again";
    }
}

// Handle POST requests
if ($_POST) {
    include_once("common_utils/formValidator.php");
    include_once("volunteerSignUp.php");

    $sess = new Session("citc_rec");
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
                    $sess->step = 1;
                    $userQA = $app->findUserSecuritySelection($u_email)->fetch_assoc();
                    $sess->u_email = $u_email;
                    $sess->u_qa = $userQA;
                } else {
                    $errMsgs["User Email"] = "We can't find a user by that email address. Please try again.";
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
                    $key = Utils::genUniqKey($userEmail);
                    $app->insertUserRecEntry($userEmail, $key, date("Y-m-d h:i:s", strtotime("+1 hours")));
                    $userLink = "?u_email=$userEmail&rec_key=$key";
                    $sess->rec_link = $userLink;
                    $step = 2;
                    $sess->step = 2;


                    // Ensure email is sent only once no matter how many 
                    // refreshes
                    if (!isset($_SESSION["emailsent"]) && $_SESSION["emailsent"] == false) {
                        // Send email
                        include_once("common_utils/email.php");
                        $emailer = new EmailTransport("Test Email", "Hello World", "test@gmail.com");
                        $emailer->sendMail("romelus.w@gmail.com");
                        $sess->emailsent = true;
                    }
                }else {
                    $errMsgs["Answer"] = "Answer is incorrect! Please try again.";
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
                $app->recoverUpdatePassword($userEmail, $new_pass);
                session_destroy();
                setcookie("citc_rec", "", time() - 3600);
                echo "password has been updated";
            } else {
                $GLOBALS["errMsgs"] = $validator->getErrors();
            }
            break;
    }
}

// Validate user input fields and retrieve errors if they exist
function validateFields($fields) {
    $validator = new FormValidator();
    $results = $validator->validate($fields);

    if ($results) {
        // Nothing to do
    }else {
        $GLOBALS["errMsgs"] = $validator->getErrors();
    }
    return $results;
}
?>

    <?php $pageTitle = "Volunteer Forgot Password"; include("header.php"); ?>

    <body>
        <?php switch($step) { case 0: ?>
        <form class="card" method="post" action="<?= $_SERVER["PHP_SELF"]; ?>">
            <p>Forgot your password eh? Please let me know your email address to help you reset your password.</p>
            <?= "<p class='error_msg'>".$errMsgs['User Email']."</p>" ?>
            <label>Email address:<span class="caveat">*</span><input type="text" name="u_email"/></label>
            <input type="submit" value="Submit"/>
        </form> 
        <?php break; case 1: ?>
        <form class="card" method="post" action="<?= $_SERVER["PHP_SELF"]; ?>">
            <p>Please answer the security question: <span class="sec_q"><?= ucwords($userQA["security_q"]); ?></span></p>
            <?= "<p class='error_msg'>".$errMsgs['Answer']."</p>" ?>
            <label>Answer:<span class="caveat">*</span><input type="text" name="sec_a"/></label>
            <input type="submit" value="Submit"/>
        </form> 
        <?php break; case 2: ?>
            <p>An email was sent to '<?= $userEmail ?>'.Click on the link to reset the password.
                <a href="<?= $userLink ?>" title="Click to reset your password">Reset Password</a>
            </p>
        <?php break; case 3: ?>
        <form class="card" method="post" action="<?= $_SERVER["PHP_SELF"]; ?>">
            <?= "<p class='error_msg'>".$errMsgs['Password']."</p>" ?>
            <label>New Password:<span class="caveat">*</span><input type="password" name="n_pass"/></label>     
            <label>Confirm New Password:<span class="caveat">*</span><input type="password"/></label>     
            <input type="submit" value="Submit"/>
        </form> 
        <?php break; } ?>
    </body>
</html>
