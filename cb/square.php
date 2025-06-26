<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/incl/database.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/incl/library.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/incl/config.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/DataModelC.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/SquareC.php");

$raw_post_data = file_get_contents('php://input');
//$raw_post_data = '{"merchant_id":"4Z3XK4ADTNFB8","type":"order.fulfillment.updated","event_id":"02aa2dac-c127-30b6-90f6-f9398c6254db","created_at":"2025-06-10T13:08:38.742119031Z","data":{"type":"order_fulfillment_updated","id":"lp3Sz2HV2GgYXRXrdmwu4pul6qZ","object":{"order_fulfillment_updated":{"created_at":"2025-06-10T13:07:40.556Z","fulfillment_update":[{"fulfillment_uid":"sRGQe6JZXsvVEtNW3VoBsC","new_state":"PROPOSED","old_state":"PROPOSED"}],"location_id":"2N1MFGS350ETN","order_id":"lp3Sz2HV2GgYXRXrdmwu4pul6qDZ","state":"OPEN","updated_at":"2025-06-10T13:08:36.938Z","version":13}}}}';
writeLog($raw_post_data);

$jsonObj = json_decode($raw_post_data);
global $dbconnection;
$oDataModel = new DataModel(0, $dbconnection);
$oSquare = new Square($dbconnection);
$products = $oSquare->GetProductsByCatalogId();
$productsById = $oSquare->GetProductsById();

writeLog($jsonObj->type);

