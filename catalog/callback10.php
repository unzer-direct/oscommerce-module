<?php
/**
 * osCommerce, Open Source E-Commerce Solutions
 * http://www.oscommerce.com
 *
 * Copyright (c) 2020 osCommerce
 *
 * Released under the GNU General Public License
 *
 * author: Genuineq office@genuineq.com
 */

require('includes/application_top.php');

// 2.3.4BS Edge compatibility
if (!defined('DIR_WS_CLASSES')) define('DIR_WS_CLASSES','includes/classes/');
if (!defined('DIR_WS_LANGUAGES')) define('DIR_WS_LANGUAGES','includes/languages/');
if (!defined('FILENAME_CHECKOUT_PROCESS')) define('FILENAME_CHECKOUT_PROCESS','checkout_process.php');
if (!defined('FILENAME_ACCOUNT_HISTORY_INFO')) define('FILENAME_ACCOUNT_HISTORY_INFO','account_history_info.php');

include(DIR_WS_LANGUAGES . $language . '/modules/payment/unzer_advanced.php');

require(DIR_FS_CATALOG.DIR_WS_CLASSES.'UnzerApi.php');

$oid = MODULE_PAYMENT_UNZER_ADVANCED_ORDERPREFIX.sprintf('%04d', $_GET["oid"]);

$unzer = new UnzerApi;

$unzer->setOptions( MODULE_PAYMENT_UNZER_ADVANCED_USERAPIKEY);
if(MODULE_PAYMENT_UNZER_ADVANCED_SUBSCRIPTION != "Normal"){
    $unzer->mode = 'subscriptions?order_id=';
}else{
    $unzer->mode = 'payments?order_id=';
}

// Commit the status request, checking valid transaction id
$str = $unzer->status($oid);

$log = "Callback request " . date('d-m-Y H:i:s') . "\n" . print_r($_REQUEST,true) . "\n";
//$log .= "Api status return " . date('d-m-Y H:i:s') . "\n" . print_r($str,true) . "\n";

$str[0]["operations"] = array_reverse($str[0]["operations"]);

$log .= "After reverse " . date('d-m-Y H:i:s') . "\n" . print_r($str,true) . "\n";

$unzer_status = $str[0]["operations"][0]["unzer_status_code"];
$unzer_type = strtolower($str[0]["type"]);
$unzer_operations_type = $str[0]["operations"][0]["type"];
$unzer_capture = $str[0]["autocapture"];
$unzer_vars = $str[0]["variables"];
$unzer_id = $str[0]["id"];
$unzer_order_id = $_GET["oid"];
$unzer_aq_status_code = $str[0]["aq_status_code"];
$unzer_aq_status_msg = $str[0]["aq_status_msg"];
$unzer_cardtype = $str[0]["metadata"]["brand"];
$unzer_cardhash_nr = $str[0]["metadata"]["hash"];
$unzer_status_msg = $str[0]["operations"][0]["unzer_status_msg"]."\n"."Cardhash: ".$unzer_cardhash_nr."\n";
$unzer_cardnumber = "xxxx-xxxxxx-".$str[0]["metadata"]["last4"];
$unzer_amount = $str[0]["operations"][0]["amount"];
$unzer_currency = $str[0]["currency"];
$unzer_pending = ($str[0]["pending"] == "true" ? " - pending ": "");
$unzer_expire = $str[0]["metadata"]["exp_month"]."-".$str[0]["metadata"]["exp_year"];
$unzer_cardhash = $str[0]["operations"][0]["type"].(strstr($str[0]["description"],'Subscription') ? " Subscription" : "");

$log .= "status $unzer_status " . "\n";
file_put_contents('unzer-api.log', $log, FILE_APPEND);
$log = '';

if (!$unzer_status) {
    // if (!$str[0]["id"]) {
    // Request is NOT authenticated or transaction does not exist

    $sql_data_array = array('cc_transactionid' => MODULE_PAYMENT_UNZER_ADVANCED_ERROR_TRANSACTION_DECLINED);
    tep_db_perform(TABLE_ORDERS, $sql_data_array, 'update', "orders_id = '" . $unzer_order_id . "'");

    $log .= "no id... " . "\n";
    $log .= "----------------- " . "\n";
    file_put_contents('unzer-api.log', $log, FILE_APPEND);

    exit();

}

$unzer_approved = false;
/*
20000  Approved
40000  Rejected By Acquirer
40001  Request Data Error
50000  Gateway Error
50300  Communications Error (with Acquirer)
*/

