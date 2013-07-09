<?php 
// Report running errors only (ignoring notices)
error_reporting(E_ERROR | E_WARNING | E_PARSE);

include_once("common_utils/functions.php");

$u_firstName;
$u_lastName;
$u_email;
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
        $sess = new Session("citc_s");
        if ($app->isUserAdmin($u_email)) {
            $sess->admin = true;
        } else {
            $sess->admin = false;
        }
        $sess->recognized = true;
        $sess->visits = $_SESSION["visits"] + 1;
        $sess->user = $u_email;
        Utils::redirect("accountPage.php");
    }
}

function establishConnection() {
    global $app;
    if(isset($app)){
        return $app;
    }else{
        return new VolunteerAppCreator(date("Y"));
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
    <?php include("header.php"); ?>
    <body>
        <div class="loginForm">
            <?php echo "<p class='error_msg'>" . $errMsgs['exists'] ."</p>" . PHP_EOL; ?>
            <form class="card animate" method="post" action="<?= $_SERVER['PHP_SELF']?>">
                <input type="checkbox" id="box" name="isNewUser" <?= $_POST['isNewUser'] == "on" ? "checked" : ""?>/>Create an Account

                <? echo "<p class='error_msg'>" . $errMsgs['First Name'] ."</p>" . PHP_EOL; ?>
                <label class='optional'>First Name:
                    <span class="caveat">*</span>
                    <input type="text" name="userFName" placeholder="Please type in your First Name" value="<?= $_POST['userFName']; ?>">
                </label>

                <? echo "<p class='error_msg'>" . $errMsgs['Last Name'] ."</p>" . PHP_EOL; ?>
                <label class='optional'>Last Name:
                    <span class="caveat">*</span>
                    <input type="text" name="userLName" placeholder="Please type in your Last Name" value="<?= $_POST['userLName']; ?>"/>
                </label>

                <? echo "<p class='error_msg'>" . $errMsgs['Email'] ."</p>" . PHP_EOL; ?>
                <label>Email:
                    <span class="caveat">*</span>
                    <input type="email" name="userEmail" placeholder="Please type in your Email Address" value="<?= $_POST['userEmail']; ?>">
                </label>

                <? echo "<p class='error_msg'>" . $errMsgs['Password'] ."</p>" . PHP_EOL;?>
                <label>Password: 
                    <span class="caveat">*</span>
                    <input type="password" name="userPassword" placeholder="Please type in your password"/>
                </label>

                <label class='optional'>Confirm Password:
                    <input type="password" placeholder="Please confirm your password"/>
                </label>

                <? echo "<p class='error_msg'>" . $errMsgs['Security Question'] ."</p>" . PHP_EOL;?>
                <label class='optional'>Security Question:
                    <span class="caveat">*</span>
                    <input type="text" name="secQ" value="<?= $_POST["secQ"]?>"/>
                </label>

                <? echo "<p class='error_msg'>" . $errMsgs['Security Answer'] ."</p>" . PHP_EOL;?>
                <label class='optional'>Security Answer:
                    <span class="caveat">*</span>
                    <input type="text" name="secA" value="<?= $_POST["secA"]?>"/>
                </label>

                <input type="checkbox" name="rememberMe" <?= $_POST['rememberMe'] == "on" ? "checked" : "" ?>/>Stay Logged in?
                <p>
                    <a href="forgotpass.php" title="Forgot Password">Forgot Password?</a>
                </p>
                <input type="submit" value="submit"/>
            </form>
        </div>
    </body>
</html>
