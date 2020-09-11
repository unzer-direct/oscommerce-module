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

include(DIR_WS_LANGUAGES . $language . '/modules/payment/quickpay_advanced.php');

require(DIR_FS_CATALOG.DIR_WS_CLASSES.'QuickpayApi.php');

$oid = MODULE_PAYMENT_QUICKPAY_ADVANCED_ORDERPREFIX.sprintf('%04d', $_GET["oid"]);

$qp = new QuickpayApi;

$qp->setOptions( MODULE_PAYMENT_QUICKPAY_ADVANCED_USERAPIKEY);
if(MODULE_PAYMENT_QUICKPAY_ADVANCED_SUBSCRIPTION != "Normal"){
    $qp->mode = 'subscriptions?order_id=';
}else{
    $qp->mode = 'payments?order_id=';
}

// Commit the status request, checking valid transaction id
$str = $qp->status($oid);

$log = "Callback request " . date('d-m-Y H:i:s') . "\n" . print_r($_REQUEST,true) . "\n";
//$log .= "Api status return " . date('d-m-Y H:i:s') . "\n" . print_r($str,true) . "\n";

$str[0]["operations"] = array_reverse($str[0]["operations"]);

$log .= "After reverse " . date('d-m-Y H:i:s') . "\n" . print_r($str,true) . "\n";

$qp_status = $str[0]["operations"][0]["qp_status_code"];
$qp_type = strtolower($str[0]["type"]);
$qp_operations_type = $str[0]["operations"][0]["type"];
$qp_capture = $str[0]["autocapture"];
$qp_vars = $str[0]["variables"];
$qp_id = $str[0]["id"];
$qp_order_id = $_GET["oid"];
$qp_aq_status_code = $str[0]["aq_status_code"];
$qp_aq_status_msg = $str[0]["aq_status_msg"];
$qp_cardtype = $str[0]["metadata"]["brand"];
$qp_cardhash_nr = $str[0]["metadata"]["hash"];
$qp_status_msg = $str[0]["operations"][0]["qp_status_msg"]."\n"."Cardhash: ".$qp_cardhash_nr."\n";
$qp_cardnumber = "xxxx-xxxxxx-".$str[0]["metadata"]["last4"];
$qp_amount = $str[0]["operations"][0]["amount"];
$qp_currency = $str[0]["currency"];
$qp_pending = ($str[0]["pending"] == "true" ? " - pending ": "");
$qp_expire = $str[0]["metadata"]["exp_month"]."-".$str[0]["metadata"]["exp_year"];
$qp_cardhash = $str[0]["operations"][0]["type"].(strstr($str[0]["description"],'Subscription') ? " Subscription" : "");

$log .= "status $qp_status " . "\n";
file_put_contents('qp-api.log', $log, FILE_APPEND);
$log = '';

if (!$qp_status) {
    // if (!$str[0]["id"]) {
    // Request is NOT authenticated or transaction does not exist

    $sql_data_array = array('cc_transactionid' => MODULE_PAYMENT_QUICKPAY_ADVANCED_ERROR_TRANSACTION_DECLINED);
    tep_db_perform(TABLE_ORDERS, $sql_data_array, 'update', "orders_id = '" . $qp_order_id . "'");

    $log .= "no id... " . "\n";
    $log .= "----------------- " . "\n";
    file_put_contents('qp-api.log', $log, FILE_APPEND);

    exit();

}

$qp_approved = false;
/*
20000  Approved
40000  Rejected By Acquirer
40001  Request Data Error
50000  Gateway Error
50300  Communications Error (with Acquirer)
*/

switch ($qp_status) {
    case '20000':
        // approved
        $qp_approved = true;

        break;

    case '40000':
    case '40001':
        // Error in request data.
        // write status message into order to retrieve it as error message on checkout_payment

        $sql_data_array = array(
            'cc_transactionid' => tep_db_input($qp_status_msg),
            'last_modified' => 'now()',
            'orders_status_id' => MODULE_PAYMENT_QUICKPAY_ADVANCED_REJECTED_ORDER_STATUS_ID
        );

        // reject order by updating status
        tep_db_perform(TABLE_ORDERS, $sql_data_array, 'update', "orders_id = '" . $qp_order_id . "'");


      $sql_data_array = array(
            'orders_id' => $qp_order_id,
            'orders_status_id' => MODULE_PAYMENT_QUICKPAY_ADVANCED_REJECTED_ORDER_STATUS_ID,
            'date_added' => 'now()',
            'customer_notified' => '0',
            'comments' => 'Callback: QuickPay Payment rejected [message: '.$qp_operations_type.'-'. $qp_status_msg . ' - '.$qp_aq_status_msg.']'
        );

        tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
        break;

    default:
        $sql_data_array = array('cc_transactionid' => $qp_status, 'last_modified' => 'now()');

        // approve order by updating status
        tep_db_perform(TABLE_ORDERS, $sql_data_array, 'update', "orders_id = '" . $qp_order_id . "'");


        $sql_data_array = array(
            'orders_id' => $qp_order_id,
            'orders_status_id' => MODULE_PAYMENT_QUICKPAY_ADVANCED_ERROR_SYSTEM_FAILURE,
            'date_added' => 'now()',
            'customer_notified' => '0',
            'comments' => 'Callback: QuickPay Payment approved [message: '.$qp_operations_type.'-'. $qp_status_msg . ' - '.$qp_aq_status_msg.']'
        );

        tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

        /*
        $sql_data_array = array(
            'orders_id' => $qp_order_id,
            'orders_status_id' => MODULE_PAYMENT_QUICKPAY_ADVANCED_REJECTED_ORDER_STATUS_ID,
            'date_added' => 'now()',
            'customer_notified' => '0',
            'comments' => 'QuickPay Payment rejected [message: '.$qp_operations_type.'-'. $qp_status_msg . ' - '.$qp_aq_status_msg.']'
        );
        tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
        */
        break;
}

