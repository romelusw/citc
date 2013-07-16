<?php 
include_once("../common_utils/HTTPRequest.php");

// Create a bunch of users
for ($i = 0; $i < 100; $i++) {
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

// Create a bunch of volunteers
for ($i = 0; $i < 20; $i++) {
    $r = new HTTPRequest();
    $r->url = "localhost/accountPage.php";
    $postData = array(
        "pdate" => "2013-06-$i",
        "pmaxreg" => "$i"
    );
    $r->post($postData);
}
// $r->get();