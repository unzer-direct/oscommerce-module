<?php
/*
  quickpay10.php

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

Protocol 10 version Copyright (c) 2012 Kim L�vendahl
  Released under the GNU General Public License

*/
function json_message($input){
    $dec = json_decode($input,true);
    $message= $dec["message"];

    //get last error
    $text = $dec["errors"]["amount"][0];

    return $message. " amount ".$text;
}

function get_quickpay_status($order_id) {
    try {
        // Commit the status request
        $eval = $qp->status(get_transactionid($order_id));

    } catch (Exception $e) {
        $eval = 'QuickPay Status: ';
        // An error occured with the status request
        $eval .= 'Failure: ' . json_message($e->getMessage()) ;
        $messageStack->add_session($eval, 'warning');
    }

    return $eval;
}


function get_quickpay_reverse($order_id) {
    global $messageStack;

    try {
        $qp = new QuickpayApi;
        // Commit the reversal
        $eval = $qp->cancel(get_transactionid($order_id));
        $result = 'QuickPay Reverse: ';
        if ($eval) {
            $operations = array_reverse($eval["operations"]);
            if ($operations[0]["qp_status_code"] === '20000') {
                // The reversal was completed
                $result .= 'Success: ' . $operations[0]["qp_status_msg"];
                $messageStack->add_session($result, 'success');
            }
        }
    } catch (Exception $e) {
        // An error occured with the reversal
        $result .= 'Failure: ' . json_message($e->getMessage()) ;
        $messageStack->add_session($result . ' : ' . number_format($amount/100,2,',','.')." ".$eval["currency"], 'warning');
    }
}


function get_quickpay_capture($order_id, $amount) {
    global $messageStack;

    try {
        $qp = new QuickpayApi;
        // Set values
        $id = get_transactionid($order_id);
        // Commit the capture
        $eval = $qp->capture($id,$amount);
        $result = 'QuickPay Capture ';
        // exit("<pre>".print_r($eval,true)."</pre>");

        if ($eval) {
            $operations= array_reverse($eval["operations"]);
            if ($operations[0]["qp_status_code"] == '20000') {
                // The reversal was completed
                $result .= 'Succes: ' . $operations[0]["qp_status_msg"];
                $messageStack->add_session($result . ' : ' . number_format($amount/100,2,',','.')." ".$eval["currency"], 'success');
            }
        }
    } catch (Exception $e) {
        // Print error message
        // An error occured with the capture
        $result .= 'Failure: ' . json_message($e->getMessage()) ;
        $messageStack->add_session($result . ' : ' . number_format($amount/100,2,',','.')." ".$eval["currency"], 'warning');
    }
}


function get_quickpay_credit($order_id, $amount) {
    global $messageStack;

    try {
        $qp = new QuickpayApi;
        // Set values
        $id = get_transactionid($order_id);
        // Commit the capture
        $eval = $qp->refund($id, $amount);
        $result = 'QuickPay Credit ';
        if ($eval) {
            $operations= array_reverse($eval["operations"]);
            if ($operations[0]["qp_status_code"] == '20000') {
                // The credit was completed
                $result .= 'Succes: ' . $operations[0]["qp_status_msg"];
                $messageStack->add_session($result . ' : ' . number_format($amount/100,2,',','.')." ".$eval["currency"], 'success');
            }
        }
    } catch (Exception $e) {
        // Print error message
        // An error occured with the credit
        $result .= 'Failure: ' . json_message($e->getMessage());
        $messageStack->add_session($result, 'warning');
    }
}


function get_transactionid($order_id) {
    global $order;

    $transaction_query = tep_db_query("select cc_transactionid from " . TABLE_ORDERS . " where orders_id = '" . (int)$order_id . "'");
    $transaction = tep_db_fetch_array($transaction_query);
    if($transaction['cc_transactionid'] > 0){
        return $transaction['cc_transactionid'];
    }else{
        $apistatus= new QuickpayApi();
        $apistatus->setOptions(MODULE_PAYMENT_QUICKPAY_ADVANCED_USERAPIKEY);
        $apistatus->mode = 'payments?order_id=';

        // Commit the status request, checking valid transaction id
        $qporder_id = MODULE_PAYMENT_QUICKPAY_ADVANCED_ORDERPREFIX.sprintf('%04d', $order_id);
        $st = $apistatus->status($qporder_id);
        if(!$st[0]['id']){
            $st = $apistatus->createorder($qporder_id, $order->info['currency']);
        }
        $order->info['cc_transactionid'] = $st[0]['id'];

        return $st[0]['id'];
    }
}
?>
