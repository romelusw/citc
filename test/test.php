<?php 
include_once("../common_utils/HTTPRequest.php");

// Create a bunch of users
for ($i = 0; $i < 50; $i++) {
    $r = new HTTPRequest();
    $r->url = "localhost/index.php";
    $postData = array(
        "isNewUser" => "on",
        "userFName" => "User $i First Name",
        "userLName" => "User $i Last Name",
        "userEmail" => "user$i@gmail.com",
        "userPassword" => "$i",
        "secQ" => "Number?",
        "secA" => "$i",
    );
    $r->post($postData);
}

// Create a bunch of volunteers dates
for ($i = 0; $i < 30; $i++) {
    $r = new HTTPRequest();
    $r->url = "localhost/accountPage.php";
    $postData = array(
        "pdate" => "2013-06-$i",
        "pmaxreg" => "100"
    );
    $r->post($postData);
}

// Create a bunch of volunteers for the event
for ($i = 0; $i < 100; $i++) {
    $r = new HTTPRequest();
    $r->url = "localhost/signup.php";
    $postData = array(
        "vol_firstName" => "Volunteer User $i F",
        "vol_lastName" => "Volunteer User $i L",
        "vol_email" => "vol_user$i@gmail.com",
        "vol_Phone" => "7777777777",
        "volDay" => "2013-06-02"
    );
    $r->post($postData);
}
// $r->get();