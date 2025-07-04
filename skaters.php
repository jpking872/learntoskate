<?php

	include_once("incl/session.php");
	include_once("incl/database.php");
	include_once("incl/library.php");
	include_once("incl/config.php");
	include_once("classes/DataModelC.php");
	include_once("classes/ClassesC.php");
	include_once("classes/LTS.php");


	if (!$sessionUser || $sessionRole != 3) {
		header("Location: /login.php");
	}

    if (isset($_GET['query'])) {
        $query = $_GET['query'];
        if (isset($_GET['type']) && $_GET['type'] == 'pin') {
            $filter['pin'] = $query;
        } else {
            $filter['user'] = $query;
        }
    } else {
        $filter['active'] = empty($_GET['all']) || $_GET['all'] != 1;
    }

    $sMessage = "";

	if ($_POST) {

		$uid = mysqli_real_escape_string($dbconnection, $_POST['userid']);
		$fname = mysqli_real_escape_string($dbconnection, $_POST['fname']);
		$lname = mysqli_real_escape_string($dbconnection, $_POST['lname']);
        $sfname = mysqli_real_escape_string($dbconnection, $_POST['sfname']);
        $slname = mysqli_real_escape_string($dbconnection, $_POST['slname']);
		$pin = mysqli_real_escape_string($dbconnection, $_POST['pin']);
		$oldpin = mysqli_real_escape_string($dbconnection, $_POST['oldpin']);
		$email = mysqli_real_escape_string($dbconnection, $_POST['email']);
		$iReg = strlen($_POST['registration']) < 3 ? "NULL" : "'" . mysqli_real_escape_string($dbconnection, $_POST['registration']) . "'";
		$iWaiver = strlen($_POST['waiver']) < 3 ? "NULL" : "'" . mysqli_real_escape_string($dbconnection, $_POST['waiver']) . "'";

        $ilevel = $_POST['level'];

		$oData = new DataModel(0, $dbconnection);

		if ($oldpin != $pin) { 
			$pinAvailable = $oData->CheckPIN($pin);
		} else {
			$pinAvailable = true;
		}

		if ($pinAvailable) {

		 	$sql = "UPDATE `users` SET `fname` = '$fname', `lname` = '$lname', `sfname` = '$sfname', `slname` = '$slname', `pin` = '$pin', `email` = '$email',
               `level` = '$ilevel', `registration` = $iReg, `waiver` = $iWaiver WHERE `id` = '$uid' LIMIT 1";

			$result = mysqli_query($dbconnection, $sql);

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

    $oLTS = new LTS($dbconnection);
    $levels = $oLTS->GetLevels();
	include_once("header.php");

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
            <p class="showSkaters"><?php echo count($aUserArray) ?> active skater<?php echo $adds ?> | <a href="/skaters.php?all=1">&raquo;Show all skaters</a> | <a class="emailStringLink" href="javascript:void(0)">&raquo;Copy emails to clipboard</a> | <input type="text" name="query" class="searchSkater" placeholder="search"></p>
            <div class="emailString"><?php echo $emailString ?></div>
        <?php } elseif (isset($filter['pin']) || isset($filter['user'])) { ?>
            <p class="showSkaters"><?php echo count($aUserArray) ?> skater<?php echo $adds ?> | <?php echo "search: <span class=\"gold\">" . $query . "</span>"?> | <a href="/skaters.php">&raquo;Show active skaters</a> | <input type="text" name="query" class="searchSkater" placeholder="search"></p>
        <?php } else { ?>
            <p class="showSkaters"><?php echo count($aUserArray) ?> skater<?php echo $adds ?> | <a href="/skaters.php">&raquo;Show only active skaters</a> | <input type="text" name="query" class="searchSkater" placeholder="search"></p>
        <?php } ?>
        </form>
		<table id="usertable">
			<tr>
                <td class="medCell">Skater Name</td>
				<td class="medCell">Parent Name</td>
				<td class="medCell">Email</td>
				<td class="smallCell">Level</td>
				<td class="smallCell centerCell">Registration</td>
				<td class="smallCell centerCell">Waiver</td>
				<td class="smallCell centerCell">Balance</td>
				<td class="smallCell centerCell">Pass</td>
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

				$emailLink = $user['email'];


                $levelName = "Not approved";
                foreach ($levels as $level) {
                    if ($user['level'] == $level['id']) {
                        $levelName = $level['level'];
                    }
                }

				$rowClass = $user['balance'] >= 0 ? "" : " class=\"redtext\"";
                $skaterName = $user['slname'] . ", " . $user['sfname'];
                $regName = $user['registration'] ? date("m/d/Y", strtotime($user['registration'])) : "-";
                $waiverName = $user['waiver'] ? date("m/d/Y", strtotime($user['waiver'])) : "-";

				echo "<tr" . $fullRowClass . ">
                        <td class=\"medCell\"><a href=\"/profile.php?userid=" . $user['userid'] . "\" class=\"userLink\" data-uid=\"" . $user['userid'] . "\">" . $skaterName . "</a></td>
                        <td class=\"medCell\"><a href=\"/profile.php?userid=" . $user['userid'] . "\" class=\"userLink\" data-uid=\"" . $user['userid'] . "\">" . $user['lname'] . ", " . $user['fname'] . $starCoach . "</a></td>
                        <td class=\"medCell\">" . $emailLink . "</td>
						<td class=\"medCell\">" . $levelName . "</td>
						<td class=\"smallCell centerCell\">" . $regName . "</td>
						<td class=\"smallCell centerCell\">" . $waiverName . "</td>
						<td class=\"smallCell centerCell\"><span$rowClass>" . $user['balance'] . "</span></td><td class=\"smallCell centerCell\">" . $passCheck . "</td><td class=\"medCell\"><a href=\"#\" class=\"editLink\" data-id=\"" . $user['userid'] . "\">edit</a> 
						<a href=\"#\" class=\"adjustLink\" data-id=\"" . $user['userid'] . "\" >adj</a> 
						<a href=\"#\" class=\"deleteLink\" data-id=\"" . $user['userid'] . "\" >del</a>
						</td></tr>";

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
				<p><input type="submit" value="Submit"></p>
			</form>
		</div>

		<div id="editUser">
			<h4>Edit User Form</h4>
			<form id="editUserForm" method="post" action="">
                <div class="errorText"></div>
				<input type="hidden" name="oldpin" value="">
				<input type="hidden" name="userid" value="0">
				<div class="editLeft">
                    <p>Skater First Name:<br><input type="text" name="sfname"></p>
                    <p>Skater Last Name:<br><input type="text" name="slname"></p>
					<p>Parent First Name:<br><input type="text" name="fname"></p>
					<p>Parent Last Name:<br><input type="text" name="lname"></p>
					<p>PIN:<br><input type="text" name="pin"></p>
					<p>Email:<br><input type="text" name="email"></p>
				</div>
				<div class="editRight">
                    <p>Level:<br/>
                        <select name="level">
                            <?php
                            echo "<option value=\"0\">Not approved</option>";
                            foreach ($levels as $level) {
                                echo "<option value=\"" . $level['id'] . "\">" . $level['level'] . "</option>";
                            } ?>
                        </select>
                    </p>
                    <p>Registration Fee:<br/><input type="text" class="registrationDate" name="registration"></p>
                    <p>Waiver Submitted:<br/><input type="text" class="waiverDate" name="waiver"></p>
				</div>
				<div style="clear:both"></div>
				<p><input type="submit" value="Submit"></p>
			</form>
		</div>

<?php 

	include_once("footer.php");

?>