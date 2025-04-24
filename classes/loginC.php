<?php

function Login($aPostVars) {

	global $dbconnection;

	$oDataModel = new DataModel(0, $dbconnection);

	$pin = (strlen($aPostVars['pin']) == 4 || strlen($aPostVars['pin']) == 5) ? $aPostVars['pin'] : 0;

    $lastName = strlen($aPostVars['last']) <= 25 || strlen($aPostVars['last']) >= 2 ? $aPostVars['last'] : null;

	$adminpass = (isset($_POST['adminPass'])) ? $_POST['adminPass'] : "";

	if (!$pin || !$lastName) {

		return array('status' => false, 'data' => 'Invalid login parameters.');

	} else {

		$sql = "SELECT * FROM `users` WHERE `pin` = '" . $pin . "' AND `lname` = '" . mysqli_real_escape_string($dbconnection, $lastName) . "'";

		$result = mysqli_query($dbconnection, $sql);

		if ($result && mysqli_num_rows($result) > 0) {

			$aRow = mysqli_fetch_array($result);

			if ($aRow['role'] == 3 && md5($adminpass) != $aRow['password']) {
				return array('status' => false, 'data' => 'Invalid password.');
			}

			$_SESSION['id'] = $aRow['id'];
			$_SESSION['name'] = $aRow['fname'] . " " . $aRow['lname'];
			$_SESSION['role'] = $aRow['role'];
			if ($aRow['level'] == 0) {
                return array('status' => false, 'data' => 'In order to sign up for classes on Learn to Skate, you must have paid the registration fee and submitted the waiver.');
            }

			return array('status' => true, 'data' => $aRow);

		}

		return array('status' => false, 'data' => 'Invalid login.');
	}

}

function Logout() {
	
	session_start();
	session_destroy();

	header("Location: /");

}

function LogoutHidden() {

	session_start();
	session_destroy();
	
}
