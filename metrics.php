<?php

	include_once("incl/session.php");
	include_once("incl/database.php");
	include_once("incl/library.php");
	include_once("incl/config.php");
	include_once("classes/DataModelC.php");
	include_once("classes/ClassesC.php");
	include_once("classes/MetricsC.php");

	if (!$sessionUser || $sessionRole < 3) {
		header("Location: /login.php");
	}

	include_once("header.php");

    $oMetrics = new Metrics($dbconnection);

?>

    <div class="infoBar">
        <div class="pageTitle">SITE DATA</div>
    </div>
		<div class="main">

    <p class="notification">
        <span class="gold">Total Purchases:</span> <?php echo $oMetrics->getTableCount("purchase") ?><br/>
        <span class="gold">Total Points Purchased:</span> <?php echo $oMetrics->getTotalPurchases() ?><br/>
        <span class="gold">Total Orders:</span> <?php echo $oMetrics->getTableCount("orders") ?><br/>
        <span class="gold">Total Classes:</span> <?php echo $oMetrics->getTableCount("classes") ?><br/>
        <span class="gold">Total Classes Registered:</span> <?php echo $oMetrics->getTableCount("class_user") ?><br/>
        <span class="gold">Total Passes:</span> <?php echo $oMetrics->getTableCount("passes") ?><br/>
        <span class="gold">Total Users:</span> <?php echo $oMetrics->getTableCount("users") ?><br/>
    </p>

<?php

	include_once("footer.php");

?>