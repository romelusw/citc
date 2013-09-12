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
if(isset($_COOKIE["citc_rem"]) || $_SESSION["recognized"]) {
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
        setcookie("citc_rem", $u_email ."_". $token, strtotime($config["rem_me_token_exp"]), "/", "", false, true);
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
    if(isset($app)){
        return $app;
    }else{
        return new VolunteerAppCreator();
    }
}

function logInRegisteredUser() {
    global $u_email, $u_pass, $app, $token;
    $validator = new FormValidator();
    $fields = array(
        "Email" => array("email" => $u_email)
        #"Password" => array("pass" => $u_pass)
    );

    if ($validator->validate($fields)) {
        $app = establishConnection();
        if(!$app->userIsValid($u_email, $u_pass)) {
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
        #"Password" => array("pass" => $u_pass),
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
    <body>
        <div class="centerForm">
            <h1>CITC Volunteer App</h1>
            <div class="loginForm">
            <?= Utils::generateUIError($errMsgs['exists']);?>
            <form class="card animate" method="post" action="<?= $_SERVER['PHP_SELF']?>">
                <input type="checkbox" id="box" name="isNewUser" <?= $_POST['isNewUser'] == "on" ? "checked" : ""?>/>Create an Account

                <?= Utils::generateUIError($errMsgs['First Name']);?>
                <label class='optional'>
                    <div class="lft"><i class="icon-user"></i><span class="caveat">*</span></div>
                    <input type="text" class="formField" name="userFName" placeholder="First Name" value="<?= $_POST['userFName']; ?>">
                </label>

                <?= Utils::generateUIError($errMsgs['Last Name']);?>
                <label class='optional pairsWithAbove'>
                    <div class="lft"><span class="empty_icon"></span></div>
                    <input type="text" class="formField" name="userLName" placeholder="Last Name" value="<?= $_POST['userLName']; ?>"/>
                </label>

                <?= Utils::generateUIError($errMsgs['Email']);?>
                <label>
                    <div class="lft"><i class="icon-envelope-alt"></i><span class="caveat">*</span></div>
                    <input type="email" class="formField" name="userEmail" placeholder="Email Address" value="<?= $_POST['userEmail']; ?>" required />
                </label>

                <?= Utils::generateUIError($errMsgs['Password']);?>
                <label>
                    <div class="lft"><i class="icon-key"></i><span class="caveat">*</span></div>
                    <input type="password" class="formField" name="userPassword" placeholder="Please type in your password" required/>
                </label>

                <label class='optional pairsWithAbove'>
                    <div class="lft"><span class="empty_icon"></span></div>
                    <input type="password" class="formField" placeholder="Please confirm your password"/>
                </label>

                <?= Utils::generateUIError($errMsgs['Security Question']);?>
                <label class='optional'>
                    <div class="lft"><i class="icon-question"></i><span class="caveat">*</span></div>
                    <input type="text" class="formField" name="secQ" placeholder="Security Question" value="<?= $_POST["secQ"]?>"/>
                </label>

                <?= Utils::generateUIError($errMsgs['Security Answer']);?>
                <label class='optional pairsWithAbove'>
                    <div class="lft"><span class="empty_icon"></span></div>
                    <input type="text" class="formField" name="secA" placeholder="Security Answer" value="<?= $_POST["secA"]?>"/>
                </label>

                <label><input type="checkbox" name="rememberMe" <?= $_POST['rememberMe'] == "on" ? "checked" : "" ?>/>Stay Logged in?</label>
                
                <p>
                    <a href="forgotpass.php" title="Forgot Password">Forgot Password?</a>
                </p>
                <input type="submit" class="formButton" value="submit"/>
            </form>
            </div>
        </div>
<?php include("footer.php"); ?>
