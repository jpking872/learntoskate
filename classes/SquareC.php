<?php

include_once("DataModelC.php");

class Square {

    public $db;
    public $squareData;
    public $dataModel;

    public function __construct($db) {
        $this->db = $db;
        $this->dataModel = new DataModel(0, $db);
    }

    function SetSquareData($orderData) {
        $this->squareData = $orderData;
    }

    function GetProductsByCatalogId() {

        $sql = "SELECT * FROM `products`";
        $result = mysqli_query($this->db, $sql);

        $productsArray = [];

        while ($row = mysqli_fetch_array($result)) {
            $productsArray[$row['catalog_id']] = $row['id'];
        }

        return $productsArray;

    }

    function GetProductsById() {

        $sql = "SELECT * FROM `products`";
        $result = mysqli_query($this->db, $sql);

        $productsArray = [];

        while ($row = mysqli_fetch_array($result)) {
            $productsArray[$row['id']] = $row;
        }

        return $productsArray;

    }

    function GetUserIdFromPin($skaterPin) {

        $skaterPin = trim($skaterPin);

        $sql = "SELECT * FROM `users` WHERE `pin` = '" . mysqli_real_escape_string($this->db, $skaterPin) . "' ORDER BY `id` DESC LIMIT 1";
        $result = mysqli_query($this->db, $sql);

        if ($row = mysqli_fetch_array($result)) {
            $userId = $row['id'];
            return $userId;
        }
        return false;

    }

    function GetUserIdFromNameAndPin($skaterName, $skaterPin) {

        writeLog("checking name: $skaterName and pin: $skaterPin");

        $nameArray = explode(" ", $skaterName);
        $skaterFirst = $nameArray[0];
        $skaterLast = "";
        for ($i = 1; $i < count($nameArray); $i++) {
            $skaterLast .= $nameArray[$i] . " ";
        }
        rtrim($skaterLast);
        $skaterPin = trim($skaterPin);

        $sql = "SELECT * FROM `users` WHERE `sfname` = '" . mysqli_real_escape_string($this->db, $skaterFirst) . "'
            AND `slname` = '" . mysqli_real_escape_string($this->db, $skaterLast) . "' ORDER BY `id` DESC LIMIT 1";
        $result = mysqli_query($this->db, $sql);

        $numReturned = mysqli_num_rows($result);

        if ($numReturned === 1) {
            $skater = mysqli_fetch_assoc($result);
            writeLog("name match: " . $skaterName);
            return ['id' => $skater['id'], 'pin' => $skater['pin']];
        }

        $sql = "SELECT * FROM `users` WHERE `pin` = '" . mysqli_real_escape_string($this->db, $skaterPin) . "' ORDER BY `id` DESC LIMIT 1";
        $result = mysqli_query($this->db, $sql);

        if ($row = mysqli_fetch_array($result)) {
            writeLog("pin match: " . $skaterPin);
            $skaterFirstNames = explode(" ", $row['sfname']);
            $skaterLastNames = explode(" ", $row['slname']);
            $parentFirstNames = explode(" ", $row['fname']);
            $parentLastNames = explode(" ", $row['lname']);
            $allNames = array_merge($skaterFirstNames, $skaterLastNames, $parentFirstNames, $parentLastNames);

            for ($j = 0; $j < count($allNames); $j++) {
                if (strlen($allNames[$j]) >= 2 && $allNames[$j] != strtolower("n/a")) {
                    if (stristr($skaterName, $allNames[$j])) {
                        writeLog("pin confirmed: " . $skaterName);
                        return ['id' => $row['id'], 'pin' => $row['pin']];
                    }
                }
            }
        }
        writeLog("No match");
        return false;

    }

    function GetOrder($orderId, $item) {

        $sql = "SELECT * FROM `orders` WHERE `order_id` =  '" . mysqli_real_escape_string($this->db, $orderId) . "' AND `item` = '" . $item . "' ORDER BY `id` DESC LIMIT 1";
        $result = mysqli_query($this->db, $sql);

        if ($result) {
            if ($row = mysqli_fetch_array($result)) {
                return $row;
            }
        }
        return false;
    }

    function GetRecentOrders() {

        $sql = "SELECT * FROM `orders` WHERE `order_updated` > (DATE_SUB(CURDATE(), INTERVAL 5 DAY)) ORDER BY `order_updated` DESC";
        $result = mysqli_query($this->db, $sql);

        $aOrders = [];
        while($row = mysqli_fetch_array($result)) {
            $aOrders[] = $row;
        }

        return $aOrders;
    }

    function RecentCanceledOrder($email) {

        $result2 = mysqli_query($this->db, "SELECT NOW()");
        if ($row = mysqli_fetch_array($result2)) {
            $currTime = strtotime($row[0]);
        }

        $sql = "SELECT * FROM `orders` WHERE `square_email` = '" . $email . "' ORDER BY `order_updated` DESC LIMIT 1";
        $result = mysqli_query($this->db, $sql);

        if ($row = mysqli_fetch_array($result)) {
            if ($row['status'] == 'CANCELED' && strtotime($row['order_updated']) > $currTime - 30) {
                writeLog("class recent canceled order, mst time: " . date("Y-m-d H:i:s", $currTime));
                writeLog(print_r($row, true));
                return true;
            }
        }

        return false;

    }

