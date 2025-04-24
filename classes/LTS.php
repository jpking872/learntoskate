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

    public function GetTodaysClasses($sDate)
    {
        $dayOfWeek = date("l", strtotime($sDate));
        $sql = "SELECT * FROM `sessions` WHERE `active` = 1 AND `day` = '" . $dayOfWeek . "'";
        $result = mysqli_query($this->db, $sql);
        $sessionClasses = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $sqlStart = date("Y-m-d H:i:s", strtotime($sDate . " " . $row['start']));
            $sqlEnd = date("Y-m-d H:i:s", strtotime($sDate . " " . $row['end']));
            $sql2 = "SELECT * FROM `classes` WHERE `active` = 1 AND `start` >= '" . $sqlStart . "' AND `end` <= '" . $sqlEnd . "' ORDER BY `id`";
            $result2 = mysqli_query($this->db, $sql2);
            $tmpClasses = [];
            while ($row2 = mysqli_fetch_assoc($result2)) {
                $tmpClasses[] = $row2;
            }
            $sessionClasses[] = array('session' => $row, 'classes'  => $tmpClasses);
        }

        return $sessionClasses;

    }

    public function GetLTSClasses($userLevel)
    {
        $sql = "SELECT * FROM `classes` WHERE `active` = 1 AND `start` > NOW() ORDER BY `start` ASC";
        $result = mysqli_query($this->db, $sql);
        $sessionClasses = [];
        while ($row = mysqli_fetch_assoc($result)) {

            $classLevels = explode("|", $row['level']);
            if (in_array($userLevel, $classLevels)) {
                $sessionClasses[] = $row;
            }

        }
        return $sessionClasses;

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



}
