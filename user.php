<?php

	include_once("incl/session.php");
	include_once("incl/database.php");
	include_once("incl/library.php");
	include_once("incl/config.php");
	include_once("classes/DataModelC.php");
	include_once("classes/ClassesC.php");

	if (!$sessionUser || $sessionRole != 3) {
		header("Location: /login.php");
	}

    if (isset($_GET['query'])) {
        $query = $_GET['query'];
        if (preg_match("/[0-9]{4,5}/", $query)) {
            $filter['pin'] = $query;
        } else {
            $filter['user'] = $query;
        }
    } else {
        $filter['active'] = empty($_GET['all']) || $_GET['all'] != 1;
    }


	
	/*$sFilename = "";
	
	if ($_FILES['profile']['name']) {
		
		$xUpload = UploadFile($_FILES);
		if ($xUpload === TRUE) {
			$sFilename = $_FILES['profile']['name'];
		} else if ($xUpload === FALSE) {
			header("Location: /user.php?error=file");
			exit();
		} else {
			$sFilename = $xUpload;
		}
	
	}*/

	if ($_POST) {

		$uid = mysqli_real_escape_string($dbconnection, $_POST['userid']);
		$fname = mysqli_real_escape_string($dbconnection, $_POST['fname']);
		$lname = mysqli_real_escape_string($dbconnection, $_POST['lname']);
		$pin = mysqli_real_escape_string($dbconnection, $_POST['pin']);
		$oldpin = mysqli_real_escape_string($dbconnection, $_POST['oldpin']);
		$email = mysqli_real_escape_string($dbconnection, $_POST['email']);
        $notes = mysqli_real_escape_string($dbconnection, $_POST['notes']);
		//$phone = $_POST['phone'];
		//$password = $_POST['password'];

		/*if (strlen($password) < 3) { 
			$sPassSQL = "";
		} else {
			$sPassSQL = ", `password` = '" . md5($password) . "'";
		}
		$password = $_POST['password'];*/
		$sMessage = "";

		$oData = new DataModel(0, $dbconnection);

		if ($oldpin != $pin) { 
			$pinAvailable = $oData->CheckPIN($pin);
		} else {
			$pinAvailable = true;
		}

		if ($pinAvailable) {

		 	$sql = "UPDATE `users` SET `fname` = '$fname', `lname` = '$lname', `pin` = '$pin', `email` = '$email' WHERE `id` = '$uid' LIMIT 1";
			$result = mysqli_query($dbconnection, $sql);

            $oData2 = new DataModel($uid, $dbconnection);
            $oData2->AppendNote($notes, $uid, $sessionUser);

		} else {

			$sMessage = "<p class=\"message\">PIN is not available.</p>";

		}

		if ($pinAvailable) {

			if ($result) {

				$sMessage = "<p class=\"message\">Successful edit.</p>";

			} else {

				$sMessage = "<p class=\"message\">Error with edit.</p>";

			}

		}

	}

	$aUserArray = array();
	$oUserData = new DataModel(0, $dbconnection);
    $result = $oUserData->GetAllUsers($filter);

    $emailString = "";
	for ($i = 0; $i < count($result); $i++) {
        $oUserData->SetUser($result[$i]['userid']);
        $result[$i]['balance'] = $oUserData->GetUserBalance();
        $aUserArray[] = $result[$i];
        $emailString .= str_replace(' ', '', $result[$i]['email']) . ";";
    }

    $emailString = substr($emailString, 0, -1);

	include_once("header.php");

	if (isset($_GET['result'])) {

		$sMessage = "<p class=\"message\">" . urldecode($_GET['result']) . "</p>";

	} else {
        $sMessage = "";
    }

