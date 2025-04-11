<?php

	include_once("incl/session.php");
	include_once("incl/database.php");
	include_once("incl/library.php");
	include_once("incl/config.php");
	include_once("classes/DataModelC.php");

	if (!$sessionUser || $sessionRole != 3) {
		header("Location: /login.php");
	}

	if ($_POST) {

		$infoid = $_POST['infoid'];
		$headline = $_POST['headline'];
		$news = $_POST['news'];
		$activeDate = $_POST['activeDate'];
		$active = $_POST['active'];

		if ($infoid == 0) {
			$sql = "INSERT INTO `information` (`headline`, `news`, `activeDate`, `active`) VALUES ('" . mysqli_real_escape_string($dbconnection, $headline) . "', '" . mysqli_real_escape_string($dbconnection, $news) . "', '" . 
				mysqli_real_escape_string($dbconnection, $activeDate) . "', '" . mysqli_real_escape_string($dbconnection, $active) . "')";
		} else {
			$sql = "UPDATE `information` SET `headline` = '" . mysqli_real_escape_string($dbconnection, $headline) . "', `news` = '" . mysqli_real_escape_string($dbconnection, $news) . "', `activeDate` = '" . mysqli_real_escape_string($dbconnection, $activeDate)
							. "', `active` = '" . mysqli_real_escape_string($dbconnection, $active) . "' WHERE `id` = '" . mysqli_real_escape_string($dbconnection, $infoid) . "'";
		}

		//var_dump($sql);

		$result = mysqli_query($dbconnection, $sql);

	}

	include_once("header.php");

	if ($_GET['row'] && intval($_GET['row']) && $_GET['row'] > 0 && $_GET['row'] <= 10) {

		$currentRow = $_GET['row'];

	} else {

		$currentRow = 0;
	}

	$sql = "SELECT * FROM `information` ORDER BY `entered` DESC LIMIT 10";

	$result = mysqli_query($dbconnection, $sql);

	$aInfo = array();

	while ($row = mysqli_fetch_array($result)) {

		$aInfo[] = $row;

	}

	$sql = "SELECT * FROM `information` WHERE `id` = '" . mysqli_real_escape_string($dbconnection, $currentRow) . "'";

	$result = mysqli_query($dbconnection, $sql);

	if ($row = mysqli_fetch_array($result)) {

		$currentInfo = $row;

	}



?>

		<div class="infoBar">
			<div class="pageTitle">EDIT INFORMATION SCREEN</div> 
		</div>

		<ul>

			<?php foreach ($aInfo as $info) {

				echo "<li>" . $info['id'] . ": <a href=\"?row=" . $info['id'] . "\">" . substr($info['headline'], 0, 50) . " &#151; " . 
				substr($info['news'], 0, 250) . " &#151; " . $info['activeDate'] . " &#151; " . $info['active'] . " &#151; " . 
				$info['entered'] . "</a></li>"; 

			}

			?>

		</ul>

		<?php if (empty($currentInfo) || $currentInfo['active'] == 1) {
			$active = array(" checked ", "");
		} else {
			$active = array("", " checked");
		} ?>

		<form id="infoForm" method="post" action="">
			<input type="hidden" name="infoid" value="<?php echo (isset($currentInfo)) ? $currentInfo['id'] : 0 ?>">
			<p>Headline:<br/><textarea id="headlineArea" name="headline" rows="6" cols="75"><?php echo (isset($currentInfo)) ? $currentInfo['headline'] : "" ?></textarea></p>
			<p>News:<br/><textarea id="newsArea" name="news" rows="6" cols="75"><?php echo (isset($currentInfo)) ? $currentInfo['news'] : "" ?></textarea></p>
			<p>Active Date:<br/><input type="text" name="activeDate" class="activeDate" value="<?php echo (isset($currentInfo)) ? $currentInfo['activeDate'] : date("Y-m-d") ?>"></p>
			<p>Active:<br/><input type="radio" name="active" value="1" <?php echo $active[0] ?>> yes <input type="radio" name="active" value="0" <?php echo $active[1] ?>> no</p>
			<p><input type="submit" value="Add Information"></p>
		</form>

		<script type="text/javascript" src="markitup/jquery.markitup.js"></script>
		<script type="text/javascript" src="markitup/sets/default/set.js"></script>
		<script type="text/javascript">
			$(document).ready(function () {

				$("#headlineArea, #newsArea").markItUp(mySettings);
			})

		</script>

		<link rel="stylesheet" type="text/css" href="markitup/skins/markitup/style.css" />
		<link rel="stylesheet" type="text/css" href="markitup/sets/default/style.css" />

<?php

	include_once("footer.php");

?>