<?php

require_once(dirname(__DIR__, 2) . "/env-lts.php");

$con = mysqli_connect(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME);
$GLOBALS['dbconnection'] = $con;

?>