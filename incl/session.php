<?php

	session_start();

	$sessionUser = isset($_SESSION['id']) ? $_SESSION['id'] : FALSE; 
	$sessionName = isset($_SESSION['name']) ? $_SESSION['name'] : FALSE;
	$sessionRole = isset($_SESSION['role']) ? $_SESSION['role'] : FALSE;

?>