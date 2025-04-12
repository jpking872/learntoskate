<?php

include_once("classes/ClassesC.php");
class LTS extends Classes
{

    public $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function GetTodaysClasses()
    {
        $todaysDay = date("l");
        $sql = "SELECT * FROM `sessions` WHERE `active` = 1 AND `day` = '" . $todaysDay . "'";
        $result = mysqli_query($this->db, $sql);
        $sessionClasses = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $sqlStart = date("Y-m-d H:i:s", strtotime("today " . $row['start']) - 60);
            $sqlEnd = date("Y-m-d H:i:s", strtotime("today " . $row['end']) - 60);
            $sql2 = "SELECT * FROM `classes` WHERE `active` = 1 AND `start` > '" . $sqlStart . "' AND `end` < '" . $sqlEnd . "' ORDER BY `id`";
            $result2 = mysqli_query($this->db, $sql2);
            $tmpClasses = [];
            while ($row2 = mysqli_fetch_assoc($result2)) {
                $tmpClasses[] = $row2;
            }
            $sessionClasses[] = array('session' => $row, 'classes'  => $tmpClasses);
        }

        return $sessionClasses;

    }




}