    function InsertOrder() {

        if (!isset($this->squareData)) return false;
        $orderData = $this->squareData;

        foreach ($orderData as $key => $value) {
            $orderData[$key] = mysqli_real_escape_string($this->db, $value);
        }

        $sql = "INSERT INTO `orders` (order_id, item, uid, skater_pin, square_email, square_display_name, quantity, product_id, status) VALUES (
                        '" . $orderData['order_id'] . "', 
                        '" . $orderData['item'] . "', 
                        '" . $orderData['uid'] . "', 
                        '" . $orderData['skater_pin'] . "', 
                        '" . $orderData['square_email'] . "', 
                        '" . $orderData['square_display_name'] . "', 
                        '" . $orderData['quantity'] . "', 
                        '" . $orderData['product_id'] . "', 
                        '" . $orderData['status'] . "' 
                        )";

        $result = mysqli_query($this->db, $sql);

        return $result;

    }

    function UpdateOrder()
    {
        if (!isset($this->squareData)) return false;
        $orderData = $this->squareData;

        $sqladd = "";

        foreach ($orderData as $key => $value) {

            if ($key == 'order_id') continue;

            $sqladd .= "`" . $key . "` = '" . mysqli_real_escape_string($this->db, $value) . "', ";
        }

        $sqladd = substr($sqladd, 0, -2);
        $sql = "UPDATE `orders` SET " . $sqladd . " WHERE `order_id` = '" . mysqli_real_escape_string($this->db, $orderData['order_id']) . "' AND `item` = '" . $orderData['item'] . "' LIMIT 1";

        $result = mysqli_query($this->db, $sql);

        return $result;

    }

    function BuildPurchaseData($userId, $freestylePackage, $quantity) {

        $purchaseData = [];
        $purchaseData['uid'] = $userId;

        $productsById = $this->GetProductsById();
        $currentProduct = $productsById[$freestylePackage];
        $purchaseData['points'] = $currentProduct['num_sessions'] * $quantity;
        if ($currentProduct['num_sessions'] == 9999) {
            $purchaseData['points'] = 0;
        } else {
            $purchaseData['points'] = $currentProduct['num_sessions'] * $quantity;
        }
        $purchaseData['price'] = $currentProduct['price'] * $quantity;
        $purchaseData['date'] = date("Y-m-d");
        $purchaseData['note'] = "Square";

        if ($currentProduct['num_sessions'] == "9999") {
            $month = date("n");
            $year = date("Y");
            $currentDate = date("j");

            if ($currentDate > 15) {
                $month++;
                if ($month == 13) {
                    $month = 1;
                    $year++;
                }
            }

            $purchaseData['pass'] = $month . "-" . $year;
        } else {
            $purchaseData['pass'] = "";
        }

        return $purchaseData;
    }

    function AddRegistration($uid, $orderDate) {

        $sql = "UPDATE `users` SET `registration` = '" . mysqli_real_escape_string($this->db, $orderDate) .
            "' WHERE `id` = '" . mysqli_real_escape_string($this->db, $uid) . "' LIMIT 1";

        $result = mysqli_query($this->db, $sql);

        return $result;

    }

    function UpdateUserId($orderId, $item, $userId) {

        $sql = "UPDATE `orders` SET `uid` = '" . mysqli_real_escape_string($this->db, $userId) .
            "' WHERE `order_id` = '" . mysqli_real_escape_string($this->db, $orderId) . "' AND `item` = '" . $item . "' LIMIT 1";

        $result = mysqli_query($this->db, $sql);

        return $result;

    }

    function UpdateUserPin($orderId, $item, $pin) {

        $sql = "UPDATE `orders` SET `skater_pin` = '" . mysqli_real_escape_string($this->db, $pin) .
            "' WHERE `order_id` = '" . mysqli_real_escape_string($this->db, $orderId) . "' AND `item` = '" . $item . "' LIMIT 1";

        $result = mysqli_query($this->db, $sql);

        return $result;

    }

    function UpdateStatus($orderId, $item, $status) {

        $sql = "UPDATE `orders` SET `status` = '" . mysqli_real_escape_string($this->db, $status) .
            "' WHERE `order_id` = '" . mysqli_real_escape_string($this->db, $orderId) . "' AND `item` = '" . $item . "' LIMIT 1";

        $result = mysqli_query($this->db, $sql);

        return $result;

    }

    function UpdatePurchaseId($orderId, $item, $purchaseId) {

        $sql = "UPDATE `orders` SET `purchase_id` = '" . mysqli_real_escape_string($this->db, $purchaseId) .
            "' WHERE `order_id` = '" . mysqli_real_escape_string($this->db, $orderId) . "' AND `item` = '" . $item . "' LIMIT 1";

        $result = mysqli_query($this->db, $sql);

        return $result;

    }
}