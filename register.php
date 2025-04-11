<?php

	include_once("classes/loginC.php");
	include_once("incl/session.php");
	include_once("incl/config.php");
	include_once("incl/database.php");
	include_once("classes/DataModelC.php");

	$bResult = 1;

	$error = "";
    $successRegister = false;

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {

		$aPostVars = array();
		foreach($_POST as $key => $value) {
			if ($value == "") {
				$error .= "Invalid input. Must complete all fields. ";
				break;
			}

			$aPostVars[$key] = $value;
		}

		$fname = trim($aPostVars['fname']);
		$lname = trim($aPostVars['lname']);
		$pin = trim($aPostVars['pin']);
		$email = trim($aPostVars['email']);

		if (strlen($pin) != 5) {
			$error .= "PIN length must be 5 digits. ";
		}

		$oData = new DataModel(0, $dbconnection);
		$pinAvailable = $oData->CheckPIN($pin);
        $emailAvailable = $oData->CheckEmail($email);
        $nameAvailable = $oData->CheckName($fname, $lname);

		if (!$pinAvailable) {
			$error .= "PIN is not available. ";
		}

        if (!$emailAvailable) {
            $error .= "Email is not available. ";
        }

        if (!$nameAvailable) {
            $error .= "Duplicate name. ";
        }

		if (!$error) {

			$sql = "INSERT INTO `users` (`fname`, `lname`, `pin`, `email`, `role`, `created`) VALUES ( 
				'" . mysqli_real_escape_string($dbconnection, $fname) . "', 
				'" . mysqli_real_escape_string($dbconnection, $lname) . "',
				'" . mysqli_real_escape_string($dbconnection, $pin) . "', 
				'" . mysqli_real_escape_string($dbconnection, $email) . "',  
				'0',
				NOW())";

			$result = mysqli_query($dbconnection, $sql);

			if ($result) {
				$successRegister = true;
                header("Location: login.php");
                exit();
			} else {
                $error .= "database error";
            }

		}

	}

	include_once("header.php");

?>

		<div class="infoBar">
			<div class="pageTitle">REGISTER SKATER</div>
		</div>
		<div id="register_area">
            <p><span class="errorText"><?php echo $error ?></span></p>
			<form id="registerForm" action="" method="post">
				<p>First name:<br/><input type="text" name="fname" class="registerInput"></p>
				<p>Last name:<br/><input type="text" name="lname" class="registerInput"></p>
				<p>PIN: (5 digits)<br/><input type="password" name="pin" maxlength="5" class="registerInput registerPin"></p>
				<p>Email:<br/><input type="text" name="email" class="registerInput"></p>
				<p><input type="submit" value="Register" class="registerSubmit"></p>
			</form>
		</div>
<?php 

	include_once("footer.php");
?>