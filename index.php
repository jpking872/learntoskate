<?php

include_once("incl/session.php");

if ($sessionUser < 1) {
    header("Location:login.php");
} else {
    header("Location:today.php");
}

?>