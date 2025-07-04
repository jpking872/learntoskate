<?php

include_once("classes/ClassesC.php");
include_once("classes/DataModelC.php");
class LTS extends Classes
{

    public $db;
    public $dataModel;

    public function __construct($db)
    {
        $this->db = $db;
        $this->dataModel = new DataModel(0, $db);
    }

    public function GetDayOfClasses($sDate)
    {
        $sql = "SELECT DISTINCT `start` from `classes` WHERE DATE(`start`) = '" . $sDate . "'";
        $result = mysqli_query($this->db, $sql);
        $sessionClasses = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $sql2 = "SELECT * FROM `classes` WHERE `start` = '" . $row['start'] . "' ORDER BY `id`";
            $result2 = mysqli_query($this->db, $sql2);
            $tmpClasses = [];
            while ($row2 = mysqli_fetch_assoc($result2)) {
                $tmpClasses[] = $row2;
            }
            $sessionClasses[] = array('session' => $row, 'classes'  => $tmpClasses);
        }

        return $sessionClasses;

    }

    public function GetLTSClasses($userData)
    {
        $currentTime = date("Y-m-d H:i:s", strtotime("today"));

        $sql = "SELECT * FROM `classes` WHERE `active` = 1 AND `start` > '" . $currentTime . "' ORDER BY `start` ASC";
        $result = mysqli_query($this->db, $sql);

        $futureClasses = $this->GetClassesInFuture($userData['id']);
        $sessionClasses = [];
        $currentClasses = [];
        while ($row = mysqli_fetch_assoc($result)) {

            $classLevels = explode("|", $row['level']);
            if (in_array($userData['level'], $classLevels)) {
                $sessionClasses[] = $row;
                $currentClasses[] = $row['id'];
            }

        }

        for($i = 0; $i < count($futureClasses); $i++) {
            $tmp = $futureClasses[$i];
            if (in_array($tmp['id'], $currentClasses)) {
                continue;
            } else {
                $sessionClasses[] = $tmp;
            }
        }

        return $sessionClasses;

    }

    private function GetClassesInFuture($uid)
    {
        $currentTime = date("Y-m-d H:i:s", time());

        $sql = "SELECT c.* FROM `classes` c INNER JOIN `class_user` cu ON c.`id` = cu.`classid` 
                      WHERE cu.`uid` = '" . mysqli_real_escape_string($this->db, $uid) . "' AND c.`start` > '" . $currentTime . "' ORDER BY c.`start` ASC";
        $result = mysqli_query($this->db, $sql);
        $futureClasses = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $futureClasses[] = $row;
        }

        return $futureClasses;

    }

    public function GetLevels()
    {
        $sql = "SELECT * FROM `levels` ORDER BY `priority` ASC";
        $result = mysqli_query($this->db, $sql);
        $levelsArray = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $levelsArray[] = array("id" => $row['id'], "level" => $row['level']);
        }
        return $levelsArray;
    }

    public function AddLTSClass($title, $start, $end, $levels) {

        $sql = "INSERT INTO `classes` ( `title`, `start`, `end`, `level`, `size`, `cost`, `active`) VALUES ( 
					'" . mysqli_real_escape_string($this->db, $title) . "',
					'" . mysqli_real_escape_string($this->db, $start) . "',
					'" . mysqli_real_escape_string($this->db, $end) . "',
					'" . mysqli_real_escape_string($this->db, $levels) . "',
					'25', '1', '1')";
        $result = mysqli_query($this->db, $sql);

    }

    public function VerifyBalance($uid, $classArray) {

        $numToAdd = 0;

        for($i = 0; $i < count($classArray); $i++) {
            if (!$this->HasPassThisClass($uid, $classArray[$i])) {
                $numToAdd++;
            }
        }

        $numActive = $this->GetNumberActiveClasses($uid);
        $oData = new DataModel($uid, $this->db);
        $beforeBalance = $oData->GetUserBalance();

        writeLog($uid . " " . $beforeBalance . " " . $numActive . " " . $numToAdd);

        $afterBalance = $beforeBalance + $numActive - $numToAdd;

        return $afterBalance >= 0;
    }

    private function HasPassThisClass($uid, $cid) {

        $sql = "SELECT * FROM `classes` WHERE `id` = '" . $cid . "'";
        $result = mysqli_query($this->db, $sql);
        if ($row = mysqli_fetch_assoc($result)) {
            $classMonth = date("n", strtotime($row['start']));
            $classYear = date("Y", strtotime($row['start']));
        }

        $passClassDay = $this->dataModel->HasUserPass($uid, $classMonth, $classYear);

        return $passClassDay;

    }

    private function GetNumberActiveClasses($uid) {

        $currentTime = date("Y-m-d H:i:s", time());

        $sql = "SELECT * FROM `class_user` cu INNER JOIN `classes` c ON cu.`classid` = c.`id` WHERE cu.`uid` = '" .
            mysqli_real_escape_string($this->db, $uid) . "' AND c.`active` = 1 AND cu.`pass` = 0 AND `start` > '$currentTime'";

        $result = mysqli_query($this->db, $sql);

        return mysqli_num_rows($result);

    }

    public function SendSingleEmail($emailTo, $payload, $template)
    {
        $templateModel = [];

        $templateModel['email'] = $emailTo;

        if (is_array($payload) && count($payload) > 0) {
            foreach ($payload as $key => $value) {
                $templateModel[$key] = $value;
            }
        }

        $message = array(
            "From" => "admin@skatetothepoint.com",
            "To" => $emailTo,
            "TemplateID" => EMAIL_TEMPLATES[$template],
            "TemplateModel" => $templateModel
        );

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://api.postmarkapp.com/email/withTemplate",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => json_encode($message),
            CURLOPT_HTTPHEADER => array(
                "Accept: application/json",
                "Content-Type: application/json",
                "X-Postmark-Server-Token: " . POSTMARK_TOKEN
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        writeLog($response);
        if ($err) {
            return false;
        } else {
            $response = json_decode($response);
            return $response;
        }

    }


}
