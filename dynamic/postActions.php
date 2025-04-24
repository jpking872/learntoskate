<?php

include_once("../incl/session.php");
include_once("../incl/database.php");
include_once("../incl/library.php");
include_once("../incl/config.php");
include_once("../classes/DataModelC.php");

if (!$sessionUser || $sessionRole != 3) {
	header("Location: /login.php");
}

if ($_POST['actiontype'] == "adjust") {

	$aPostVar = array('uid' => $_POST['userid'], 'price' => $_POST['price'], 'points' => $_POST['points'], 'pass' => $_POST['pass'], 'note' => $_POST['note']);
	$aPostVar['date'] = date("Y-m-d H:i:s", strtotime($_POST['date']));

	$oData = new DataModel($aPostVar['uid'], $dbconnection);
	$todaysPoints = $oData->TodaysAdjustedPoints() + $aPostVar['points'];

	if ($todaysPoints <= MAX_POINTS_DAY) {

		$aPostVar['admin'] = $_SESSION['id'];
		$aPostVar['ipaddr'] = $_SERVER['REMOTE_ADDR'];

		$result = $oData->AddPurchase($aPostVar);
	} else {
		$result = false;
	}

	$message ="";
	if ($result) {
		$message = "?result=" . urlencode("Points added successfully.");
	} else {
		$message = "?result=" . urlencode("Error adding points.");
	}

	header("Location: /skaters.php" . $message);
	exit();

} 

elseif ($_POST['actiontype'] == "email") {

	try {

		$subj = $_POST['subject'];
		$toemail = $_POST['skaterEmail'];
		$toname = $_POST['skaterName'];
		$msg = nl2br($_POST['message']);

	    $mandrill = new Mandrill('e9emj3L7VMFa4qfVY_3dAw');
	    $message = array(
	        'html' => $msg,
	        'text' => null,
	        'subject' => $subj,
	        'from_email' => 'skating.director@gmail.com',
	        'from_name' => 'Alexey Gruber',
	        'to' => array(
           	array(
                	'email' => $toemail,
                	'name' => $toname,
                	'type' => 'to'
            	)
        	),
	        'headers' => array('Reply-To' => 'skating.director@gmail.com'),
	        'important' => false,
	        'track_opens' => null,
	        'track_clicks' => null,
	        'auto_text' => null,
	        'auto_html' => null,
	        'inline_css' => null,
	        'url_strip_qs' => null,
	        'preserve_recipients' => null,
	        'view_content_link' => null,
	        'bcc_address' => null,
	        'tracking_domain' => null,
	        'signing_domain' => null,
	        'return_path_domain' => null,
	        'merge' => true,
	        'merge_language' => 'mailchimp',
	        'global_merge_vars' => null,
	        'merge_vars' => null,
	        'tags' => null,
	        'subaccount' => null,
	        'google_analytics_domains' => null,
	        'google_analytics_campaign' => null,
	        'metadata' => null,
	        'recipient_metadata' => null,
	        'attachments' => null,
	        'images' => null
	    );

	    $async = FALSE;
	    $ip_pool = 'Main Pool';
	    $send_at = '';
	    $result = $mandrill->messages->send($message, $async, $ip_pool, $send_at);

	    $message = "";
	    if ($result[0]['status'] == 'sent') {

			$message = "?result=" . urlencode("Email sent successfully.");
		
		} else {
			
			$message = "?result=" . urlencode("Error sending email.");
		}

		header("Location: /user.php" . $message);
		exit();
	  
		} catch(Mandrill_Error $e) {
		    // Mandrill errors are thrown as exceptions
		    echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
		    // A mandrill error occurred: Mandrill_Unknown_Subaccount - No subaccount exists with the id 'customer-123'
		    throw $e;
		}

	
}

elseif ($_POST['actiontype'] == 'copyschedule') {

	if (date('l') == "Sunday") {
		$starttime = "today";
	} else {
		$starttime = "last Sunday";
	}

	$timestart = strtotime($starttime);

	$nextSunday = date("Y-m-d", strtotime("next Sunday"));

	$sql = "DELETE FROM `schedule` WHERE `date` >= '" . $nextSunday . "'";
	$result = mysqli_query($dbconnection, $sql);

	$count = 0;
	for ($i = $timestart; $i < $timestart + (7 * 24 * 3600); $i += 24 * 3600) {

		$copyDate = date("Y-m-d", $i);
		echo $copyDate;

		$currentDate = date("Y-m-d", strtotime($copyDate) + 7 * 24 * 3600);
		echo $currentDate;

		$sql = "SELECT * FROM `schedule` WHERE `date` = '" . $copyDate . "'";
		$result = mysqli_query($dbconnection, $sql);

		$aDayData = array();
		while ($row = mysqli_fetch_array($result)) {
			$aDayData[] = array('date' => $currentDate, 'start' => $row['start'], 'stop' => $row['stop']);
		}

		for ($j = 0; $j < count($aDayData); $j++) {

			$sql2 = "INSERT INTO `schedule` ( `date`, `start`, `stop`, `entered`) VALUES ( 
					'" . mysqli_real_escape_string($dbconnection, $currentDate) . "',
					'" . mysqli_real_escape_string($dbconnection, $aDayData[$j]['start']) . "',
					'" . mysqli_real_escape_string($dbconnection, $aDayData[$j]['stop']) . "',
					NOW())";
			$result2 = mysqli_query($dbconnection, $sql2);

		}


		$count++;

	}

	header("Location: /schedule.php");
	exit();

}



?>
