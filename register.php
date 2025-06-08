<?php

	include_once("classes/loginC.php");
	include_once("incl/session.php");
	include_once("incl/config.php");
    include_once("incl/library.php");
	include_once("incl/database.php");
	include_once("classes/DataModelC.php");
	include_once("classes/LTS.php");

	$bResult = 1;

	$error = "";
    $successRegister = false;
    $oLTS = new LTS($dbconnection);
    $oData = new DataModel(0, $dbconnection);

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
        $sfname = trim($aPostVars['sfname']);
        $slname = trim($aPostVars['slname']);
		$pin = trim($aPostVars['pin']);
		$email = trim($aPostVars['email']);

        $validPin = preg_match("/^[A-Za-z0-9]{5}$/", $pin);

		if (!$validPin) {
			$error .= "PIN must be 5 letters or numbers. ";
		}

		$pinAvailable = $oData->CheckPIN($pin);
        //$emailAvailable = $oData->CheckEmail($email);
        $nameAvailable = $oData->CheckName($sfname, $slname);

		if (!$pinAvailable) {
			$error .= "PIN is not available. ";
		}

        /*if (!$emailAvailable) {
            $error .= "Email is not available. ";
        }*/

        if (!$nameAvailable) {
            $error .= "Duplicate name. ";
        }

		if (!$error) {

			$sql = "INSERT INTO `users` (`fname`, `lname`, `sfname`, `slname`, `pin`, `email`, `role`, `created`) VALUES ( 
				'" . mysqli_real_escape_string($dbconnection, $fname) . "', 
				'" . mysqli_real_escape_string($dbconnection, $lname) . "',
				'" . mysqli_real_escape_string($dbconnection, $sfname) . "', 
				'" . mysqli_real_escape_string($dbconnection, $slname) . "',
				'" . mysqli_real_escape_string($dbconnection, $pin) . "', 
				'" . mysqli_real_escape_string($dbconnection, $email) . "',  
				'1',
				NOW())";

			$result = mysqli_query($dbconnection, $sql);

			if ($result) {
				$successRegister = true;
                $newId = mysqli_insert_id($dbconnection);
                $oData->SetUser($newId);
                $userData = $oData->GetUserData();
                $payload = array("fname" => $userData['fname'], "lname" => $userData['lname'], "sfname" => $userData['sfname'], "slname" => $userData['slname'], "email" => $userData['email']);
                $result = $oLTS->SendSingleEmail(DIRECTOR_EMAIL, $payload, 'register');
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
                <p>*Skater First name:<br/><input type="text" name="sfname" class="registerInput"></p>
                <p>*Skater Last name:<br/><input type="text" name="slname" class="registerInput"></p>
				<p>*Parent First name:<br/><input type="text" name="fname" class="registerInput"></p>
				<p>*Parent Last name:<br/><input type="text" name="lname" class="registerInput"></p>
				<p>*PIN: (5 letters or numbers)<br/><input type="password" name="pin" maxlength="5" class="registerInput registerPin"></p>
				<p>*Email:<br/><input type="text" name="email" class="registerInput"></p>
				<p><input type="submit" value="Register" class="registerSubmit"></p>
			</form>
		</div>

        <script type="text/javascript">
            $(document).ready(function () {
                $("#registerForm").submit(function (e) {
                    var errorText = "";
                    if ($("input[name='sfname']").val().length < 2 || $("input[name='sfname']").val().length > 50) {
                        errorText += "Skater first name is required.<br/>";
                    }
                    if ($("input[name='slname']").val().length < 2 || $("input[name='slname']").val().length > 50) {
                        errorText += "Skater last name is required.<br/> ";
                    }
                    if ($("input[name='fname']").val().length < 2 || $("input[name='fname']").val().length > 50) {
                        errorText += "Parent first name is required.<br/>";
                    }
                    if ($("input[name='lname']").val().length < 2 || $("input[name='lname']").val().length > 50) {
                        errorText += "Parent last name is required.<br/>";
                    }
                    var pin= new RegExp('^[A-Za-z0-9]{5}$');
                    if (!pin.test($("input[name='pin']").val())) {
                        errorText += "Pin must be 5 letters or numbers.<br/> ";
                    }
                    var email = new RegExp('^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$');
                    if (!email.test($("input[name='email']").val())) {
                        errorText += "Valid email is required.<br/> ";
                    }

                    if (errorText.length > 0) {
                        $(".errorText").html(errorText);
                        return false;
                    } else {
                        return true;
                    }
                })
            })
        </script>
<?php 

	include_once("footer.php");
?>