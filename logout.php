<?php
include_once("common_utils/functions.php");
session_destroy();
setcookie("citc_s", "", time() - 3600);
setcookie("citc_rem", "", time() - 3600);
Utils::redirect("index.php");
