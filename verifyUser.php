<?php
error_reporting(E_ERROR | E_WARNING | E_PARSE);
include_once("common_utils/session.php");
$sess = new Session("citc_s");
$app = new VolunteerAppCreator();

if (isset($_SESSION["recognized"])) {
    $u_email = $_SESSION["user"];
    $isAdmin = $_SESSION["admin"];
    $app->updateUserLastLogin($u_email);
} elseif (isset($_COOKIE["citc_rem"])) {
    session_destroy();
    setcookie(session_name(), "", time() - 3600);
    $parsed = preg_split("/[_]/", htmlspecialchars($_COOKIE["citc_rem"]));
    $u_email = $parsed[0];
    $u_token = $parsed[1];

    if ($app->userTokenIsValid($u_email, $u_token)) {
        $token = md5(uniqid());
        $isAdmin = $app->isUserAdmin($u_email);
        $app->updateUserToken($u_email, $token);
        $app->updateUserLastLogin($u_email);
        setcookie("citc_rem", $u_email . "_" . $token,
            strtotime($config["rem_me_token_exp"]), "/", "", false, true);
    } else {
        error_log($_SERVER["REMOTE_ADDR"] . " Token is invalid for $u_email | Potential Hacker!");
    }
} else {
    Utils::redirect("index.php");
}