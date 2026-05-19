<?php

include_once("DataModelC.php");

class Metrics {

    public $db;
    public $dataModel;

    public function __construct($db) {

        $this->db = $db;
        $this->dataModel = new DataModel(0, $db);

    }

    public function getTableCount($table) {

        $sql = "SELECT COUNT(*) as rowcount FROM `" . $table . "`";
        $result = $this->db->query($sql);
        $row = $result->fetch_assoc();
        return $row['rowcount'];

    }

    public function getTotalPurchases() {

        $sql = "SELECT SUM(`points`) as sum FROM `purchase`";
        $result = $this->db->query($sql);
        $row = $result->fetch_assoc();
        return $row['sum'];

    }

    public function getPastClasses() {

        if (date("l") != "Sunday") {
            $currentWeek = strtotime("last Sunday");
        } else {
            $currentWeek = strtotime("today");
        }

        $sqlDate = date("Y-m-d", $currentWeek);

        $sql = "SELECT COUNT(*) as class_count FROM `class_user` cu LEFT JOIN `classes` c ON cu.`classid` = c.`id` WHERE DATE(c.`start`) < '" . $sqlDate . "'";
        $result = $this->db->query($sql);
        $row = $result->fetch_assoc();
        return $row['class_count'];

    }

    public function activeSkaterPointBalance() {

        $allUsers = $this->dataModel->getAllUsers(true);

        $totalBalance = 0;
        for ($i = 0; $i < count($allUsers); $i++) {
            $this->dataModel->SetUser($allUsers[$i]['userid']);
            $tmpBalance = $this->dataModel->GetUserBalance();
            $totalBalance += $tmpBalance;
        }

        return $totalBalance;

    }
}