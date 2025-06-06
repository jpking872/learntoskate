<?php

	include_once("../incl/session.php");
	include_once("../incl/database.php");
	include_once("../incl/library.php");
	include_once("../incl/config.php");
	include_once("../classes/DataModelC.php");

	$aScheduleData = array();

	function RetrieveTimes($sDate) {

		global $dbconnection;

		$sql = "SELECT * FROM `schedule` WHERE `date` = '" . $sDate . "'";
		$result = mysqli_query($dbconnection, $sql);
		$aDayData = array();
		while ($row = mysqli_fetch_array($result)) {
			$aDayData[] = array('date' => $sDate, 'start' => $row['start'], 'stop' => $row['stop']);
		}

		if (count($aDayData) == 0) {
			$aDayData[] = array('date' => $sDate, 'start' => "-", 'stop' => "-");
		} 

		return $aDayData;
	}

	if ($_POST['type'] == 'getSchedule') {

		$sDateDecode = urldecode($_POST['date']);

		if ($sDateDecode == "0") {
			$sTime = time();
		} else {
			$sTime = strtotime($sDateDecode);
		}

		$iStart = strtotime("last Sunday", $sTime);

		if (date("w", $sTime) == 0) {
			$iStart = strtotime("next Sunday", $iStart);
		}

		for ($iCurr = 0; $iCurr < 21; $iCurr++) {

			$iThisDate = strtotime("+" . $iCurr . " days", $iStart);

			$sDBDate = date("Y-m-d", $iThisDate);

			$aScheduleData[] = RetrieveTimes($sDBDate);

		}

		echo json_encode($aScheduleData);
		exit();
		
	}

	else if ($_POST['type'] == 'getScheduleList') {

		$sDateDecode = urldecode($_POST['date']);

		if ($sDateDecode == "0") {
			$sTime = time();
		} else {
			$sTime = strtotime($sDateDecode);
		}

		$iStart = strtotime("last Sunday", $sTime);

		if (date("w", $sTime) == 0) {
			$iStart = strtotime("next Sunday", $iStart);
		}

		for ($iCurr = 0; $iCurr < 7; $iCurr++) {

			$iThisDate = strtotime("+" . $iCurr . " days", $iStart);

			$sDBDate = date("Y-m-d", $iThisDate);

			$aScheduleData[] = RetrieveTimes($sDBDate);

		}

		$aListData = array();
		for ($i = 0; $i < 7; $i++) {
			for ($j = 0; $j < count($aScheduleData[$i]); $j++) {

				$data = $aScheduleData[$i][$j];

				$aTempArray = array();
				$aTempArray['date'] = date("D n/j", strtotime($data['date']));
				$aTempArray['start'] = substr($data['start'], 0, -3);
				$aTempArray['stop'] = substr($data['stop'], 0, -3);

				$aListData[$i][] = $aTempArray;
			}
		}

		echo json_encode($aListData);
		exit();
		
	}

	else if ($_POST['type'] == 'getHistory') {

		$userid = $_POST['id'];

		$oData = new DataModel($userid, $dbconnection);

		$aResult = array();
		$aResult['points'] = $oData->GetPointsForUser();
		$aResult['monthpoints'] = $oData->GetPointsByMonth();
		$aResult['totalpoints'][0] = $oData->GetRecentPointsForUser(0);
		$aResult['totalpoints'][1] = $oData->GetRecentPointsForUser(1);
		$aResult['purchases'] = $oData->GetPurchasesForUser();
		$aResult['totalpurchases'] = $oData->GetTotalPurchasesForUser();
		$aResult['totalpayments'] = $oData->GetTotalPaymentsForUser();
		$aResult['userinfo'] = $oData->GetUserData();

		echo json_encode($aResult);
		exit();

	} else if ($_POST['type'] == 'getCurrentSkaters') {

		$oData = new DataModel(0, $dbconnection);
		$aSkaters = $oData->GetTodaysSkaters();

		echo json_encode($aSkaters);


		exit();

	} else if ($_POST['type'] == 'getUser') {

        if (!$sessionUser || $sessionRole != 3) {
            return false;
        }

		$uid = $_POST['id'];
		$oData = new DataModel($uid, $dbconnection);
		$aUser = $oData->GetUserData();

		echo json_encode($aUser);

	} else if ($_POST['type'] == "deleteUser") {

        if (!$sessionUser || $sessionRole != 3) {
            return false;
        }

		$uid = $_POST['id'];
		$oData = new DataModel($uid, $dbconnection);
		$aUser = $oData->DeleteUser();

	} else if ($_POST['type'] == 'getPoints') {
            
            $uid = $_POST['userid'];
            $oData = new DataModel($uid, $dbconnection);
            $points = $oData->GetRecentPointsForUser();
            
            echo $points;
            
    } else if ($_POST['type'] == 'isInvoiceOpen') {
        
        $uid = $_POST['userid'];
        $oData = new DataModel($uid, $dbconnection);
        $bOpen  = $oData->IsInvoiceOpen();
        
        echo $bOpen;
        
    } else if ($_POST['type'] == 'checkAdmin') {  

    	$pin = $_POST['pin'];
        $lname = $_POST['lname'];
    	$oData = new DataModel(0, $dbconnection);

        $result = $oData->IsAdminPIN($pin, $lname);

    	if ($result != false) {
    		echo "1";
    	} else {
    		echo "0";
    	}
    }