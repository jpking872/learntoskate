<?php

	include_once("incl/session.php");
	include_once("incl/database.php");
	include_once("incl/config.php");


	if (!$sessionUser) {
		header("Location: /login.php");
	}

	$sessions = $_GET['sessions'];
	$points = $sessions;

	include_once("header.php");


?>

		<p class="message">Session Confirmed.  <?php echo $points ?> point<?php echo $points == 1 ? "" : "s" ?> allocated.</p>
		<p class="message">Redirecting to today's skaters...</p>

		<script type="text/javascript">

		setTimeout(function () {
			window.location.href = "/today.php?logout=yes";
		}, 3000);

		</script>
<?php 

	include_once("footer.php");

?>