switch ($unzer_status) {
    case '20000':
        // approved
        $unzer_approved = true;

        break;

    case '40000':
    case '40001':
        // Error in request data.
        // write status message into order to retrieve it as error message on checkout_payment

        $sql_data_array = array(
            'cc_transactionid' => tep_db_input($unzer_status_msg),
            'last_modified' => 'now()',
            'orders_status_id' => MODULE_PAYMENT_UNZER_ADVANCED_REJECTED_ORDER_STATUS_ID
        );

        // reject order by updating status
        tep_db_perform(TABLE_ORDERS, $sql_data_array, 'update', "orders_id = '" . $unzer_order_id . "'");


      $sql_data_array = array(
            'orders_id' => $unzer_order_id,
            'orders_status_id' => MODULE_PAYMENT_UNZER_ADVANCED_REJECTED_ORDER_STATUS_ID,
            'date_added' => 'now()',
            'customer_notified' => '0',
            'comments' => 'Callback: Unzer Payment rejected [message: '.$unzer_operations_type.'-'. $unzer_status_msg . ' - '.$unzer_aq_status_msg.']'
        );

        tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
        break;

    default:
        $sql_data_array = array('cc_transactionid' => $unzer_status, 'last_modified' => 'now()');

        // approve order by updating status
        tep_db_perform(TABLE_ORDERS, $sql_data_array, 'update', "orders_id = '" . $unzer_order_id . "'");


        $sql_data_array = array(
            'orders_id' => $unzer_order_id,
            'orders_status_id' => MODULE_PAYMENT_UNZER_ADVANCED_ERROR_SYSTEM_FAILURE,
            'date_added' => 'now()',
            'customer_notified' => '0',
            'comments' => 'Callback: Unzer Payment approved [message: '.$unzer_operations_type.'-'. $unzer_status_msg . ' - '.$unzer_aq_status_msg.']'
        );

        tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

        /*
        $sql_data_array = array(
            'orders_id' => $unzer_order_id,
            'orders_status_id' => MODULE_PAYMENT_UNZER_ADVANCED_REJECTED_ORDER_STATUS_ID,
            'date_added' => 'now()',
            'customer_notified' => '0',
            'comments' => 'Unzer Payment rejected [message: '.$unzer_operations_type.'-'. $unzer_status_msg . ' - '.$unzer_aq_status_msg.']'
        );
        tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
        */
        break;
}

$log .= "----------------- " . "\n";
file_put_contents('unzer-api.log', $log, FILE_APPEND);



