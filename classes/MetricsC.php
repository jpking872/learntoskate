<?php

class Metrics {

    public $db;

    public function __construct($db) {

        $this->db = $db;

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

}