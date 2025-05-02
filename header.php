<html>
<head>
	<title>Ice Skate Memorial City : Learn to Skate</title>
	<link rel="stylesheet" type="text/css" href="css/style.css?r=<?php echo rand(1000,9999) ?>">

	<script src="/jquery-custom/external/jquery/jquery.js"></script>
	<script src="/jquery-custom/jquery-ui.js"></script>
	<link rel="stylesheet" href="/jquery-custom/jquery-ui.css">

	<script src="js/functions.js?r=<?php echo rand(1000,9999) ?>"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Archivo:ital,wght@0,100..900;1,100..900&family=Lato:ital,wght@0,100;0,300;0,400;0,700;0,900;1,100;1,300;1,400;1,700;1,900&display=swap" rel="stylesheet">
	<meta name="viewport" content="width=device-width, initial-scale=1">

</head>

<body>
<div class="wrapper">
    <div class="header">
        <a href="/">
            <img class="headerImageFull" src="images/ltsLogo.png" alt="Header Image">
            <img class="headerImageMobile" src="images/ltsLogo.png" alt="Header Image">
        </a>
    </div>

    <?php if (!isset($hideNav) || $hideNav == false ) { ?>

        <div class="navigation"><?php include_once("navigation.php"); ?></div>

    <?php } ?> 
