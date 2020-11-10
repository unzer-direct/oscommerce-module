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

// Add some things here to avoid editing application top
include('includes/quickpay10.php');

// 2.3.4BS Edge compatibility
if (!defined('DIR_WS_CLASSES')) define('DIR_WS_CLASSES','includes/classes/');
if (!defined('DIR_WS_IMAGES')) define('DIR_WS_IMAGES','images/');

include(DIR_FS_CATALOG.DIR_WS_CLASSES.'QuickpayApi.php');
global $api;
$api = new QuickpayApi();
$api->setOptions(MODULE_PAYMENT_QUICKPAY_ADVANCED_USERAPIKEY);

class hook_admin_orders_quickpay_payment {

    function listen_orderAction() {
        if ( !class_exists('hook_admin_orders_quickpay_payment_action') ) {
            include(DIR_FS_CATALOG . 'includes/modules/hooks/admin/orders_quickpay_payment_action.php');
        }

        $hook = new hook_admin_orders_quickpay_payment_action();

        return $hook->execute();
    }

    function listen_orderTab() {
        if ( !class_exists('hook_admin_orders_quickpay_payment_tab') ) {
            include(DIR_FS_CATALOG . 'includes/modules/hooks/admin/orders_quickpay_payment_tab.php');
        }

        $hook = new hook_admin_orders_quickpay_payment_tab();

        return $hook->execute();
    }
}