if ($unzer_approved) {
    $sql = "select orders_status, currency, currency_value from " . TABLE_ORDERS . " where orders_id = '" . $unzer_order_id . "'";
    $order_query = tep_db_query($sql);

    if (tep_db_num_rows($order_query) > 0) {
        $order = tep_db_fetch_array($order_query);

        // $comment_status = "Transaction: ".$str["id"] . $unzer_pending.' (' . $unzer_cardtype . ' ' . $currencies->format($unzer_amount / 100, false, $unzer_currency) . ') '. $unzer_status_msg;

        // set order status as configured in the module
        $order_status_id = (MODULE_PAYMENT_UNZER_ADVANCED_ORDER_STATUS_ID > 0 ? (int) MODULE_PAYMENT_UNZER_ADVANCED_ORDER_STATUS_ID : (int) DEFAULT_ORDERS_STATUS_ID);

        $sql_data_array = array(
            'cc_transactionid' => $str[0]["id"],
            'cc_type' => $unzer_cardtype,
            'cc_number' => $unzer_cardnumber,
            'cc_expires' => ($unzer_expire ? $unzer_expire : 'N/A'),
            'cc_cardhash' => $unzer_cardhash,
            'orders_status' => $order_status_id,
            'last_modified' => 'now()'
        );

        // approve order by updating status
        tep_db_perform(TABLE_ORDERS, $sql_data_array, 'update', "orders_id = '" . $unzer_order_id . "'");


        // write/update into order history
        $sql_data_array = array(
            'orders_id' => $unzer_order_id,
            'orders_status_id' => $order_status_id,
            'date_added' => 'now()',
            'customer_notified' => '0',
            'comments' => 'Callback: Unzer Payment approved [message: '.$unzer_operations_type.'-'. $unzer_status_msg . ' - '.$unzer_aq_status_msg.']'
        );

        tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

        /*
        $sql = "select * from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . $unzer_order_id . "'";
        $order_query = tep_db_query($sql);
        $sql_data_array = array(
            'orders_id' => $unzer_order_id,
            'orders_status_id' => $order_status_id,
            'date_added' => 'now()',
            'customer_notified' => '0',
            'comments' => 'Unzer Payment '.$unzer_operations_type.' successfull [ ' . $comment_status . ']'
        );

        if ($unzer_operations_type == "authorize" ) {
            tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
        }
        */

        //subscription handling
        if($unzer_type == "subscription"){

            $apiorder= new UnzerApi();
            $apiorder->setOptions(MODULE_PAYMENT_UNZER_ADVANCED_USERAPIKEY);
            $apiorder->mode = "subscriptions/";
            $addlink = $unzer_id."/recurring/";
            $unzer_autocapture = (MODULE_PAYMENT_UNZER_ADVANCED_AUTOCAPTURE == "No" ? FALSE : TRUE);
            //create new unzer order
            $process_parameters["amount"]= $unzer_amount;
            $process_parameters["order_id"]= $unzer_order_id."-".$unzer_id;
            $process_parameters["auto_capture"]= $unzer_autocapture;
            $storder = $apiorder->createorder($unzer_order_id, $unzer_currency_code, $process_parameters, $addlink);

        }

        // payment link sent by admin approved, but customer is not logged in
        if($unzer_approved && $str[0]["link"]["reference_title"] == "admin link" && !tep_session_is_registered('customer_id')){

            include(DIR_WS_LANGUAGES . $language . '/' . FILENAME_CHECKOUT_PROCESS);

            //reset cart
            $csql = "select customers_id, customers_firstname, customers_lastname  from " . TABLE_CUSTOMERS . " where customers_email_address = '" . $str[0]["link"]["customer_email"] . "' ";
            $c_query = tep_db_query($csql);
            if (tep_db_num_rows($c_query) > 0) {
                $cb = tep_db_fetch_array($c_query);

                tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET . " where customers_id = '" . (int) $cb["customers_id"]  . "'");
                tep_db_query("delete from " . TABLE_CUSTOMERS_BASKET_ATTRIBUTES . " where customers_id = '" . (int) $cb["customers_id"]  . "'");
            }

            // write/update into order history
            $sql = "select * from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . $unzer_order_id . "'";
            $order_query = tep_db_query($sql);
            $sql_data_array = array(
                'orders_id' => $unzer_order_id,
                'orders_status_id' => $order_status_id,
                'date_added' => 'now()',
                'customer_notified' => '1'
            );

            tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);


            $email_order = STORE_NAME . "\n" .
                        EMAIL_SEPARATOR . "\n" .
                        EMAIL_TEXT_ORDER_NUMBER . ' ' . $unzer_order_id . "\n" .
                        EMAIL_TEXT_INVOICE_URL . ' ' . tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $unzer_order_id, 'SSL', false) . "\n" .
                        EMAIL_TEXT_DATE_ORDERED . ' ' . strftime(DATE_FORMAT_LONG) . "\n\n";

            tep_mail($cb["customers_firstname"] .' '.$cb["customers_lastname"], $str[0]["link"]["customer_email"], EMAIL_TEXT_SUBJECT, $email_order, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);

            // send emails to other people
            if (SEND_EXTRA_ORDER_EMAILS_TO != '') {
                tep_mail('', SEND_EXTRA_ORDER_EMAILS_TO, EMAIL_TEXT_SUBJECT, $email_order, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
            }
        }
    }
}


require('includes/application_bottom.php');

?>

<!--{
  "id": 7,
  "order_id": "Order7",
  "accepted": true,
  "test_mode": true,
  "branding_id": null,
  "variables": {},
  "acquirer": "nets",
  "operations": [
    {
      "id": 1,
      "type": "authorize",
      "amount": 123,
      "pending": false,
      "unzer_status_code": "20000",
      "unzer_status_msg": "Approved",
      "aq_status_code": "000",
      "aq_status_msg": "Approved",
      "data": {},
      "created_at": "2015-03-05T10:06:18+00:00"
    }
  ],
  "metadata": {
    "type": "card",
    "brand": "unzer-test-card",
    "last4": "0008",
    "exp_month": 8,
    "exp_year": 2019,
    "country": "DK",
    "is_3d_secure": false,
    "customer_ip": "195.41.47.54",
    "customer_country": "DK"
  },
  "created_at": "2015-03-05T10:06:18Z",
  "balance": 0,
  "currency": "DKK"
}-->
