<?php

include_once("incl/session.php");
include_once("incl/database.php");
include_once("incl/library.php");
include_once("incl/config.php");
include_once("classes/DataModelC.php");

if (!$sessionUser || $sessionRole < 1) {
    header("Location: /login.php");
}

$jsonString = '{
        "quick_pay": {
            "name": "Auto Detailing",
            "price_money": {
                "amount": 10000,
                "currency": "USD"
            },
            "location_id": "L4YPF4T9PQD4R"
        }
    }';

$array = array('quick_pay'=>array('name'=>'Auto Detailing', 'price_money' => array('amount'=>10000, 'currency' => 'USD'), 'location_id' => 'L4YPF4T9PQD4R'));
$response = SquareAPICall(SQUARE_API_HOST . "/v2/online-checkout/payment-links", "POST", $jsonString);
print_r($response);

/*$paymentid = "NcZTsH0R38KxW72QND9kHTBfujeZY";
$response = SquareAPICall(SQUARE_API_HOST . "/v2/payments/" . $paymentid, "GET", '');
print_r($response);
echo "<br/><br/>";

$orderid = "4Hqdo1Q3l8xcV5qdR4XMoRzS5gQZY";
$response = SquareAPICall(SQUARE_API_HOST . "/v2/orders/" . $orderid, "GET", '');
print_r($response);
echo "<br/><br/>";
*/

$key = "skater-recipient";
$value = "Angie2";
$customerId = "J0XQHR32F9XA3WQJ9ZS11Q1V2R";

$setJson = '{
    "custom_attribute": {
    "key": "' . $key . '",
    "value": "' . $value . '",
    "visibility": "VISIBILITY_READ_WRITE_VALUES"
  }
}';

$response = SquareAPICall(SQUARE_API_HOST . "/v2/customers/$customerId/custom-attributes/$key", "POST", $setJson);
print_r($response);
echo "<br/><br/>";

$response = SquareAPICall(SQUARE_API_HOST . "/v2/customers/$customerId/custom-attributes/$key", "GET", '');
print_r($response);
echo "<br/><br/>";


include_once("header.php");

?>

<div class="main">

    <script type="text/javascript">

    </script>

    <?php

    include_once("footer.php");

    ?>

