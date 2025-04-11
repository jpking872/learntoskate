<?php

$isLogin = $_SERVER['REQUEST_URI'] == '/login.php';
?>
<ul class="mainNav">
	<?php if ($sessionRole > 0) { ?>
		<li><a href="/logout.php">logout</a></li>
        <li><a href="/">CURRENT</a></li>
        <li><a href="/today.php">TODAY</a></li>
		<li><a href="/points.php">POINTS</a></li>
        <li><a href="https://squareup.com/store/ice-skate-usa">PURCHASE</a></li>
	<?php } ?>

	<?php if ($sessionRole == 3) { ?>
		<li><a href="/schedule.php">SCHEDULE</a></li>
		<li><a href="/user.php">SKATERS</a></li>
		<li><a href="/previous.php">PREVIOUS</a></li>
        <li><a href="/classes.php">CLASSES</a></li>
		<li><a href="/orders.php">ORDERS</a></li>
	<?php } ?>

	<?php if ($sessionRole > 0) { ?>
		<li>Welcome <?php echo $sessionName ?></li>
	<?php } ?>

	<?php if ($sessionRole == 0) { ?>
	    <?php if (!$isLogin) { ?>
		<li><a href="/login.php" id="loginButton">SIGN IN</a></li>
		<?php } ?>
		<li><a href="/register.php">REGISTER SKATER</a></li>
		<li><a href="https://squareup.com/store/ice-skate-usa">PURCHASE</a></li>
	<?php } ?>
</ul>

<div class="smallNav">

	<?php if ($sessionRole > 0) { ?>
			<div class="smallNavItem"><a href="/logout.php">logout</a></div>
            <div class="smallNavItem"><a href="/">CURRENT</a></div>
			<div class="smallNavItem"><a href="/today.php">TODAY</a></div>
			<div class="smallNavItem"><a href="/points.php">POINTS</a></div>
            <div class="smallNavItem"><a href="https://squareup.com/store/ice-skate-usa">PURCHASE</a></div>
	<?php } ?>

	<?php if ($sessionRole == 3) { ?>
	<div class="smallNavItem"><a href="/schedule.php">SCHEDULE</a></div>
	<div class="smallNavItem"><a href="/user.php">SKATERS</a></div>
	<div class="smallNavItem"><a href="/previous.php">PREVIOUS</a></div>
    <div class="smallNavItem"><a href="/classes.php">CLASSES</a></div>
	<div class="smallNavItem"><a href="/orders.php">ORDERS</a></div>
	<?php } ?>

	<?php if ($sessionRole == 0) { ?>
		<?php if (!$isLogin) { ?>
		<div class="smallNavItem"><a href="/login.php" id="loginButton">SIGN IN</a></div>
		<?php } ?>
		<div class="smallNavItem"><a href="/register.php">REGISTER SKATER</a></div>
		<div class="smallNavItem"><a href="https://squareup.com/store/ice-skate-usa">PURCHASE</a></div>
	<?php } ?>


	
	<?php if ($sessionRole > 0) { ?>
		<div class="smallNavItem">
		<span class="userName">Welcome <?php echo $sessionName ?></span>
		</div>
	<?php } ?>

</div>
