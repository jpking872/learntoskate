<?php

function writeLog($message)
{
    $logFile = dirname(__DIR__, 3) . "/logs/logfile.log";

    $logFile = fopen($logFile, "a+") or die("Unable to open file!");
    $logMessage = gmdate("d-M-Y H:i:s") . " " . $message . "\n";
    fwrite($logFile, $logMessage);
    fclose($logFile);
}

function SquareAPICall($url, $method, $jsonString)
{

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_POSTFIELDS => $jsonString,
        CURLOPT_HTTPHEADER => array(
            'Square-Version: 2024-06-04',
            "Authorization: Bearer " . SQUARE_ACCESS_TOKEN,
            "Content-type: application/json"
        ),
    ));
    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);
    if ($err) {
        echo 'User subscription check error: ' . $err;
    } else {
        return $response;
    }

}

function ValidateSignUp($aInputData, $bPassword = TRUE)
{

    $aErrorArray = array();

    if ($aInputData['plan'] == 0) {

        $aFormInfo = array('plan', 'firstname', 'lastname', 'username', 'email', 'password', 'passconf');
        $aFormInfoStrings = array("plan", "first name", "last name", "username", "email", "password", "password confirmation");

    } else if ($aInputData['service'] == 'brand') {

        $aFormInfo = array('plan', 'firstname', 'lastname', 'username', 'email', 'password', 'passconf', 'address', 'city', 'state', 'zip', 'phone', 'website', 'business', 'listingCity', 'listingState', 'bring', 'description');
        $aFormInfoStrings = array('plan', 'first name', 'last name', 'username', 'email', 'password', 'password confirmation', 'address', 'city', 'state', 'zip', 'phone', 'website', 'business name', 'listing city', 'listing state', 'items to bring', 'description');

    } else {

        $aFormInfo = array('plan', 'firstname', 'lastname', 'username', 'email', 'password', 'passconf', 'address', 'city', 'state', 'zip', 'phone', 'website', 'business', 'listingCity', 'listingState', 'size', 'bring', 'description');
        $aFormInfoStrings = array('plan', 'first name', 'last name', 'username', 'email', 'password', 'password confirmation', 'address', 'city', 'state', 'zip', 'phone', 'website', 'business name', 'listing city', 'listing state', 'group size', 'items to bring', 'description');

    }

    if (!$bPassword) {
        array_splice($aFormInfo, 5, 1);
        array_splice($aFormInfo, 5, 1);
        array_splice($aFormInfoStrings, 5, 1);
        array_splice($aFormInfoStrings, 5, 1);
    }

    foreach ($aInputData as $key => $value) {

        $bValid = true;

        if (($elemIndex = array_search($key, $aFormInfo)) === FALSE) {

            continue;

        }

        if ($key == "email") {

            $sPattern = "/^[-0-9a-zA-Z.+_]+@[-0-9a-zA-Z.+_]+\.[a-zA-Z]{2,4}$/";
            $bValid = preg_match($sPattern, $value);

        } /*else if ($key == "website") {

				$sPattern = "/^http\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?$/";
				$bValid = preg_match($sPattern, $value);
			
			}*/

        /* else if ($key == "cardnumber") {

            $sPattern = "/^[0-9]{13,16}$/";
            $bValid = preg_match($sPattern, $value);

        } else if ($key == "cardcode") {

            $sPattern = "/^[0-9]{3,4}$/";
            $bValid = preg_match($sPattern, $value);

        } */ else if ($key == "password") {

            $bValid = ($value == $aInputData['passconf']) ? true : false;
            if ($bValid) {
                if (strlen($value) < 3 || strlen($value) > 15) {
                    $bValid = false;
                }
            }

        } else {

            $bValid = (strlen($value) > 0) ? true : false;

        }

        if (!$bValid) $aErrorArray[] = $key;

    }

    return (count($aErrorArray) == 0) ? true : false;

}

function BitEncode($aFields)
{

    $posbit = 0;

    foreach ($aFields as $key => $value) {

        $posbit += pow(2, $key) * $value;

    }

    return $posbit;

}

function BitDecode($iField, $iBits)
{

    $result = array();
    $iCount = $iField;

    for ($i = $iBits - 1; $i >= 0; $i--) {

        if ($iCount >= pow(2, $i)) {
            $result[] = $i;
            $iCount -= pow(2, $i);
        }
    }

    return $result;

}


