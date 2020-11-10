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

// 2.3.4BS Edge compatibility
if (!defined('FILENAME_ORDERS')) define('FILENAME_ORDERS','orders.php');

class hook_admin_orders_quickpay_payment_action {

    function load_language() {
        global $language;
        include_once(DIR_FS_CATALOG_LANGUAGES . $language . '/modules/hooks/admin/' . basename(__FILE__));
    }

    function execute() {
          global $languages_id;

          if ( isset($_GET['tabaction']) ) {
            $this->load_language();

            $oID = $_GET['oID'];
            $amount = $_POST['amount_big'] . $_POST['amount_small'];

            switch ($_GET['tabaction']) {
                case 'quickpay_reverse':
                    $result = get_quickpay_reverse($oID);
                    tep_redirect(tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('tabaction')) . 'action=edit'));
                    break;

                case 'quickpay_capture':
                    if (!isset($_POST['amount_big']) || $_POST['amount_big'] == '' || $_POST['amount_big'] == 0) {
                        tep_redirect(tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('tabaction')) . 'action=edit'));
                    }
                    $result = get_quickpay_capture($oID, $amount);
                    tep_redirect(tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('tabaction')) . 'action=edit'));
                    break;

                case 'quickpay_credit':
                    $result = get_quickpay_credit($oID, $amount);
                    tep_redirect(tep_href_link(FILENAME_ORDERS, tep_get_all_get_params(array('tabaction')) . 'action=edit'));
                    break;

            }
        }
    }
}
