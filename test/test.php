<?php 
include_once("../common_utils/HTTPRequest.php");
$year = 2013;
$i = 2;

function createNArray($length, $prepend = null) {
    $result = array();
    for($i = 0; $i < $length; $i++) {
        if(!is_null($prepend)) {
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
//     $r->url = "localhost/index.php";
//     $postData = array(
//         "isNewUser" => "on",
//         "userFName" => "woody", //"User $i First Name",
//         "userLName" => "romelus", //"User $i Last Name",
//         "userEmail" => "romelus.w@gmail.com", //"user$i@gmail.com",
//         "userPassword" => "wr", //"$i",
//         "secQ" => "Number?",
//         "secA" => "1", //"$i",
//     );
//     $r->post($postData);

     // Create a bunch of volunteers dates w/ positions
//     $r->url = "localhost/accountPage.php";
//     $postData = array(
//         "pdate" => "$year-06-$i",
//         "pmaxreg" => createNArray(30),
//         "ptitle" => createNArray(30, "Title "),
//         "pdescription" => createNArray(30, "Blah Blah")
//     );
//     $r->post($postData);
//
     // Create a bunch of volunteers for the event
     $r->url = "localhost/signup.php";
     $postData = array(
         "vol_firstName" => "Volunteer $i",
         "vol_lastName" => "Volunteer $i",
         "vol_email" => "vol$i@gmail.com",
         "vol_Phone" => "7777777777",
         "volDay" => "$year-07-31",
         "vol_position" => "Spank me"
     );
     $r->post($postData);
 }