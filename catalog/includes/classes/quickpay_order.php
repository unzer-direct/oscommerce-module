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

class quickpay_order extends order {
    public $info, $totals, $products, $customer, $delivery, $content_type;
    public $order_id;

    // CAUTION - unlike parent constructor, this takes a populated order object
    function __construct($order) {
        global $order_id; // order class doesn't hold order id - check it's set in context!

        if (!(int)$order_id > 0)
            return false;

        foreach (get_object_vars($order) as $key => $value) {
            $this->$key = $value;
        }

        $this->id_query($order_id);
    }

    function id_query($order_id) {
        global $languages_id;

        $order_id = tep_db_prepare_input($order_id);

        $order_query = tep_db_query("select cc_transactionid from " . TABLE_ORDERS . " where orders_id = '" . (int)$order_id . "'");
        $order = tep_db_fetch_array($order_query);
        $this->info['cc_transactionid'] = $order['cc_transactionid'];
    }
}
?>
