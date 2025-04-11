<?php

require_once("../incl/session.php");
require_once("../incl/database.php");
require_once("../incl/library.php");
require_once("../incl/config.php");
require_once("../classes/DataModelC.php");
require_once("../classes/SquareC.php");

if (!$sessionUser || $sessionRole != 3) {
    header("Location: /login.php");
}

global $dbconnection;

$sql = "SELECT * FROM `orders` WHERE `status` = 'OPEN' AND `purchase_id` > 0 LIMIT 1";
$result = mysqli_query($dbconnection, $sql);

while ($row = mysqli_fetch_array($result)) {
    $orderId = $row['order_id'];
    $order = SquareAPICall(SQUARE_API_HOST . "/v2/orders/$orderId", "GET", "");
    writeLog(print_r($order, true));
    $version = $jsonOrder->order->version ?? 1;

    $jsonString =
        '{
        "order": {
          "location_id": "L4YPF4T9PQD4R",
          "fulfillments": [
            {
              "state": "COMPLETED"
            }
          ],
          "version": $version,
          "state": "COMPLETED"
        }
      }';

    $response = SquareAPICall(SQUARE_API_HOST . "/v2/orders/$orderId", "PUT", $jsonString);
    writeLog(print_r($response, true));

    $order = SquareAPICall(SQUARE_API_HOST . "/v2/orders/$orderId", "GET", "");
    writeLog(print_r($order, true));
}