$log .= "----------------- " . "\n";
file_put_contents('qp-api.log', $log, FILE_APPEND);



if ($qp_approved) {
    $sql = "select orders_status, currency, currency_value from " . TABLE_ORDERS . " where orders_id = '" . $qp_order_id . "'";
    $order_query = tep_db_query($sql);

    if (tep_db_num_rows($order_query) > 0) {
        $order = tep_db_fetch_array($order_query);

        // $comment_status = "Transaction: ".$str["id"] . $qp_pending.' (' . $qp_cardtype . ' ' . $currencies->format($qp_amount / 100, false, $qp_currency) . ') '. $qp_status_msg;

        // set order status as configured in the module
        $order_status_id = (MODULE_PAYMENT_QUICKPAY_ADVANCED_ORDER_STATUS_ID > 0 ? (int) MODULE_PAYMENT_QUICKPAY_ADVANCED_ORDER_STATUS_ID : (int) DEFAULT_ORDERS_STATUS_ID);

        $sql_data_array = array(
            'cc_transactionid' => $str[0]["id"],
            'cc_type' => $qp_cardtype,
            'cc_number' => $qp_cardnumber,
            'cc_expires' => ($qp_expire ? $qp_expire : 'N/A'),
            'cc_cardhash' => $qp_cardhash,
            'orders_status' => $order_status_id,
            'last_modified' => 'now()'
        );

        // approve order by updating status
        tep_db_perform(TABLE_ORDERS, $sql_data_array, 'update', "orders_id = '" . $qp_order_id . "'");


        // write/update into order history
        $sql_data_array = array(
            'orders_id' => $qp_order_id,
            'orders_status_id' => $order_status_id,
            'date_added' => 'now()',
            'customer_notified' => '0',
            'comments' => 'Callback: QuickPay Payment approved [message: '.$qp_operations_type.'-'. $qp_status_msg . ' - '.$qp_aq_status_msg.']'
        );

        tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

        /*
        $sql = "select * from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . $qp_order_id . "'";
        $order_query = tep_db_query($sql);
        $sql_data_array = array(
            'orders_id' => $qp_order_id,
            'orders_status_id' => $order_status_id,
            'date_added' => 'now()',
            'customer_notified' => '0',
            'comments' => 'QuickPay Payment '.$qp_operations_type.' successfull [ ' . $comment_status . ']'
        );

        if ($qp_operations_type == "authorize" ) {
            tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);
        }
        */

        //subscription handling
        if($qp_type == "subscription"){

            $apiorder= new QuickpayApi();
            $apiorder->setOptions(MODULE_PAYMENT_QUICKPAY_ADVANCED_USERAPIKEY);
            $apiorder->mode = "subscriptions/";
            $addlink = $qp_id."/recurring/";
            $qp_autocapture = (MODULE_PAYMENT_QUICKPAY_ADVANCED_AUTOCAPTURE == "No" ? FALSE : TRUE);
            //create new quickpay order
            $process_parameters["amount"]= $qp_amount;
            $process_parameters["order_id"]= $qp_order_id."-".$qp_id;
            $process_parameters["auto_capture"]= $qp_autocapture;
            $storder = $apiorder->createorder($qp_order_id, $qp_currency_code, $process_parameters, $addlink);

        }

        // payment link sent by admin approved, but customer is not logged in
        if($qp_approved && $str[0]["link"]["reference_title"] == "admin link" && !tep_session_is_registered('customer_id')){

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
            $sql = "select * from " . TABLE_ORDERS_STATUS_HISTORY . " where orders_id = '" . $qp_order_id . "'";
            $order_query = tep_db_query($sql);
            $sql_data_array = array(
                'orders_id' => $qp_order_id,
                'orders_status_id' => $order_status_id,
                'date_added' => 'now()',
                'customer_notified' => '1'
            );

            tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);


            $email_order = STORE_NAME . "\n" .
                        EMAIL_SEPARATOR . "\n" .
                        EMAIL_TEXT_ORDER_NUMBER . ' ' . $qp_order_id . "\n" .
                        EMAIL_TEXT_INVOICE_URL . ' ' . tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $qp_order_id, 'SSL', false) . "\n" .
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
      "qp_status_code": "20000",
      "qp_status_msg": "Approved",
      "aq_status_code": "000",
      "aq_status_msg": "Approved",
      "data": {},
      "created_at": "2015-03-05T10:06:18+00:00"
    }
  ],
  "metadata": {
    "type": "card",
    "brand": "quickpay-test-card",
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