function ConfirmPassword($iUID, $sPassword)
{

    global $dbconnection;

    $sql = "SELECT * FROM `users` WHERE `id` = '" . $iUID . "'";

    $result = mysqli_query($dbconnection, $sql);

    if ($row = mysqli_fetch_array($result)) {

        if ($row['password'] == md5($sPassword)) {

            return TRUE;
        }

    }

    return FALSE;
}


function UpdateSessionData()
{

    global $sessionUser;

    errortrack($sessionUser);

    $adata = GetUser($sessionUser);

    $_SESSION['id'] = $adata['id'];
    $_SESSION['name'] = $adata['username'];
    $_SESSION['role'] = $adata['role'];
    $_SESSION['email'] = $adata['email'];

    errortrack(print_r($_SESSION, true));

}

function CalculateExpirationDate($sdate)
{

    $lastpayment = strtotime($sdate);

    $expseconds = $lastpayment + (24 * 3600 * 365 / 12);

    $sexpdate = date("m/d/Y", $expseconds);

    return $sexpdate;
}

function GetUser($id)
{

    global $dbconnection;

    $sql = "SELECT * FROM `users` WHERE `id` = '" . $id . "'";

    errortrack($sql);

    $result = mysqli_query($dbconnection, $sql);
    $adata = mysqli_fetch_array($result);

    errortrack($adata);

    return $adata;
}

function UnSkypePhoneNumber($sPhone)
{

    $first = substr($sPhone, 0, 4);
    $second = substr($sPhone, 4, 3);
    $last = substr($sPhone, 7);

    $sUnSkype = "<span>" . $first . "</span><span>" . $second . "</span><span>" . $last . "</span>";

    return $sUnSkype;

}


function doRedirect($location)
{

    global $allowredirect;

    if ($allowredirect) {

        header("Location: " . $location);
        exit();

    } else {

        errortrack("Would have redirected to " . $location);
        echo echoerrortrack();
        exit();
    }
}

function errortrack($errorstring)
{

    global $errortrackon;

    if ($errortrackon == 1) {

        global $errorqueue;

        $ainfo = debug_backtrace();

        $errorqueue[] = array($ainfo[1]['file'], $ainfo[1]['line'], $errorstring);

    }

}

function echoerrortrack()
{

    global $errortrackon, $errorqueue;

    if ($errortrackon == 1) {

        $erroroutput = "=== Error Track ===\n";

        foreach ($errorqueue as $error) {

            $erroroutput .= $error[0] . " [" . $error[1] . "] " . $error[2] . "\n";

        }

        return "<pre>" . $erroroutput . "</pre>";

    }
}

function PerformLogin($aParams)
{

    $result = Login($aParams);

    return $result;

}

function UploadFile($aFileData)
{

    $allowedExts = array("gif", "jpeg", "jpg", "png");
    $temp = explode(".", $aFileData["profile"]["name"]);
    $extension = end($temp);

    if ((($aFileData["profile"]["type"] == "image/gif")
            || ($aFileData["profile"]["type"] == "image/jpeg")
            || ($aFileData["profile"]["type"] == "image/jpg")
            || ($aFileData["profile"]["type"] == "image/pjpeg")
            || ($aFileData["profile"]["type"] == "image/x-png")
            || ($aFileData["profile"]["type"] == "image/png"))
        && ($aFileData["profile"]["size"] < 5000000)
        && in_array($extension, $allowedExts)) {
        if ($aFileData["profile"]["error"] > 0) {
            return FALSE;
        } else {
            if (file_exists("upload/" . $aFileData["profile"]["name"])) {
                $newFile = "profile" . rand(0, 9) . rand(0, 9) . rand(0, 9) . rand(0, 9) . $aFileData["profile"]["name"];
                $bSuccess = move_uploaded_file($aFileData["profile"]["tmp_name"], "upload/" . $newFile);
                return $newFile;
            } else {
                $bSuccess = move_uploaded_file($aFileData["profile"]["tmp_name"], "upload/" . $aFileData["profile"]["name"]);
                return TRUE;
            }
        }
    } else {
        return FALSE;
    }


}
