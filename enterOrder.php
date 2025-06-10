<?php

include_once("incl/session.php");
include_once("incl/database.php");
include_once("incl/library.php");
include_once("incl/config.php");
include_once("classes/DataModelC.php");
include_once("classes/SquareC.php");

if (!$sessionUser || $sessionRole != 3) {
    header("Location: /login.php");
}

$oSquare = new Square($dbconnection);
$oDataModel = new DataModel(0, $dbconnection);
$errorText = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $aPostVars = $_POST;
    $orderId = $aPostVars['orderId'] ?? uniqid("lts");
    $name = $aPostVars["squareName"];
    $email = $aPostVars["squareEmail"];
    $pin = $aPostVars["skaterPin"];
    $product = $aPostVars["product"];
    $quantity = $aPostVars["quantity"];
    $orderDate = $aPostVars["orderDate"];

    $userId = $oSquare->GetUserIdFromPin($pin);
    if ($userId != false) {

        $orderData = [
            'order_id' => $orderId,
            'item' => 1,
            'uid' => $userId,
            'skater_pin' => $pin,
            'square_email' => $email,
            'square_display_name' => $name,
            'quantity' => $quantity,
            'product_id' => $product,
            'status' => "OPEN"
        ];

        $oSquare->SetSquareData($orderData);
        $result = $oSquare->InsertOrder();

        $purchaseData = $oSquare->BuildPurchaseData($userId, $product, $quantity);
        $purchaseData['admin'] = $sessionUser;
        $purchaseData['date'] = $orderDate;
        $purchaseData['note'] = "Manual Entry";

        //update registration
        if ($product == 4) {
            $oSquare->AddRegistration($userId, $orderDate);
            $purchaseData['note'] = "Registration";
        }

        writeLog(print_r($purchaseData, true));

        $oDataModel->SetUser($purchaseData['uid']);
        $result = $oDataModel->AddPurchase($purchaseData);
        if ($result) {
            $purchaseId = $oDataModel->GetLastInsertId() ?? -1;
            $oSquare->UpdatePurchaseId($orderData['order_id'], 1, $purchaseId);
            $errorText = "Added Order";
        } else {
            $errorText = "Failed adding Order";
            writeLog("Add purchase failed");
        }

    } else {

        $errorText = "Unable to find pin";
    }

}

$oSquare = new Square($dbconnection);
$aProducts = $oSquare->GetProductsById();

include_once("header.php");

?>

<div class="infoBar">
    <div class="pageTitle">ENTER ORDER</div>
</div>

<div class="main">

    <p><span class="errorText"><?php echo $errorText ?></span></p>

    <form id="enterOrder" method="post">
        <p>Square Order #:<br/><input type="text" name="orderId" maxlength="50"></p>
        <p>Square name:<br/><input type="text" name="squareName" maxlength="50"></p>
        <p>Square email:<br/><input type="text" name="squareEmail" maxlength="250"></p>
        <p>Skater Pin:<br/><input type="password" class="pinInput" name="skaterPin" maxlength="5"></p>
        <p>Product:<br/>
            <select class="orderDropdown" name="product">
        <?php foreach($aProducts as $key => $value) { ?>
            <option value="<?php echo $key ?>"><?php echo $value['title'] ?></option>
        <?php } ?>
            </select>
        </p>
        <p>Quantity:<br/>
            <select class="orderDropdown" name="quantity">
                <?php for ($i = 1; $i <= 10; $i++) { ?>
                    <option value="<?php echo $i ?>"><?php echo $i ?></option>
                <?php } ?>
            </select>
        </p>
        <p>Order Date:<br/><input type="text" class="orderDate" name="orderDate" maxlength="25" value="<?php echo date("Y-m-d") ?>"></p>
        <p><input type="submit" name="submitOrder" class="orderButton" value="Enter"></p>
    </form>



    <script type="text/javascript">

        $(document).ready(function () {
            $("#enterOrder").submit(function (e) {
                var errorText = "";
                if ($("input[name='orderId']").val().length < 5 || $("input[name='orderId']").val().length > 50) {
                    errorText += "Square order id is required.<br/>";
                }
                if ($("input[name='squareName']").val().length < 5 || $("input[name='squareName']").val().length > 50) {
                    errorText += "Square name is required.<br/>";
                }
                var pin = new RegExp('^[A-Za-z0-9]{5}$');
                if (!pin.test($("input[name='skaterPin']").val())) {
                    errorText += "Pin must be 5 letters or numbers.<br/> ";
                }
               var email = new RegExp('^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$');
                if (!email.test($("input[name='squareEmail']").val())) {
                    errorText += "Valid email is required.<br/> ";
                }
                var orderDate = new RegExp('^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$');
                if (!orderDate.test($("input[name='orderDate']").val())) {
                    errorText += "Valid date is required.<br/> ";
                }
                if (errorText.length > 0) {
                    $(".errorText").html(errorText);
                    return false;
                } else {
                    return true;
                }
            })
        })
    </script>

    <?php

    include_once("footer.php");

    ?>

