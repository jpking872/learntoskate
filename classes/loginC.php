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

		$sql = "SELECT * FROM `users` WHERE `pin` = '" . $pin . "' AND `slname` = '" . mysqli_real_escape_string($dbconnection, $lastName) . "'";

		$result = mysqli_query($dbconnection, $sql);

		if ($result && mysqli_num_rows($result) > 0) {

			$aRow = mysqli_fetch_array($result);

            if ($aRow['level'] == 0) {
                return array('status' => false, 'data' => 'In order to sign up for classes on Learn to Skate to the Point, you must have paid the registration fee and submitted the waiver.');
            }

			if ($aRow['role'] == 3 && md5($adminpass) != $aRow['password']) {
                LogLogIn($aRow['id'], 'FAIL', '');
				return array('status' => false, 'data' => 'Invalid password.');
			}

            if ($aRow['role'] == 3) {
                LogLogIn($aRow['id'], 'SUCCESS', '');
            }
            
			$_SESSION['id'] = $aRow['id'];
			$_SESSION['name'] = $aRow['fname'] . " " . $aRow['lname'];
			$_SESSION['role'] = $aRow['role'];

			return array('status' => true, 'data' => $aRow);

		}

		return array('status' => false, 'data' => 'Invalid login.');
	}

}

function LogLogIn($uid, $result, $notes) {

    global $dbconnection;

    $userid    = mysqli_real_escape_string($dbconnection, $uid);
    $source_ip = mysqli_real_escape_string($dbconnection, $_SERVER['REMOTE_ADDR']);
    $res    = mysqli_real_escape_string($dbconnection, $result);
    $note    = mysqli_real_escape_string($dbconnection, $notes);

    $sql = "INSERT INTO login_log (uid, source_ip, result, notes) VALUES ('$userid', '$source_ip', '$res', '$note')";

    mysqli_query($dbconnection, $sql);

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