switch($jsonObj->type) {
    case 'order.fulfillment.updated':

        $merchantId = $jsonObj->merchant_id;
        $orderId = $jsonObj->data->id;
        if ($jsonObj->data->object->order_fulfillment_updated->state == "DRAFT") {
            writeLog("draft");
            //exit();
        }

        //$orderId = "3k8Oc7b9h8APM0ALePt3VHuT0pqrs";

        if ($merchantId != ISUSA_MERCHANT_ID) {
            exit();
        }

        $response = SquareAPICall(SQUARE_API_HOST . "/v2/orders/$orderId", "GET", "");
        //$response = '{"order": {"id": "lp3Sz2HV2GgYXRXrdmwu4pul6qDZ","location_id": "2N1MFGS350ETN","line_items": [{"uid": "4xEpp2fLSpeK3OuNqkDU9","catalog_object_id": "PTJOHSBOYFGHTKQ4GTS32RC7","catalog_version": 1749240427764,"quantity": "1","name": "Learn to Skate to the Point Registration","variation_name": "Regular","base_price_money": {"amount": 3000,"currency": "USD"},"modifiers": [{"uid": "lPOQM1lhtYwRicEwIZADEC","base_price_money": {"amount": 0,"currency": "USD"},"total_price_money": {"amount": 0,"currency": "USD"},"name": "Skater Name: Lionel Hutz","quantity": "1"},{"uid": "Ai0v2xc1wOZX2wbTewr0g","base_price_money": {"amount": 0,"currency": "USD"},"total_price_money": {"amount": 0,"currency": "USD"},"name": "Skater Pin: 24680","quantity": "1"}],"gross_sales_money": {"amount": 3000,"currency": "USD"},"total_tax_money": {"amount": 0,"currency": "USD"},"total_discount_money": {"amount": 0,"currency": "USD"},"total_money": {"amount": 3000,"currency": "USD"},"variation_total_price_money": {"amount": 3000,"currency": "USD"},"item_type": "ITEM","total_service_charge_money": {"amount": 0,"currency": "USD"}}],"fulfillments": [{"uid": "sRGQe6JZXsvVEtNW3VoBsC","type": "SHIPMENT","state": "PROPOSED","shipment_details": {"recipient": {"display_name": "Christina Chang","email_address": "christinasunchang@gmail.com","phone_number": "+12144028298","address": {"address_line_1": "1733 Ronson Rd","locality": "Houston","administrative_district_level_1": "TX","postal_code": "77055","country": "US","first_name": "Christina","last_name": "Chang"}},"shipping_type": "No Shipping","placed_at": "2025-06-10T13:08:36.961Z"}}],"created_at": "2025-06-10T13:07:40.556Z","updated_at": "2025-06-10T13:08:40.088Z","state": "OPEN","version": 15,"reference_id": "298597638","total_tax_money": {"amount": 0,"currency": "USD"},"total_discount_money": {"amount": 0,"currency": "USD"},"total_tip_money": {"amount": 0,"currency": "USD"},"total_money": {"amount": 3000,"currency": "USD"},"tenders": [{"id": "3TAoBVQOcjKjCtioSxrsHLa3NvRZY","location_id": "2N1MFGS350ETN","transaction_id": "lp3Sz2HV2GgYXRXrdmwu4pul6qDZY","created_at": "2025-06-10T13:08:35Z","amount_money": {"amount": 3000,"currency": "USD"},"type": "CARD","card_details": {"status": "CAPTURED","card": {"card_brand": "MASTERCARD","last_4": "1572","exp_month": 11,"exp_year": 2027,"fingerprint": "sq-1-lUxmfpZ2rR9nUK_Xk59NIIfvQ4dvXFrOvTbsZMjVW-mVzm48aBI-g9NfTWLr1yM81A","card_type": "CREDIT","prepaid_type": "NOT_PREPAID","bin": "542418"},"entry_method": "KEYED"},"payment_id": "3TAoBVQOcjKjCtioSxrsHLa3NvRZY"}],"service_charges": [{"uid": "ygZcCSUNC3nL5CRrAeqAFD","name": "Shipping","amount_money": {"amount": 0,"currency": "USD"},"applied_money": {"amount": 0,"currency": "USD"},"calculation_phase": "SUBTOTAL_PHASE","taxable": true,"total_money": {"amount": 0,"currency": "USD"},"total_tax_money": {"amount": 0,"currency": "USD"},"type": "CUSTOM","treatment_type": "LINE_ITEM_TREATMENT"}],"total_service_charge_money": {"amount": 0,"currency": "USD"},"net_amounts": {"total_money": {"amount": 3000,"currency": "USD"},"tax_money": {"amount": 0,"currency": "USD"},"discount_money": {"amount": 0,"currency": "USD"},"tip_money": {"amount": 0,"currency": "USD"},"service_charge_money": {"amount": 0,"currency": "USD"}},"source": {"name": "Square Online"},"pricing_options": {"auto_apply_discounts": true,"auto_apply_taxes": false},"net_amount_due_money": {"amount": 0,"currency": "USD"}}}';
        $jsonOrder = json_decode($response);

        writeLog(print_r($response, true));

        $email = $jsonOrder->order->fulfillments[0]->shipment_details->recipient->email_address ?? '';
        $displayName = $jsonOrder->order->fulfillments[0]->shipment_details->recipient->display_name ?? '';
        $state = $jsonOrder->order->state ?? 'none';

        $items = $jsonOrder->order->line_items;

        for ($i = 0; $i < count($items); $i++) {
            $quantity = $items[$i]->quantity ?? 0;

            if ($items[$i]->catalog_object_id == "PTJOHSBOYFGHTKQ4GTS32RC7" || $items[$i]->catalog_object_id == "F35KK4MEYSEEY3YE45GEXWAL") {
                $catalogId = $items[$i]->catalog_object_id ?? '';
                $skaterName = substr($items[$i]->modifiers[0]->name, 13) ?? '';
                $skaterPin = substr($items[$i]->modifiers[1]->name, 12) ?? '';
            } else {
                $catalogId = $items[$i]->modifiers[0]->catalog_object_id ?? '';
                $skaterName = substr($items[$i]->modifiers[1]->name, 13) ?? '';
                $skaterPin = substr($items[$i]->modifiers[2]->name, 12) ?? '';
            }

            $freestylePackage = $products[$catalogId] ?? 0;
            $item = $i + 1;

            /*if ($state == "COMPLETED") {
                $result = $oSquare->UpdateStatus($orderId, $item,"COMPLETED");
                continue;
            }*/

        $userId = $oSquare->GetUserIdFromPin($skaterPin);
        if ($userId == false) {
            writeLog("Invalid pin");
            $userId = -1;
        }

        writeLog("merchant id: " . $merchantId . " state: " . $state . " order id: " . $orderId . " email_address: " . $email . " skater: " . $skaterPin . " - " . $skaterName . " quantity: " . $quantity . " package: " . $catalogId . "-" . $freestylePackage);
        writeLog(print_r($jsonOrder, true));

        if ($freestylePackage > 0 && $state != "DRAFT") {
            $orderData = [
                'order_id' => $orderId,
                'item' => $item,
                'uid' => $userId,
                'skater_pin' => $skaterPin,
                'square_email' => $email,
                'square_display_name' => $displayName,
                'quantity' => $quantity,
                'product_id' => $freestylePackage,
                'status' => $state
            ];
            $oSquare->SetSquareData($orderData);
            $aOrder = $oSquare->GetOrder($orderId, $item);
            if (!$aOrder) {
                if ($oSquare->RecentCanceledOrder($email)) {
                    writeLog("callback recent canceled order, central time: " . date("Y-m-d H:i:s"));
                    writeLog(print_r($orderData, true));
                    continue;
                }
                $result = $oSquare->InsertOrder();
                if ($result) {
                    $aOrder = $oSquare->GetOrder($orderId, $item);
                } else {
                    return false;
                }
            }
            else {
                $result = $oSquare->UpdateOrder();
                if (!$result) {
                    return false;
                }
            }

            if ($state == "OPEN" && $aOrder['purchase_id'] == 0 && $userId != -1) {

                $purchaseData = $oSquare->BuildPurchaseData($userId, $freestylePackage, $quantity);
                writeLog(print_r($purchaseData, true));

                $oDataModel->SetUser($purchaseData['uid']);
                $result = $oDataModel->AddPurchase($purchaseData);
                if ($result) {
                    $purchaseId = $oDataModel->GetLastInsertId() ?? -1;
                    $oSquare->UpdatePurchaseId($orderData['order_id'], $item, $purchaseId);
                } else {
                    writeLog("Add purchase failed");
                }

                if ($freestylePackage == 4) {
                    $oSquare->AddRegistration($userId, date("Y-m-d"));
                }

            }

        }
        }

    break;
    case 'payment.updated':
        break;
    default:
        break;
}

?>