?>

		<div class="infoBar">
			<div class="pageTitle">MODIFY SKATER INFO</div> 
		</div>
		<?php echo $sMessage ?>
        <div class="loadingDiv">
            <span class="loader"></span>
        </div>
        <form id="searchForm" method="get" action="">
            <?php $adds = count($aUserArray) == 1 ? "" : "s"; ?>
        <?php if (isset($filter['active']) && $filter['active'] == true) { ?>
            <p class="showSkaters"><?php echo count($aUserArray) ?> active skater<?php echo $adds ?> | <a href="/user.php?all=1">&raquo;Show all skaters</a> | <a class="emailStringLink" href="javascript:void(0)">&raquo;Copy emails to clipboard</a> | <input type="text" name="query" class="searchSkater" placeholder="search"></p>
            <div class="emailString"><?php echo $emailString ?></div>
        <?php } elseif (isset($filter['pin']) || isset($filter['user'])) { ?>
            <p class="showSkaters"><?php echo count($aUserArray) ?> skater<?php echo $adds ?> | <?php echo "search: <span class=\"gold\">" . $query . "</span>"?> | <a href="/user.php">&raquo;Show active skaters</a> | <input type="text" name="query" class="searchSkater" placeholder="search"></p>
        <?php } else { ?>
            <p class="showSkaters"><?php echo count($aUserArray) ?> skater<?php echo $adds ?> | <a href="/user.php">&raquo;Show only active skaters</a> | <input type="text" name="query" class="searchSkater" placeholder="search"></p>
        <?php } ?>
        </form>
		<table id="usertable">
			<tr>
				<td class="medCell">Name</td>
				<td class="largeCell">Email</td>
				<td class="smallCell">Points Balance</td>
				<td class="smallCell">Pass</td>
				<td class="medCell">Actions</td>
			</tr>

            <?php $rowCount = 0 ?>
			<?php foreach ($aUserArray as $user) {
                $oddRow = $rowCount++ % 2;
                $fullRowClass = $oddRow ? " class=\"skaterOddRow\" " : "";
				//check for a current pass
				$aUserPass = explode(",", $user['pass']);
				$currMonthYear = date("n") . "/" . date("Y");

				$passCheck = "";
				for($i = 0; $i < count($aUserPass); $i++) {
					if ($aUserPass[$i] == $currMonthYear) {
						$passCheck = "&#10004";
					}
				}

				$aRoles = array("inactive", "skater", "coach", "admin");
                $isCoach = $user['role'] == 2;
                $starCoach = $isCoach ? " (coach)" : "";


				$emailLink = "<a class=\"userLink\" href=\"mailto:" . $user['email'] . "\">" . $user['email'] . "</a>";

				$rowClass = $user['balance'] >= 0 ? "" : " class=\"redtext\"";

				echo "<tr" . $fullRowClass . "><td class=\"medCell\"><a href=\"/history.php?userid=" . $user['userid'] . "\" class=\"userLink\" data-uid=\"" . $user['userid'] . "\">" . $user['lname'] . ", " . $user['fname'] . $starCoach . "</a></td><td class=\"largeCell\">" . $emailLink . "</td>
						<td class=\"smallCell\"><span$rowClass>" . $user['balance'] . "</span></td><td class=\"smallCell\">" . $passCheck . "</td><td class=\"medCell\"><a href=\"#\" class=\"editLink\" data-id=\"" . $user['userid'] . "\">edit</a> 
						<a href=\"#\" class=\"adjustLink\" data-id=\"" . $user['userid'] . "\" >adj</a>
						<a href=\"#\" class=\"deleteLink\" data-id=\"" . $user['userid'] . "\" >del</a></td></tr>";

				} ?> 

		</table>

		<div id="adjustUser">
			<h4>Adjust User Form</h4>
			<form id="adjustUserForm" method="post" action="dynamic/postActions.php">
				<input type="hidden" name="userid" value="0">
				<input type="hidden" name="actiontype" value="adjust">
				<p>Skater: <span class="adjustName">Skater Name</span></p>
				<p>Price:<br/><input name="price" type="text"></p>
				<p>Points:<br/><input name="points" type="text"></p>
				<p>Pass:<br/><select name="pass" id="pass"><option value="0">None</option>
					<?php 
					$currentDateValue = array(date("n-Y"), date("n-Y", time() + 24 * 3600 *365 / 12), date("n-Y", time() + 24 * 3600 * 2 * 365 / 12 ));
					$currentDateString = array(date("F Y"), date("F Y", time() + 24 * 3600 *365 / 12), date("F Y", time() + 24 * 3600 * 2 * 365 / 12 ));
					for ($i = 0; $i < 3; $i++) {
						echo "<option value='" . $currentDateValue[$i] . "'>" . $currentDateString[$i] . "</option>";
					} ?>
				</select></p>
				<p>Date:<br/><input type="text" name="date" id="purchaseDate" value="<?php echo date("Y-m-d") ?>"></p>
				<p>Note:<br/><input name="note" type="text" size="125"></p>
				<p><input type="submit" value="Enter"></p>
			</form>
		</div>

		<div id="emailUser">
			<h4>Email User Form</h4>
			<form id="emailUserForm" method="post" action="dynamic/postActions.php">
				<input type="hidden" name="userid" value="0">
				<input type="hidden" name="actiontype" value="email">
				<p>Skater:<br><input type="text" name="skaterName"></p>
				<p>Email:<br><input type="text" name="skaterEmail"></p>
				<p>Subject:<br><input type="text" name="subject" style="width:375px"></p>
				<p>Message:<br>
				<textarea id="message" name="message" rows="6" cols="80"></textarea></p>
				<p><input type="submit" value="send"></p>
			</form>
		</div>

		<div id="editUser">
			<h4>Edit User Form</h4>
			<form id="editUserForm" method="post" action="">
				<input type="hidden" name="oldpin" value="">
				<input type="hidden" name="userid" value="0">
				<div class="editLeft">
					<p>First Name:<br><input type="text" name="fname"></p>
					<p>Last Name:<br><input type="text" name="lname"></p>
					<p>PIN:<br><input type="text" name="pin"></p>
					<p>Email:<br><input type="text" name="email"></p>
				</div>
				<div class="editRight">
                    <p>Add a note:<br/>
                        <input class="userNotes" name="notes">
                    </p>
				</div>
				<div style="clear:both"></div>
				<p><input type="submit" value="edit"></p>
			</form>
		</div>


<?php 

	include_once("footer.php");

?>