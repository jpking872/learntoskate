<?php

require_once($_SERVER['DOCUMENT_ROOT'] . "/incl/database.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/incl/library.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/incl/config.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/DataModelC.php");
require_once($_SERVER['DOCUMENT_ROOT'] . "/classes/SquareC.php");

$raw_post_data = file_get_contents('php://input');
//$raw_post_data = '{"merchant_id":"4Z3XK4ADTNFB8","type":"order.fulfillment.updated","event_id":"6f79a2ea-47c3-3ec0-8e70-1286981a282b","created_at":"2024-08-05T14:46:34Z","data":{"type":"order_fulfillment_updated","id":"dhrN4uQUF9eo4I1v2nvSPpRdfHXZY","object":{"order_fulfillment_updated":{"created_at":"2024-08-05T14:46:30.424Z","fulfillment_update":[{"fulfillment_uid":"vsj1b8Kz32xMHNkZcPl7gC","new_state":"PROPOSED","old_state":"PROPOSED"}],"location_id":"2N1MFGS350ETN","order_id":"dhrN4uQUF9eo4I1v2nvSPpRdfHXZY","state":"OPEN","updated_at":"2024-08-05T14:46:34.155Z","version":4}}}}';
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
        //$orderId = "RvlrZtMFazhIjmxDfbltfAhfkOWZY";

        if ($merchantId != ISUSA_MERCHANT_ID) {
            return;
        }

        $response = SquareAPICall(SQUARE_API_HOST . "/v2/orders/$orderId", "GET", "");
        //$response = '{"order": {"id": "dhrN4uQUF9eo4I1v2nvSPpRdfHXZY","location_id": "2N1MFGS350ETN","line_items": [{"uid": "ZzmhCYj6RJeBuDndOKMLS","catalog_object_id": "RRU3LPKMQGAD3KJB3U3IM6P4","catalog_version": 1722829720913,"quantity": "1","name": "Freestyle Sessions","variation_name": "Regular","base_price_money": {"amount": 0,"currency": "USD"},"modifiers": [{"uid": "xbWDyzClRRZqx81CCZEWcB","base_price_money": {"amount": 0,"currency": "USD"},"total_price_money": {"amount": 0,"currency": "USD"},"name": "Enter pin of skater: 92110","quantity": "1"},{"uid": "GMr70QrNhVoLAZCMZXs5ZC","base_price_money": {"amount": 26500,"currency": "USD"},"total_price_money": {"amount": 26500,"currency": "USD"},"name": "30 Freestyle Sessions","catalog_object_id": "RAVTU4HFZ7FU45LBXUOCNY3V","catalog_version": 1722829720913,"quantity": "1"}],"gross_sales_money": {"amount": 26500,"currency": "USD"},"total_tax_money": {"amount": 0,"currency": "USD"},"total_discount_money": {"amount": 0,"currency": "USD"},"total_money": {"amount": 26500,"currency": "USD"},"variation_total_price_money": {"amount": 0,"currency": "USD"},"item_type": "ITEM","total_service_charge_money": {"amount": 0,"currency": "USD"}}],"fulfillments": [{"uid": "vsj1b8Kz32xMHNkZcPl7gC","type": "SHIPMENT","state": "PROPOSED","line_item_application": "ALL","shipment_details": {"recipient": {"display_name": "Leah Whitley","email_address": "leah.r.whitley@gmail.com","phone_number": "+18324520815","address": {"address_line_1": "4005 Fernwood Drive","locality": "Houston","administrative_district_level_1": "TX","postal_code": "77021","country": "US","first_name": "Leah","last_name": "Whitley"}},"shipping_note": "","shipping_type": "Free shipping","placed_at": "2024-08-05T14:46:32.289Z"}}],"created_at": "2024-08-05T14:46:30.424Z","updated_at": "2024-08-05T14:46:32.000Z","state": "OPEN","version": 5,"reference_id": "11ef5338d493726984043cecef6da95a","total_tax_money": {"amount": 0,"currency": "USD"},"total_discount_money": {"amount": 0,"currency": "USD"},"total_tip_money": {"amount": 0,"currency": "USD"},"total_money": {"amount": 26500,"currency": "USD"},"tenders": [{"id": "jfZXbYqnToj9W0fzoMrvYmrilgXZY","location_id": "2N1MFGS350ETN","transaction_id": "dhrN4uQUF9eo4I1v2nvSPpRdfHXZY","created_at": "2024-08-05T14:46:31Z","amount_money": {"amount": 26500,"currency": "USD"},"type": "CARD","card_details": {"status": "CAPTURED","card": {"card_brand": "VISA","last_4": "2784","exp_month": 7,"exp_year": 2029,"fingerprint": "sq-1-7G5y74cJ1yJICXRgy13saX0AxYYF4j9zsKYI4Ih7Qvdifz4hnCn5W09mPzkrAhWczQ","card_type": "DEBIT","prepaid_type": "NOT_PREPAID","bin": "461046"},"entry_method": "ON_FILE"},"payment_id": "jfZXbYqnToj9W0fzoMrvYmrilgXZY"}],"total_service_charge_money": {"amount": 0,"currency": "USD"},"net_amounts": {"total_money": {"amount": 26500,"currency": "USD"},"tax_money": {"amount": 0,"currency": "USD"},"discount_money": {"amount": 0,"currency": "USD"},"tip_money": {"amount": 0,"currency": "USD"},"service_charge_money": {"amount": 0,"currency": "USD"}},"source": {"name": "Square Online"},"net_amount_due_money": {"amount": 0,"currency": "USD"}}}';
        //$response = '{"order": {"id": "RvlrZtMFazhIjmxDfbltfAhfkOWZY","location_id": "2N1MFGS350ETN","line_items": [{"uid": "2fLIilrcjbf43d8qfptqUD","catalog_object_id": "RRU3LPKMQGAD3KJB3U3IM6P4","catalog_version": 1722654292140,"quantity": "1","name": "Freestyle Sessions","variation_name": "Regular","base_price_money": {"amount": 0,"currency": "USD"},"modifiers": [{"uid": "AewhZaaGhsVagcFUr6PLxB","base_price_money": {"amount": 0,"currency": "USD"},"total_price_money": {"amount": 0,"currency": "USD"},"name": "Enter pin of skater: CITLA","quantity": "1"},{"uid": "BAzFUTOSPFFEkj6Je48G1","base_price_money": {"amount": 40500,"currency": "USD"},"total_price_money": {"amount": 40500,"currency": "USD"},"name": "50 Freestyle Sessions","catalog_object_id": "RMO73JMUMWFN7PPHJ53YUPOR","catalog_version": 1722654292140,"quantity": "1"}],"gross_sales_money": {"amount": 40500,"currency": "USD"},"total_tax_money": {"amount": 0,"currency": "USD"},"total_discount_money": {"amount": 0,"currency": "USD"},"total_money": {"amount": 40500,"currency": "USD"},"variation_total_price_money": {"amount": 0,"currency": "USD"},"item_type": "ITEM","total_service_charge_money": {"amount": 0,"currency": "USD"}},{"uid": "dId9y5FE9GBUVLQ4SSLgEB","catalog_object_id": "RRU3LPKMQGAD3KJB3U3IM6P4","catalog_version": 1722654292140,"quantity": "1","name": "Freestyle Sessions","variation_name": "Regular","base_price_money": {"amount": 0,"currency": "USD"},"modifiers": [{"uid": "JldZ8sSKxHukkSL4pDnmhD","base_price_money": {"amount": 0,"currency": "USD"},"total_price_money": {"amount": 0,"currency": "USD"},"name": "Enter pin of skater: EMMAV","quantity": "1"},{"uid": "Eac7VecRR1hXVxVoFOlkM","base_price_money": {"amount": 40500,"currency": "USD"},"total_price_money": {"amount": 40500,"currency": "USD"},"name": "50 Freestyle Sessions","catalog_object_id": "RMO73JMUMWFN7PPHJ53YUPOR","catalog_version": 1722654292140,"quantity": "1"}],"gross_sales_money": {"amount": 40500,"currency": "USD"},"total_tax_money": {"amount": 0,"currency": "USD"},"total_discount_money": {"amount": 0,"currency": "USD"},"total_money": {"amount": 40500,"currency": "USD"},"variation_total_price_money": {"amount": 0,"currency": "USD"},"item_type": "ITEM","total_service_charge_money": {"amount": 0,"currency": "USD"}}],"fulfillments": [{"uid": "qOiDtybiWqRwpIVQN80YoC","type": "SHIPMENT","state": "OPEN","line_item_application": "ALL","shipment_details": {"recipient": {"display_name": "Jean-Francois Vautrin","email_address": "jfvautrin51@gmail.com","phone_number": "+12485688671","address": {"address_line_1": "14219 Heatherhill Pl","locality": "Houston","administrative_district_level_1": "TX","postal_code": "77077","country": "US","first_name": "Jean-Francois","last_name": "Vautrin","organization": "Ice Skate USA"}},"shipping_type": "Free shipping","placed_at": "2024-08-03T12:53:59.582Z","shipped_at": "2024-08-04T14:09:47.765Z"}}],"created_at": "2024-08-03T12:51:10.417Z","updated_at": "2024-08-04T14:16:56.083Z","state": "OPEN","version": 14,"reference_id": "1704671052","total_tax_money": {"amount": 0,"currency": "USD"},"total_discount_money": {"amount": 0,"currency": "USD"},"total_tip_money": {"amount": 0,"currency": "USD"},"total_money": {"amount": 81000,"currency": "USD"},"closed_at": "2024-08-04T14:16:56.083Z","tenders": [{"id": "XBHIp6wnJfq0mTS0KnNzJfvWD5XZY","location_id": "2N1MFGS350ETN","transaction_id": "RvlrZtMFazhIjmxDfbltfAhfkOWZY","created_at": "2024-08-03T12:53:58Z","amount_money": {"amount": 81000,"currency": "USD"},"type": "CARD","card_details": {"status": "CAPTURED","card": {"card_brand": "VISA","last_4": "6671","exp_month": 1,"exp_year": 2027,"fingerprint": "sq-1-GZ6WImW_2V6kRq8BD0n05hUZLj1RD4CIB7KEJkJJFwnpbaV6C1OJ-7GhniPulHvEsA","card_type": "CREDIT","prepaid_type": "NOT_PREPAID","bin": "414720"},"entry_method": "KEYED"},"payment_id": "XBHIp6wnJfq0mTS0KnNzJfvWD5XZY"}],"service_charges": [{"uid": "CtKnEWJdtULIKz0ggAlE7B","name": "Shipping","amount_money": {"amount": 0,"currency": "USD"},"applied_money": {"amount": 0,"currency": "USD"},"calculation_phase": "SUBTOTAL_PHASE","taxable": true,"total_money": {"amount": 0,"currency": "USD"},"total_tax_money": {"amount": 0,"currency": "USD"},"type": "CUSTOM","treatment_type": "LINE_ITEM_TREATMENT"}],"total_service_charge_money": {"amount": 0,"currency": "USD"},"net_amounts": {"total_money": {"amount": 81000,"currency": "USD"},"tax_money": {"amount": 0,"currency": "USD"},"discount_money": {"amount": 0,"currency": "USD"},"tip_money": {"amount": 0,"currency": "USD"},"service_charge_money": {"amount": 0,"currency": "USD"}},"source": {"name": "Square Online"},"pricing_options": {"auto_apply_discounts": true,"auto_apply_taxes": false},"net_amount_due_money": {"amount": 0,"currency": "USD"}}}';
        $jsonOrder = json_decode($response);
        $email = $jsonOrder->order->fulfillments[0]->shipment_details->recipient->email_address ?? '';
        $displayName = $jsonOrder->order->fulfillments[0]->shipment_details->recipient->display_name ?? '';
        $state = $jsonOrder->order->state ?? 'none';

        $items = $jsonOrder->order->line_items;

        for ($i = 0; $i < count($items); $i++) {
            $quantity = $items[$i]->quantity ?? 0;
            $skater = substr($items[$i]->modifiers[0]->name, 21) ?? '';
            $catalogId = $items[$i]->modifiers[1]->catalog_object_id ?? '';
            $freestylePackage = $products[$catalogId] ?? 0;
            $item = $i + 1;

            if ($state == "COMPLETED") {
                $result = $oSquare->UpdateStatus($orderId, $item,"COMPLETED");
                continue;
            }

        $userId = $oSquare->GetUserIdFromPin($skater);
        if ($userId == false) {
            writeLog("Invalid pin");
            $userId = -1;
        }

        writeLog("merchant id: " . $merchantId . " state: " . $state . " order id: " . $orderId . " email_address: " . $email . " skater: " . $skater . " quantity: " . $quantity . " package: " . $catalogId . "-" . $freestylePackage);
        writeLog(print_r($jsonOrder, true));

        if ($freestylePackage > 0 && $state != "DRAFT") {
            $orderData = [
                'order_id' => $orderId,
                'item' => $item,
                'uid' => $userId,
                'skater_pin' => $skater,
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