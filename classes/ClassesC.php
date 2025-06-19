<?php

class Classes
{

    public $db;
    public $dataModel;

    public function __construct($db)
    {
        $this->db = $db;
        $this->dataModel = new DataModel(0, $db);
    }

    public function getClasses()
    {
        $sql = "SELECT * FROM `classes` WHERE `active` = 1 ORDER BY `start` ASC";
        $result = mysqli_query($this->db, $sql);

        $classesArray = [];

        while ($row = mysqli_fetch_array($result)) {
            $classesArray[] = $row;
        }

        return $classesArray;
    }

    public function getClassesByUid($uid)
    {

        $sql = "SELECT c.*, cu.`pass` FROM `classes` c INNER JOIN `class_user` cu ON c.`id` = cu.`classid` WHERE cu.`uid` = '" . mysqli_real_escape_string($this->db, $uid) . "' ORDER BY `start` DESC";
        $result = mysqli_query($this->db, $sql);

        $classesArray = [];

        while ($row = mysqli_fetch_array($result)) {
            $classesArray[] = $row;
        }

        return $classesArray;

    }

    public function getUsersByClass($cid)
    {
        $sql = "SELECT `uid` FROM `class_user` WHERE cu.`classid` = '" . mysqli_real_escape_string($this->db, $cid) . "'";
        $result = mysqli_query($this->db, $sql);

        $userArray = [];

        while ($row = mysqli_fetch_array($result)) {
            $userArray[] = $row['uid'];
        }

        return $userArray;

    }

    public function GetClassById($cid)
    {
        $sql = "SELECT * FROM `classes` WHERE `id` = '" . $cid . "'";
        $result = mysqli_query($this->db, $sql);

        if ($row = mysqli_fetch_array($result)) {
            return $row;
        } else {
            return false;
        }

    }

    public function addUserToClass($uid, $cid)
    {
        $startDate = $this->GetClassById($cid);
        $hasPass = $this->dataModel->HasUserPass($uid, date('n', strtotime($startDate['start'])), date("Y", strtotime($startDate['start'])) );

        $passVal = $hasPass ? 1 : 0;

        writeLog("Add user to class: " . $uid . " " . $cid);

        $sql = "INSERT INTO `class_user` (`uid`, `classid`, `pass`) 
            VALUES ('" . mysqli_real_escape_string($this->db, $uid) . "', '" .
            mysqli_real_escape_string($this->db, $cid) . "', '" . mysqli_real_escape_string($this->db, $passVal) . "')";

        $result = mysqli_query($this->db, $sql);

        return $result;
    }

    public function removeUserFromActiveClasses($uid)
    {
        $currentTime = date("Y-m-d H:i:s", time());

        $sql = "DELETE cu FROM `class_user` cu INNER JOIN `classes` c ON cu.`classid` = c.`id` WHERE cu.`uid` = '" .
                    mysqli_real_escape_string($this->db, $uid) . "' AND c.`active` = 1 AND `start` > '$currentTime'";

        writeLog($sql);

        $result = mysqli_query($this->db, $sql);
        return $result;

    }

    public function removeUserFromClass($uid, $cid)
    {
        $sql = "DELETE FROM `class_user` WHERE `uid` = '" . mysqli_real_escape_string($this->db, $uid) . "' 
                    AND `classid` = '" . mysqli_real_escape_string($this->db, $cid) . "' LIMIT 1";
        $result = mysqli_query($this->db, $sql);

        return $result;
    }

    public function getClassSize($cid) {
        $sql = "SELECT COUNT(*) as num FROM `class_user` WHERE `classid` = " . mysqli_real_escape_string($this->db, $cid);
        $result = mysqli_query($this->db, $sql);
        if ($row = mysqli_fetch_array($result)) {
            return $row['num'];
        } else {
            return false;
        }
    }

    public function getCurrentClasses() {
        
        $currTime = date("Y-m-d H:i:s");
        $sql = "SELECT * FROM `classes` WHERE `start` < '" . $currTime . "' AND `end` > '" . $currTime . "' AND `active` = 1 ORDER BY `start` ASC";
        $result = mysqli_query($this->db, $sql);
        $classesArray = [];
        while ($row = mysqli_fetch_array($result)) {
            $classesArray[] = $row;
        }
        return $classesArray;

    }

    public function getDaysClasses($date) {

        $sql = "SELECT * FROM `classes` WHERE DATE(`start`) = '" . $date . "' AND `active` >= 0 ORDER BY `start` ASC";
        $result = mysqli_query($this->db, $sql);
        $classesArray = [];
        while ($row = mysqli_fetch_array($result)) {
            $classesArray[] = $row;
        }
        return $classesArray;
    }

    public function getSkatersInClass($cid) {

        $sql = "SELECT u.*, c.`pass` FROM `class_user` c INNER JOIN `users` u ON u.id = c.uid WHERE c.classid = ' " . mysqli_real_escape_string($this->db, $cid) . "' ORDER BY c.`entered` ASC";
        $result = mysqli_query($this->db, $sql);
        $skatersArray = [];
        while ($row = mysqli_fetch_array($result)) {
            $skaterName = $row['sfname'] . " " . $row['slname'];

            if ($row['pass'] == 1 && 0) {
                $skatersArray[] = "<span class=\"green\">" . $skaterName . "</span>";
            } else {
                $skatersArray[] = $skaterName;
            }
        }
        return implode(", ", $skatersArray);
    }

    public function AddClass($title, $start, $end) {

        $sql = "INSERT INTO `classes` ( `title`, `start`, `end`, `size`, `cost`, `active`) VALUES ( 
					'" . mysqli_real_escape_string($this->db, $title) . "',
					'" . mysqli_real_escape_string($this->db, $start) . "',
					'" . mysqli_real_escape_string($this->db, $end) . "',
					'25', '1', '1')";
        $result = mysqli_query($this->db, $sql);

    }

    public function CancelClass($cid) {

        $this->EmptyMembersFromClass($cid);

        $sql = "UPDATE `classes` SET `active` = -1 WHERE `id` = '" . mysqli_real_escape_string($this->db, $cid) . "'";
        $result = mysqli_query($this->db, $sql);
        return $result;

    }

    public function DeleteClass($cid) {

        $this->EmptyMembersFromClass($cid);

        $sql = "DELETE FROM `classes` WHERE `id` = '" . mysqli_real_escape_string($this->db, $cid) . "' LIMIT 1";
        $result = mysqli_query($this->db, $sql);
        return $result;

    }

    public function EmptyMembersFromClass($cid) {

        $sql = "DELETE FROM `class_user` WHERE `classid` = '" . mysqli_real_escape_string($this->db, $cid) . "'";
        $result = mysqli_query($this->db, $sql);
         return $result;
    }

}
