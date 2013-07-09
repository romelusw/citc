<?php
include_once("functions.php");

if ($_SERVER['REQUEST_METHOD'] == "delete") {
	Utils::printCode(print_r(Utils::retrieveRequestInfo(), true));
}