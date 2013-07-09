<?php
include_once("dbConnect.php");
$conn = new DatabaseConnector();
$config = parse_ini_file("citc_config.ini");
$conn->connect($config["host"], $config["user"], $config["pass"], $config["db"]);