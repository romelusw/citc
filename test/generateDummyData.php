<?php
include_once("../common_utils/HTTPRequest.php");
$year = 2013;
$baseDir = "localhost/citc/volunteer_signup/";

/**
 * Creates an array of n length
 *
 * @param $length the size of the returned array of elements
 * @param null $prepend string to prepend to array elements
 * @return array the created array
 */
function createNArray($length, $prepend = null) {
    $result = array();
    for ($i = 0; $i < $length; $i++) {
        if (!is_null($prepend)) {
            array_push($result, $prepend . $i);
        } else {
            array_push($result, $i);
        }
    }
    return $result;
}

// Create a bunch of users
for ($i = 1; $i <= 100; $i++) {
    $r = new HTTPRequest();
    $r->url = $baseDir . "index.php";
    $postData = array(
        "isNewUser" => "on",
        "userFName" => "User $i First Name",
        "userLName" => "User $i Last Name",
        "userEmail" => "user$i@gmail.com",
        "userPassword" => "UserPassw%rd$i",
        "secQ" => "Number?",
        "secA" => "$i",
    );
    $r->post($postData);

    // Create a bunch of volunteers dates w/ positions
    $r->url = $baseDir . "accountPage.php";
    $day = rand(1, 3);
    $postData = array(
        "pdate" => "$year-06-$day",
        "pmaxreg" => createNArray(30),
        "ptitle" => createNArray(30, "Title "),
        "pstarttime[]" =>  (rand(1, 23) . ":" . rand(1, 55) . ":00"),
        "pdescription" => createNArray(30, "Blah Blah")
    );
    $r->post($postData);

    // Create a bunch of volunteers for the event
    $r->url = $baseDir . "signup.php";
    $day = rand(1, 3);
    $t = $i % 30;
    $postData = array(
        "vol_firstName" => "Volunteer $i",
        "vol_lastName" => "Volunteer $i",
        "vol_email" => "vol$i@gmail.com",
        "vol_phone" => "7777777777",
        "volDay" => "$year-06-$day",
        "vol_position" => "Title $t"
    );
    $r->post($postData);
}