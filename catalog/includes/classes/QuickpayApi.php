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

include('QPConnectorInterface.php');
include('QPConnectorCurl.php');
include('QPConnectorFactory.php');

class QuickpayApi {

    public $mode = "payments/";

    /**
     * Set the options for this object
     * apikey is found in https://manage.quickpay.net
     */
    function setOptions($apiKey, $connTimeout=10, $apiVersion="v10") {
        QPConnectorFactory::getConnector()->setOptions($apiKey, $connTimeout, $apiVersion);
    }

    /**
     * Get a list of payments.
     */
    function getPayments() {
        $result = QPConnectorFactory::getConnector()->request($this->mode);
        return json_decode($result, true);
    }

    /**
     * Get a specific payment.
     * The errorcode 404 is set in the thrown exception if the order is not found
     */
    function status($id) {
        $result = QPConnectorFactory::getConnector()->request($this->mode . $id);

        return json_decode($result, true);
    }

    function link($id, $postArray) {
        $result = QPConnectorFactory::getConnector()->request($this->mode . $id . "/link?currency=" . $postArray["currency"] . "&amount=" . $postArray["amount"], $postArray, 'PUT');

        return json_decode($result, true);
    }


    /**
     * Renew a payment
     */
    function renew($id) {
        $postArray = array();
        $postArray['id'] = $id;
        $result = QPConnectorFactory::getConnector()->request($this->mode . $id . '/renew', $postArray);
        return json_decode($result, true);
    }


    /**
    * Capture a payment
    */
    function capture($id, $amount, $extras=null) {
        $postArray = array();
        $postArray['id'] = $id;
        $postArray['amount'] = $amount;
        if (!is_null($extras)) {
            $postArray['extras'] = $extras;
        }
        $result = QPConnectorFactory::getConnector()->request($this->mode . $id . '/capture', $postArray);

        return json_decode($result, true);
    }

    /**
     * Refund a payment
     */
    function refund($id, $amount, $extras=null) {
        $postArray = array();
        $postArray['id'] = $id;
        $postArray['amount'] = $amount;
        if (!is_null($extras)) {
            $postArray['extras'] = $extras;
        }
        $result = QPConnectorFactory::getConnector()->request($this->mode . $id . '/refund', $postArray);

        return json_decode($result, true);
    }


    /**
     * Cancel a payment
     */
    function cancel($id) {
        $postArray = array();
        $postArray['id'] = $id;
        $result = QPConnectorFactory::getConnector()->request($this->mode . $id . '/cancel', $postArray);

        return json_decode($result, true);
    }

    function createorder($order_id, $currency, $postArray, $addlink='') {
        $result = QPConnectorFactory::getConnector()->request($this->mode . $addlink . '?order_id=' . $order_id . '&currency=' . $currency, $postArray);

        return json_decode($result, true);
    }

    function log_operations($operations, $currency = ""){
        $str="<ul>";
        foreach($operations as $op){
            $str .= "<li><b>" . $op["type"] . "</b> - " . number_format($op["amount"]/100, 2, ',', '') . " " . $currency . ", <b>Quickpay info</b>: " . $op["qp_status_msg"] . ", <b>Aquirer info</b>: " . $op["aq_status_msg"] . ", <b>Log</b>: " . $op["created_at"] . (($op["fraud"]) ? (", <b>Fraud</b>: " . json_encode($op["fraud_remarks"])) : ("")) . "</li>";
        }
        $str .= "<ul>";

        return $str;
    }

    public function init() {
        //check for curl
        if(!extension_loaded('curl')) {
            return false;
        }

        return true;
    }
}

?>
