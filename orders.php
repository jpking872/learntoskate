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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $orderId = $_POST['order'];
    $correctPin = $_POST['pin'];
    $item = $_POST['item'];

    $aOrder = $oSquare->GetOrder($orderId, $item);
    $status = $aOrder['status'];

    $userId = $oSquare->GetUserIdFromPin($correctPin);
    if ($userId == false) {
        $message = "Invalid PIN.";
    } elseif ($status == "CANCELED") {
        $oSquare->UpdatePurchaseId($orderId, $item, "-1");
        $oSquare->UpdateUserId($orderId, $item, $userId);
        $oSquare->UpdateUserPin($orderId, $item, $correctPin);
    } else {
        $oDataModel = new DataModel($userId, $dbconnection);
        $purchaseData = $oSquare->BuildPurchaseData($userId, $aOrder['product_id'], $aOrder['quantity']);
        $result = $oDataModel->AddPurchase($purchaseData);
        if ($result) {
            $purchaseId = $oDataModel->GetLastInsertId() ?? -1;
            $oSquare->UpdatePurchaseId($orderId, $item, $purchaseId);
            $oSquare->UpdateUserId($orderId, $item, $userId);
            $oSquare->UpdateUserPin($orderId, $item, $correctPin);
        } else {
            writeLog("Add purchase failed");
        }
    }

}

$aOrders = $oSquare->GetRecentOrders();
$aProducts = $oSquare->GetProductsById();

include_once("header.php");

?>

<div class="infoBar">
    <div class="pageTitle">RECENT ORDERS</div>
</div>

<div class="main">

    <?php if (isset($message)) {
        echo "<p>" . $message . "</p>";
    } ?>

        <?php for ($i = 0; $i < count($aOrders); $i++) {
            $tmp = $aOrders[$i];
            $isValid = $tmp['uid'] != -1;
            $notEnteredClass = $isValid ? "gold" : "errorText";
        ?>
            <p>
                Order Id: <span class="<?php echo $notEnteredClass ?>"><?php echo $tmp['order_id'] ?> | <?php echo $tmp['item'] ?></span><br/>
                Skater Id: <span class="gold"><?php echo $tmp['uid'] ?></span> |
                Skater name: <span class="gold"><?php echo $tmp['square_display_name'] ?></span> |
                Skater pin: <span class="gold"><a href="/skaters.php?type=pin&query=<?php echo $tmp['skater_pin'] ?>"><?php echo $tmp['skater_pin'] ?></a></span> |
                Square email: <span class="gold"><?php echo $tmp['square_email'] ?></span><br/>
                Quantity: <span class="gold"><?php echo $tmp['quantity'] ?></span> |
                Package: <span class="gold"><?php echo $aProducts[$tmp['product_id']]['title'] ?></span><br/>
                Status: <span class="gold"><?php echo $tmp['status'] ?></span> |
                Purchase Id: <span class="gold"><?php echo $tmp['purchase_id'] ?></span> |
                Time: <span class="gold"><?php echo date($tmp['order_updated']) ?></span></p>

                <?php if (!$isValid) { ?>
                <form class="reconcilePurchase" method="post">
                    Correct PIN: <input type="text" class="pinInput" name="pin">
                    <input type="hidden" name="order" value="<?php echo $tmp['order_id'] ?>">
                    <input type="hidden" name="item" value="<?php echo $tmp['item'] ?>">
                    <input type="submit" value="Reconcile">
                </form>
                <?php } ?>

        <?php } ?>

    <p><a href="/enterOrder.php"><button class="orderButton">Enter an Order</button></a></p>
    <?php

    include_once("footer.php");

    ?>

