<?php
include_once("common_utils/functions.php");
error_reporting(E_ERROR | E_WARNING | E_PARSE);

$u_firstName;
$u_lastName;
$u_email = "none@gmail.com";
$u_pass;
$u_newAcct;
$app;
$token;
$errMsgs = array();
$config = parse_ini_file("conf/citc_config.ini");

// If there is a cookie stating you are already identified, redirect
if (isset($_COOKIE["citc_rem"]) || $_SESSION["recognized"]) {
    Utils::redirect("accountPage.php");
}

// Handle submissions
if ($_POST) {
    include_once("volunteerSignUp.php");
    include_once("common_utils/formValidator.php");
    include_once("common_utils/session.php");

    // User Inputs
    $u_firstName = Utils::normalize($_POST["userFName"]);
    $u_lastName = Utils::normalize($_POST["userLName"]);
    $u_email = Utils::normalize($_POST["userEmail"]);
    $u_pass = Utils::normalize($_POST["userPassword"]);
    $u_secQ = Utils::normalize($_POST["secQ"]);
    $u_secA = Utils::normalize($_POST["secA"]);
    $u_newAcct = $_POST["isNewUser"] == "on";
    $rememberMe = $_POST["rememberMe"] == "on";

    // Login
    $isValidUser;
    if ($u_newAcct) {
        $isValidUser = createNewAcct();
    } else {
        $isValidUser = logInRegisteredUser();
    }

    // Set cookie for verified users
    if ($rememberMe && $isValidUser) {
        $token = md5(uniqid());
        $app->updateUserToken($u_email, $token);
        setcookie("citc_rem", $u_email . "_" . $token, strtotime($config["rem_me_token_exp"]), "/", "", false, true);
    }

    // Show Accounts page
    if ($isValidUser) {
        $session = new Session("citc_s");
        if ($app->isUserAdmin($u_email)) {
            $session->admin = true;
        } else {
            $session->admin = false;
        }
        $session->recognized = true;
        $session->visits = $_SESSION["visits"] + 1;
        $session->user = $u_email;
        Utils::redirect("accountPage.php");
    }
}

function establishConnection() {
    global $app;
    if (isset($app)) {
        return $app;
    } else {
        return new VolunteerAppCreator();
    }
}

function logInRegisteredUser() {
    global $u_email, $u_pass, $app, $token;
    $validator = new FormValidator();
    $fields = array(
        "Email" => array("non_empty_text" => $u_email),
        "Password" => array("non_empty_text" => $u_pass)
    );

    if ($validator->validate($fields)) {
        $app = establishConnection();
        if (!$app->userIsValid($u_email, $u_pass)) {
            $GLOBALS["errMsgs"]["exists"] = "Email/Passphrase incorrect please try again.";
            return false;
        } else {
            $app->updateUserLastLogin($u_email, $token);
            return true;
        }
    } else {
        $GLOBALS["errMsgs"] = $validator->getErrors();
        return false;
    }
}

function createNewAcct() {
    global $u_firstName, $u_lastName, $u_email, $u_pass, $app, $token, $u_secQ, $u_secA;
    $validator = new FormValidator();
    $fields = array(
        "First Name" => array("non_empty_text" => $u_firstName),
        "Last Name" => array("non_empty_text" => $u_lastName),
        "Email" => array("email" => $u_email),
        "Password" => array("pass" => $u_pass),
        "Security Question" => array("non_empty_text" => $u_secQ),
        "Security Answer" => array("non_empty_text" => $u_secA)
    );

    if ($validator->validate($fields)) {
        $app = establishConnection();
        $u_secQ = (strpos($u_secQ, "?") !== false) ? $u_secQ : $u_secQ . " ?";
        $app->insertNewUser($u_firstName, $u_lastName, $u_email, $u_pass, $u_secQ, $u_secA, $token);
        return true;
    } else {
        $GLOBALS["errMsgs"] = $validator->getErrors();
        return false;
    }
}

?>
<?php $pageTitle = "Volunteer Admin Sign In"; include("header.php"); ?>
<div class="generic-form center">
    <img class="center" src="http://christmasinthecity.org/wp-content/uploads/CITC-Logo.png"/>
    <h1>CITC Volunteer App</h1>

    <div class="loginForm">
        <?= Utils::generateUIError($errMsgs['exists']); ?>
        <form class="card" method="post" action="<?= $_SERVER['PHP_SELF'] ?>">
            <?php /*<div class="form-field-section">
                <input type="checkbox" id="new-acct-create"
                       onclick="$('.hidden').toggle();"
                       name="isNewUser" <?= $_POST['isNewUser'] == "on" ? "checked" : "" ?>/>
                <label for="new-acct-create">Create an Account</label>
            </div>*/?>

            <div class="form-field-section hidden">
                <?= Utils::generateUIError($errMsgs['First Name']); ?>
                <label for="userFName">
                    <i class="icon-user"></i>
                    <span class="require-icon">*</span>
                </label>
                <input type="text" id="new-acct-fname" class="form-field"
                       name="userFName" placeholder="First Name"
                       value="<?= $_POST['userFName']; ?>">

                <?= Utils::generateUIError($errMsgs['Last Name']); ?>
                <label for="new-acct-lname"></label>
                <input type="text" id="new-acct-lname" class="form-field"
                       name="userLName" placeholder="Last Name"
                       value="<?= $_POST['userLName']; ?>"/>
            </div>

            <div class="form-field-section">
                <?= Utils::generateUIError($errMsgs['Email']); ?>
                <label for="acct-email">
                    <i class="icon-envelope-alt"></i>
                    <span class="require-icon">*</span>
                </label>
                <input type="email" id="acct-email" class="form-field"
                       name="userEmail" placeholder="Email Address"
                       value="<?= $_POST['userEmail']; ?>" required/>
            </div>

            <div class="form-field-section">
                <?= Utils::generateUIError($errMsgs['Password']); ?>
                <label for="acct-pass">
                    <i class="icon-key"></i>
                    <span class="require-icon">*</span>
                </label>
                <input type="password" id="acct-pass" class="form-field"
                       name="userPassword"
                       placeholder="Please type in your password" required/>

                <div class="hidden">
                    <label for="new-acct-pass2"></label>
                    <input type="password" id="new-acct-pass2" class="form-field"
                           placeholder="Please confirm your password"/>
                </div>
            </div>

            <div class="form-field-section hidden">
                <label for="new-acct-sans">
                    <i class="icon-question"></i>
                    <span class="require-icon">*</span>
                </label>
                <input type="text" id="new-acct-sans" class="form-field"
                       name="secQ" placeholder="Security Question"
                       value="<?= $_POST["secQ"] ?>"/>

                <label for="new-acct-sans2"></label>
                <input type="text" id="new-acct-sans2" class="form-field"
                       name="secA" placeholder="Security Answer"
                       value="<?= $_POST["secA"] ?>"/>
            </div>


            <div class="form-field-section">
                <a href="forgotpass" class="right" title="Forgot Password">Forgot
                    Password&nbsp;?</a>
            </div>

            <div class="form-field-section">
                <input type="checkbox" id="rememberme"
                       name="rememberMe" <?= $_POST['rememberMe'] == "on" ? "checked" : "" ?>/>
                <label for="rememberme">Stay Logged in?</label>
                <input type="submit" class="form-button right" value="submit"/>
            </div>
            <div class="clear"></div>
        </form>
    </div>
</div>
<?php include("footer.php"); ?>
