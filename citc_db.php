<?php
include_once("common_utils/dbConnect.php");
$conn = new DatabaseConnector();
$config = parse_ini_file("conf/citc_config.ini");
$conn->connect($config["host"], $config["user"], $config["pass"], $config["db"]);