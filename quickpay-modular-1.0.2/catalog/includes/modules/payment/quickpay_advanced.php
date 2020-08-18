<?php
/*
  quickpay_advanced.php, v2.0 Oct 2017
    v1.1 - 2011-06-17

   osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2017 osCommerce

  Released under the GNU General Public License
 */

// 2.3.4BS Edge compatibility
if (!defined('DIR_WS_CLASSES')) define('DIR_WS_CLASSES','includes/classes/');
if (!defined('DIR_WS_ICONS')) define('DIR_WS_ICONS','images/icons/');
if (!defined('FILENAME_ACCOUNT_HISTORY_INFO')) define('FILENAME_ACCOUNT_HISTORY_INFO','account_history_info.php');
if (!defined('FILENAME_CHECKOUT_CONFIRMATION')) define('FILENAME_CHECKOUT_CONFIRMATION','checkout_confirmation.php');
if (!defined('FILENAME_CHECKOUT_PAYMENT')) define('FILENAME_CHECKOUT_PAYMENT','checkout_payment.php');
if (!defined('FILENAME_CHECKOUT_PROCESS')) define('FILENAME_CHECKOUT_PROCESS','checkout_process.php');
if (!defined('FILENAME_CHECKOUT_SUCCESS')) define('FILENAME_CHECKOUT_SUCCESS','checkout_success.php');
if (!defined('FILENAME_SHIPPING')) define('FILENAME_SHIPPING','shipping.php');

/** You can extend the following cards-array and upload corresponding titled images to images/icons */
if (!defined('MODULE_AVAILABLE_CREDITCARDS'))
define('MODULE_AVAILABLE_CREDITCARDS',array(
    '3d-dankort',
    '3d-jcb',
    '3d-visa',
    '3d-mastercard',
    'mastercard',
    'mastercard-debet',
    'american-express',
    'dankort',
    'diners',
    'jcb',
    'visa',
    'visa-electron',
    'viabill',
    'fbg1886',
    'paypal',
    'sofort',
    'mobilepay',
    'bitcoin',
    'swish',
    'trustly',
    'klarna',
    'maestro',
    'ideal',
    'paysafecard',
    'resurs',
    'vipps',
));

include(DIR_FS_CATALOG.DIR_WS_CLASSES.'QuickpayApi.php');

class quickpay_advanced {

    var $code, $title, $description, $enabled, $creditcardgroup, $num_groups;

// class constructor
    function __construct() {
        global $order,$cardlock;

        if (isset($_POST['cardlock'])) $cardlock = $_POST['cardlock'];

        $this->code = 'quickpay_advanced';
        $this->title = MODULE_PAYMENT_QUICKPAY_ADVANCED_TEXT_TITLE;
        $this->public_title = MODULE_PAYMENT_QUICKPAY_ADVANCED_TEXT_PUBLIC_TITLE;
        $this->description = MODULE_PAYMENT_QUICKPAY_ADVANCED_TEXT_DESCRIPTION;
        $this->sort_order = defined('MODULE_PAYMENT_QUICKPAY_ADVANCED_SORT_ORDER') ? MODULE_PAYMENT_QUICKPAY_ADVANCED_SORT_ORDER : 0;
        $this->enabled = (defined('MODULE_PAYMENT_QUICKPAY_ADVANCED_STATUS') && MODULE_PAYMENT_QUICKPAY_ADVANCED_STATUS == 'True') ? (true) : (false);
        $this->creditcardgroup = array();
        $this->email_footer = ($cardlock == "viabill" || $cardlock == "viabill" ? DENUNCIATION : '');
        $this->order_status = (defined('MODULE_PAYMENT_QUICKPAY_ADVANCED_PREPARE_ORDER_STATUS_ID') && ((int)MODULE_PAYMENT_QUICKPAY_ADVANCED_PREPARE_ORDER_STATUS_ID > 0)) ? ((int)MODULE_PAYMENT_QUICKPAY_ADVANCED_PREPARE_ORDER_STATUS_ID) : (0);

        // CUSTOMIZE THIS SETTING FOR THE NUMBER OF PAYMENT GROUPS NEEDED
        $this->num_groups = 5;

        if (is_object($order))
            $this->update_status;


        // Store online payment options in local variable
        for ($i = 1; $i <= $this->num_groups; $i++) {
            if (defined('MODULE_PAYMENT_QUICKPAY_ADVANCED_GROUP' . $i) && constant('MODULE_PAYMENT_QUICKPAY_ADVANCED_GROUP' . $i) != '') {
                // if (!isset($this->creditcardgroup[constant('MODULE_PAYMENT_QUICKPAY_ADVANCED_GROUP' . $i . '_FEE')])) {
                    // $this->creditcardgroup[constant('MODULE_PAYMENT_QUICKPAY_ADVANCED_GROUP' . $i . '_FEE')] = array();
                // }
                $payment_options = preg_split('[\,\;]', constant('MODULE_PAYMENT_QUICKPAY_ADVANCED_GROUP' . $i));
                foreach ($payment_options as $option) {
                    $msg .= $option;
                    // $this->creditcardgroup[constant('MODULE_PAYMENT_QUICKPAY_ADVANCED_GROUP' . $i . '_FEE')][] = $option;
                }
            }
        }
//V10
        if($_POST['quickpayIT'] == "go" && !isset($_SESSION['qlink'])) {
            $this->form_action_url = 'https://payment.quickpay.net/';
        }else{
            $this->form_action_url = tep_href_link(FILENAME_CHECKOUT_CONFIRMATION, '', 'SSL');
        }
    }


// class methods
    function update_status() {
        global $order, $quickpay_fee, $HTTP_POST_VARS, $qp_card;

        if (($this->enabled == true) && defined('MODULE_PAYMENT_QUICKPAY_ZONE') && ((int) MODULE_PAYMENT_QUICKPAY_ZONE > 0)) {
            $check_flag = false;
            $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_QUICKPAY_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
            while ($check = tep_db_fetch_array($check_query)) {
                if ($check['zone_id'] < 1) {
                    $check_flag = true;
                    break;
                } elseif ($check['zone_id'] == $order->billing['zone_id']) {
                    $check_flag = true;
                    break;
                }
            }

            if ($check_flag == false) {
                $this->enabled = false;
            }
        }

        if (!tep_session_is_registered('qp_card'))
            tep_session_register('qp_card');
        if (isset($_POST['qp_card']))
            $qp_card = $_POST['qp_card'];

        if (!tep_session_is_registered('cart_QuickPay_ID'))
            tep_session_register('cart_QuickPay_ID');
        if (isset($_GET['cart_QuickPay_ID']))
            $qp_card = $_GET['cart_QuickPay_ID'];


        if (!tep_session_is_registered('quickpay_fee')) {
            tep_session_register('quickpay_fee');
        }
    }


    function javascript_validation() {
        $js = '  if (payment_value == "' . $this->code . '") {' . "\n" .
              '      var qp_card_value = null;' . "\n" .
              '      if (document.checkout_payment.qp_card.length) {' . "\n" .
              '          for (var i=0; i<document.checkout_payment.qp_card.length; i++) {' . "\n" .
              '              if (document.checkout_payment.qp_card[i].checked) {' . "\n" .
              '                  qp_card_value = document.checkout_payment.qp_card[i].value;' . "\n" .
              '              }' . "\n" .
              '          }' . "\n" .
              '      } else if (document.checkout_payment.qp_card.checked) {' . "\n" .
              '          qp_card_value = document.checkout_payment.qp_card.value;' . "\n" .
              '      } else if (document.checkout_payment.qp_card.value) {' . "\n" .
              '          qp_card_value = document.checkout_payment.qp_card.value;' . "\n" .
              '          document.checkout_payment.qp_card.checked=true;' . "\n" .
              '      }' . "\n" .
              '      if (qp_card_value == null) {' . "\n" .
              '          error_message = error_message + "' . MODULE_PAYMENT_QUICKPAY_ADVANCED_TEXT_SELECT_CARD . '";' . "\n" .
              '          error = 1;' . "\n" .
              '      }' . "\n" .
              '      if (document.checkout_payment.cardlock.value == null) {' . "\n" .
              '          error_message = error_message + "' . MODULE_PAYMENT_QUICKPAY_ADVANCED_TEXT_SELECT_CARD . '";' . "\n" .
              '          error = 1;' . "\n" .
              '      }' . "\n" .
              '  }' . "\n";
        return $js;
    }


    function selection() {
        global $order, $currencies, $qp_card, $cardlock;
        $qty_groups = 0;

        /** Count how many MODULE_PAYMENT_QUICKPAY_ADVANCED_GROUP are configured. */
        for ($i = 1; $i <= $this->num_groups; $i++) {
            if (constant('MODULE_PAYMENT_QUICKPAY_ADVANCED_GROUP' . $i) == '') {
                continue;
            }

            $qty_groups++;
        }

        if($qty_groups > 1) {
            $selection = array('id' => $this->code, 'module' => $this->title. tep_draw_hidden_field('cardlock', $cardlock ));
        }

        /** Parse all the configured MODULE_PAYMENT_QUICKPAY_ADVANCED_GROUP */
        $selection['fields'] = array();
        $msg = '<table width="100%"><tr><td>';
        $optscount=0;
        for ($i = 1; $i <= $this->num_groups; $i++) {
            $options_text = '';
            if (defined('MODULE_PAYMENT_QUICKPAY_ADVANCED_GROUP' . $i) && constant('MODULE_PAYMENT_QUICKPAY_ADVANCED_GROUP' . $i) != '') {
                $payment_options = preg_split('[\,\;]', constant('MODULE_PAYMENT_QUICKPAY_ADVANCED_GROUP' . $i));
                foreach ($payment_options as $option) {
                    $cost = (MODULE_PAYMENT_QUICKPAY_ADVANCED_AUTOFEE == "No" || $option == 'viabill' ? "0" : "1");
                    if($option=="creditcard"){
                        $optscount++;
                        /** Read the logos defined on admin panel **/
                        $cards = explode(";",MODULE_PAYMENT_QUICKPAY_CARD_LOGOS);
                        foreach ($cards as $optionc) {
                            $iconc = "";
                            if(file_exists(DIR_WS_ICONS.$optionc.".png")){
                              $iconc = DIR_WS_ICONS.$optionc.".png";
                            }elseif(file_exists(DIR_WS_ICONS.$optionc.".jpg")){
                              $iconc = DIR_WS_ICONS.$optionc.".jpg";
                            }elseif(file_exists(DIR_WS_ICONS.$optionc.".gif")){
                              $iconc = DIR_WS_ICONS.$optionc.".gif";
                            }

                            //define payment icon width
                            $w = 35;
                            $h = 22;
                            $space = 5;

                            $msg .= tep_image($iconc,$optionc,$w,$h,'style="position:relative;border:0px;float:left;margin:'.$space.'px;" ');
                        }

                        $msg .= $this->get_payment_options_name($option).'</td></tr></table>';
              					$options_text=$msg;

                        //$cost = $this->calculate_order_fee($order->info['total'], $fees[$i]);
                        if($qty_groups==1){
                            $selection = array(
                                'id' => $this->code,
                                'module' => '<table width="100%" border="0">
                                                <tr class="moduleRow table-selection" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectQuickPayRowEffect(this, ' . ($optscount-1) . ',\''.$option.'\')">
                                                    <td class="main" style="height:22px;vertical-align:middle;">' .$options_text.($cost !=0 ? '</td><td class="main" style="height:22px;vertical-align:middle;"> (+ '.MODULE_PAYMENT_QUICKPAY_ADVANCED_FEELOCKINFO.')' :'').'
                                                        </td>
                                                </tr>'.'
                                            </table>'.tep_draw_hidden_field('cardlock', $option));


                        }else{
                            $selection['fields'][] = array(
                                'title' => '<table width="100%" border="0">
                                                <tr class="moduleRow table-selection" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectQuickPayRowEffect(this, ' . ($optscount-1) . ',\''.$option.'\')">
                                                    <td class="main" style="height:22px;vertical-align:middle;">' . $options_text.($cost !=0 ? '</td><td style="height:22px;vertical-align:middle;">(+ '.MODULE_PAYMENT_QUICKPAY_ADVANCED_FEELOCKINFO.')' :'').'
                                                        </td>
                                                </tr>'.'
                                            </table>',
                                'field' => tep_draw_radio_field(
                                    'qp_card',
                                    '',
                                    ($option==$cardlock ? true : false),
                                    ' onClick="setQuickPay(); document.checkout_payment.cardlock.value = \''.$option.'\';" '
                                )
                            );
                        }//end qty=1
                    }

                    if($option != "creditcard"){
                        //upload images to images/icons corresponding to your chosen cardlock groups in your payment module settings
                        //OPTIONAL image if different from cardlogo, add _payment to filename
                        $selectedopts = explode(",", $option);
                        $icon = "";
                        foreach($selectedopts as $option){
                            $optscount++;

                            $icon = "";
                            if(file_exists(DIR_WS_ICONS.$option.".png")){
                              $icon = DIR_WS_ICONS.$option.".png";
                            }elseif(file_exists(DIR_WS_ICONS.$option.".jpg")){
                              $icon = DIR_WS_ICONS.$option.".jpg";
                            }elseif(file_exists(DIR_WS_ICONS.$option.".gif")){
                              $icon = DIR_WS_ICONS.$option.".gif";
                            }elseif(file_exists(DIR_WS_ICONS . $option . "_payment.png")){
                              $icon = DIR_WS_ICONS . $option . "_payment.png";
                            }elseif(file_exists(DIR_WS_ICONS . $option . "_payment.jpg")){
                              $icon = DIR_WS_ICONS . $option . "_payment.jpg";
                            }elseif(file_exists(DIR_WS_ICONS . $option . "_payment.gif")){
                              $icon = DIR_WS_ICONS . $option . "_payment.gif";
                            }
                            $space = 5;

                            //define payment icon width
                            if(strstr($icon, "_payment")){
                                $w = 120;
                                $h = 27;
                                if(strstr($icon, "3d")){
                                    $w = 60;
                                }
                            }else{
                                $w = 35;
                                $h = 22;
                            }

                            //$cost = $this->calculate_order_fee($order->info['total'], $fees[$i]);
                            $options_text = '<table width="100%">
                                                <tr>
                                                    <td>'.tep_image($icon,$this->get_payment_options_name($option),$w,$h,' style="position:relative;border:0px;float:left;margin:'.$space.'px;" ').'</td>
                                                    <td style="height: 27px;white-space:nowrap;vertical-align:middle;" >' . $this->get_payment_options_name($option) . '</td>
                                                </tr>
                                            </table>';

                            if($qty_groups==1){
                                $selection = array(
                                    'id' => $this->code,
                                    'module' => '<table width="100%" border="0">
                                                    <tr class="moduleRow table-selection" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectQuickPayRowEffect(this, ' . ($optscount-1) . ',\''.$option.'\')">
                                                        <td class="main" style="height: 27px;white-space:nowrap;vertical-align:middle;">' .$options_text.($cost !=0 ? '</td><td style="height:22px;vertical-align:middle;"> (+ '.MODULE_PAYMENT_QUICKPAY_ADVANCED_FEELOCKINFO.')' :'').'
                                                            </td>
                                                    </tr>'.'
                                                </table>'.tep_draw_hidden_field('cardlock', $option).tep_draw_hidden_field('qp_card', (isset($fees[1])) ? $fees[1] : '0'));
                            }else{
                                $selection['fields'][] = array(
                                    'title' => '<table width="100%" border="0">
                                                    <tr class="moduleRow table-selection" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectQuickPayRowEffect(this, ' . ($optscount-1) . ',\''.$option.'\')">
                                                        <td class="main" style="height: 27px;white-space:nowrap;vertical-align:middle;">' . $options_text.($cost !=0 ? '</td><td style="height:22px;vertical-align:middle;"> (+ '.MODULE_PAYMENT_QUICKPAY_ADVANCED_FEELOCKINFO.')' :'').'
                                                            </td>
                                                    </tr>'.'
                                                </table>',
                                    'field' => tep_draw_radio_field(
                                        'qp_card',
                                        '',
                                        ($option==$cardlock ? true : false),
                                        ' onClick="setQuickPay();document.checkout_payment.cardlock.value = \''.$option.'\';" '
                                    )
                                );
                            }
                        }
                    }
                }
            }
        }

        $js_function = '
            <script language="javascript"><!--

                function setQuickPay() {
                    var radioLength = document.checkout_payment.payment.length;
                    for(var i = 0; i < radioLength; i++) {
                        document.checkout_payment.payment[i].checked = false;
                        if(document.checkout_payment.payment[i].value == "quickpay_advanced") {
                            document.checkout_payment.payment[i].checked = true;
                        }
                    }
                }

                function selectQuickPayRowEffect(object, buttonSelect, option) {
                    if (!selected) {
                        if (document.getElementById) {
                            selected = document.getElementById("defaultSelected");
                        } else {
                            selected = document.all["defaultSelected"];
                        }
                    }

                    if (selected) selected.className = "moduleRow";

                    object.className = "moduleRowSelected";
                    selected = object;
                    document.checkout_payment.cardlock.value = option;
                    document.checkout_payment.qp_card.checked = false;
                    if (document.checkout_payment.qp_card[0]) {
                        document.checkout_payment.qp_card[buttonSelect].checked=true;
                    } else {
                        document.checkout_payment.qp_card.checked=true;
                    }
                    setQuickPay();
                }

                /* For each payment method */
                document.checkout_payment.payment.forEach(function(node) {
                  /* Apply for radio input and for his parent row */
                  [node, node.parentElement.parentElement].forEach(item => {
                    /* When a payment method is selected deselect all subpayment methods */
                    item.addEventListener("click", function() {
                        document.checkout_payment.qp_card.forEach(function(sub_node) {
                            sub_node.checked=false;
                        });
                    });
                  })
                })

            //--></script>';

            $selection['module'] .= $js_function;
        return $selection;
    }


    function pre_confirmation_check() {
        global $cartID, $cart, $cardlock;

        if (!tep_session_is_registered('cardlock')) {
            tep_session_register('cardlock');
        }

        if (empty($cart->cartID)) {
            $cartID = $cart->cartID = $cart->generate_cart_id();
        }

        if (!tep_session_is_registered('cartID')) {
            tep_session_register('cartID');
        }

        $this->get_order_fee();
    }


    function confirmation($addorder=false) {
        global $cartID, $cart_QuickPay_ID, $customer_id, $languages_id, $order, $order_total_modules, $oscTemplate;
        $order_id = substr($cart_QuickPay_ID, strpos($cart_QuickPay_ID, '-') + 1);

        //do not create preparing order id before payment confirmation is chosen by customer
        $mode = false;
        if(MODULE_PAYMENT_QUICKPAY_ADVANCED_MODE == "Before" || $_POST['callquickpay'] == "go"){
            $mode = true;
        }

        if($mode && !$order_id) {
            // write new pro forma order if payment link is not created
            //if(!isset($_SESSION['qlink'])){
            $order_totals = array();
            if (is_array($order_total_modules->modules)) {
                reset($order_total_modules->modules);
                while (list(, $value) = each($order_total_modules->modules)) {
                    $class = substr($value, 0, strrpos($value, '.'));
                    if ($GLOBALS[$class]->enabled) {
                        for ($i = 0, $n = sizeof($GLOBALS[$class]->output); $i < $n; $i++) {
                            if (tep_not_null($GLOBALS[$class]->output[$i]['title']) && tep_not_null($GLOBALS[$class]->output[$i]['text'])) {
                                $order_totals[] = array(
                                    'code' => $GLOBALS[$class]->code,
                                    'title' => $GLOBALS[$class]->output[$i]['title'],
                                    'text' => $GLOBALS[$class]->output[$i]['text'],
                                    'value' => $GLOBALS[$class]->output[$i]['value'],
                                    'sort_order' => $GLOBALS[$class]->sort_order
                                );
                            }
                        }
                    }
                }
            }

            $sql_data_array = array(
                'customers_id' => $customer_id,
                'customers_name' => $order->customer['firstname'] . ' ' . $order->customer['lastname'],
                'customers_company' => $order->customer['company'],
                'customers_street_address' => $order->customer['street_address'],
                'customers_suburb' => $order->customer['suburb'],
                'customers_city' => $order->customer['city'],
                'customers_postcode' => $order->customer['postcode'],
                'customers_state' => $order->customer['state'],
                'customers_country' => $order->customer['country']['title'],
                'customers_telephone' => $order->customer['telephone'],
                'customers_email_address' => $order->customer['email_address'],
                'customers_address_format_id' => $order->customer['format_id'],
                'delivery_name' => $order->delivery['firstname'] . ' ' . $order->delivery['lastname'],
                'delivery_company' => $order->delivery['company'],
                'delivery_street_address' => $order->delivery['street_address'],
                'delivery_suburb' => $order->delivery['suburb'],
                'delivery_city' => $order->delivery['city'],
                'delivery_postcode' => $order->delivery['postcode'],
                'delivery_state' => $order->delivery['state'],
                'delivery_country' => $order->delivery['country']['title'],
                'delivery_address_format_id' => $order->delivery['format_id'],
                'billing_name' => $order->billing['firstname'] . ' ' . $order->billing['lastname'],
                'billing_company' => $order->billing['company'],
                'billing_street_address' => $order->billing['street_address'],
                'billing_suburb' => $order->billing['suburb'],
                'billing_city' => $order->billing['city'],
                'billing_postcode' => $order->billing['postcode'],
                'billing_state' => $order->billing['state'],
                'billing_country' => $order->billing['country']['title'],
                'billing_address_format_id' => $order->billing['format_id'],
                'payment_method' => $order->info['payment_method'],
                'cc_type' => $order->info['cc_type'],
                'cc_owner' => $order->info['cc_owner'],
                'cc_number' => $order->info['cc_number'],
                'cc_expires' => $order->info['cc_expires'],
                'cc_cardhash' => '',
                'date_purchased' => 'now()',
                'orders_status' => $order->info['order_status'],
                'currency' => $order->info['currency'],
                'currency_value' => $order->info['currency_value']
            );

            tep_db_perform(TABLE_ORDERS, $sql_data_array);

            $insert_id = tep_db_insert_id();

            for ($i = 0, $n = sizeof($order_totals); $i < $n; $i++) {
                $sql_data_array = array(
                    'orders_id' => $insert_id,
                    'title' => $order_totals[$i]['title'],
                    'text' => $order_totals[$i]['text'],
                    'value' => $order_totals[$i]['value'],
                    'class' => $order_totals[$i]['code'],
                    'sort_order' => $order_totals[$i]['sort_order']
                );

                tep_db_perform(TABLE_ORDERS_TOTAL, $sql_data_array);


            }

            /**
             * checkout_process section
             * this section is taken from checkout_process.php
             * update of stock data is done in checkout_process
             * adapt if you have done adoptions there
             *
             * the data of the order-obj is depending if it was created from the cart or
             * by a given order-id
             * therefore the product data needs to be written in the cart context
             */

            for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
                /*
                 * do not update stock - until order is confirmed
                 * will be done in checkout_process
                 */
                $sql_data_array = array(
                    'orders_id' => $insert_id,
                    'products_id' => tep_get_prid($order->products[$i]['id']),
                    'products_model' => $order->products[$i]['model'],
                    'products_name' => $order->products[$i]['name'],
                    'products_price' => $order->products[$i]['price'],
                    'final_price' => $order->products[$i]['final_price'],
                    'products_tax' => $order->products[$i]['tax'],
                    'products_quantity' => $order->products[$i]['qty']);
                tep_db_perform(TABLE_ORDERS_PRODUCTS, $sql_data_array);
                $order_products_id = tep_db_insert_id();

                /*------insert customer choosen option to order--------*/
                $attributes_exist = '0';
                $products_ordered_attributes = '';
                if (isset($order->products[$i]['attributes'])) {
                    $attributes_exist = '1';
                    for ($j = 0, $n2 = sizeof($order->products[$i]['attributes']); $j < $n2; $j++) {
                        if (DOWNLOAD_ENABLED == 'true') {
                            $attributes_query = "select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix, pad.products_attributes_maxdays, pad.products_attributes_maxcount , pad.products_attributes_filename from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa left join " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad on pa.products_attributes_id=pad.products_attributes_id where pa.products_id = '" . $order->products[$i]['id'] . "'and pa.options_id = '" . $order->products[$i]['attributes'][$j]['option_id'] . "'and pa.options_id = popt.products_options_id and pa.options_values_id = '" . $order->products[$i]['attributes'][$j]['value_id'] . "'and pa.options_values_id = poval.products_options_values_id and popt.language_id = '" . $languages_id . "'and poval.language_id = '" . $languages_id . "'";
                            $attributes = tep_db_query($attributes_query);
                        } else {
                            $attributes = tep_db_query("select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa where pa.products_id = '" . $order->products[$i]['id'] . "' and pa.options_id = '" . $order->products[$i]['attributes'][$j]['option_id'] . "' and pa.options_id = popt.products_options_id and pa.options_values_id = '" . $order->products[$i]['attributes'][$j]['value_id'] . "' and pa.options_values_id = poval.products_options_values_id and popt.language_id = '" . $languages_id . "' and poval.language_id = '" . $languages_id . "'");
                        }
                        $attributes_values = tep_db_fetch_array($attributes);

                        $sql_data_array = array(
                            'orders_id' => $insert_id,
                            'orders_products_id' => $order_products_id,
                            'products_options' => $attributes_values['products_options_name'],
                            'products_options_values' => $attributes_values['products_options_values_name'],
                            'options_values_price' => $attributes_values['options_values_price'],
                            'price_prefix' => $attributes_values['price_prefix']
                        );
                        tep_db_perform(TABLE_ORDERS_PRODUCTS_ATTRIBUTES, $sql_data_array);

                        if ((DOWNLOAD_ENABLED == 'true') && isset($attributes_values['products_attributes_filename']) && tep_not_null($attributes_values['products_attributes_filename'])) {
                            $sql_data_array = array(
                                'orders_id' => $insert_id,
                                'orders_products_id' => $order_products_id,
                                'orders_products_filename' => $attributes_values['products_attributes_filename'],
                                'download_maxdays' => $attributes_values['products_attributes_maxdays'],
                                'download_count' => $attributes_values['products_attributes_maxcount']
                            );
                            tep_db_perform(TABLE_ORDERS_PRODUCTS_DOWNLOAD, $sql_data_array);
                        }
                        $products_ordered_attributes .= "\n\t" . $attributes_values['products_options_name'] . ' ' . $attributes_values['products_options_values_name'];
                    }
                }
            }
            /// end checkout_process section
            $cart_QuickPay_ID = $cartID . '-' . $insert_id;
            tep_session_register('cart_QuickPay_ID');

            //}
        }

        $error_msg = CONDITION_AGREEMENT_ERROR;
        $onload = <<<EOT
<script type="text/javascript">
    $(document).ready(function() {

        $('#agreed').click(function(){
            if($(this).val() == 'true'){
                $(this).val('false');
            } else {
                $(this).val('true');
            }
        });

        $( "form[name='checkout_confirmation']" ).submit(function( e ) {
            if($('#agreed').val() == 'true'){
                return true;
            } else {
                e.preventDefault();
                alert('$error_msg');
                return false;
            }
        });

        // for BS version - make parent box full width
        var ppDiv = $( "#agree_terms" ).parent().parent();
        if (ppDiv.hasClass('col-sm-6')) {
            ppDiv.removeClass('col-sm-6');
            ppDiv.addClass('col-sm-12');
        }
    });
</script>
EOT;
        $oscTemplate->addBlock($onload, 'footer_scripts');

        $agree_terms = '
        <table id="agree_terms" width="100%" border="0" cellspacing="0" cellpadding="2">
            <tr>
                <td class="main"><h3><b>' . HEADING_RETURN_POLICY . '</b> <a href="' . tep_href_link(FILENAME_SHIPPING, '', 'SSL') . '"><span class="orderEdit">(' . TEXT_VIEW . ')</h3></span></a></td>
            </tr>
            <tr>
                <td>' . tep_draw_separator('pixel_trans.gif', '10', '10') . '</td>
            </tr>
            <tr>
                <td>
                    <table border="0" width="100%" cellspacing="1" cellpadding="2" class="infoBox">
                        <tr class="infoBoxContents">
                            <td>
                                <table border="0" width="100%" cellspacing="0" cellpadding="2">
                                    <tr>
                                        <td class="main">' . TEXT_RETURN_POLICY . '</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td>' . tep_draw_separator('pixel_trans.gif', '10', '10') . '</td>
            </tr>
            <tr>
                <td>
                    <table border="0" width="100%" cellspacing="0" cellpadding="0">
                        <tr>
                            <td>
                                <table border="0" width="100%" cellspacing="1" cellpadding="2"  class="infoBox">
                                    <tr class="infoBoxContents" >
                                        <td align="right"><b>' . ACCEPT_CONDITIONS . '</b>&nbsp;' . tep_draw_checkbox_field('agree','false', false, 'id="agreed"') . '</td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>' . tep_draw_separator('pixel_trans.gif', '10', '10') . '</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>';
        $fee_info = (MODULE_PAYMENT_QUICKPAY_ADVANCED_AUTOFEE =="Yes" && $_POST["cardlock"] !="viabill" ? MODULE_PAYMENT_QUICKPAY_ADVANCED_FEEINFO . '<br />' : '');

        return array('title' => $fee_info . $this->email_footer . $agree_terms);

        // if($this->email_footer !='' && $addorder==false){
            // return array('title' => $fee_info . $this->email_footer . $agree_terms);
        // }else{return false;}
    }


    function process_button() {
        global $_POST, $customer_id, $order, $currencies, $currency, $languages_id, $language, $cart_QuickPay_ID, $order_total_modules, $messageStack;
        /** collect all post fields and attach as hiddenfieds to button */

        if ( !class_exists('quickpay_currencies') ) {
            include(DIR_FS_CATALOG . DIR_WS_CLASSES . 'quickpay_currencies.php');
        }
        if (!($currencies instanceof quickpay_currencies)) {
            $currencies = new quickpay_currencies($currencies);
        }

        $process_button_string = '';
        $process_fields ='';
        $process_parameters = array();

        $qp_merchant_id = MODULE_PAYMENT_QUICKPAY_ADVANCED_MERCHANTID;
        $qp_agreement_id = MODULE_PAYMENT_QUICKPAY_ADVANCED_AGGREEMENTID;


        // TODO: dynamic language switching instead of hardcoded mapping
        $qp_language = "da";
        switch ($language) {
            case "english": $qp_language = "en";
                break;
            case "swedish": $qp_language = "se";
                break;
            case "norwegian": $qp_language = "no";
                break;
            case "german": $qp_language = "de";
                break;
            case "french": $qp_language = "fr";
                break;
        }
        $qp_branding_id = "";

        $qp_subscription = (MODULE_PAYMENT_QUICKPAY_ADVANCED_SUBSCRIPTION == "Normal" ? "" : "1");
        $qp_cardtypelock = $_POST['cardlock'];
        $qp_autofee = (MODULE_PAYMENT_QUICKPAY_ADVANCED_AUTOFEE == "No" || $qp_cardtypelock == 'viabill' ? "0" : "1");
        $qp_description = "Merchant ".$qp_merchant_id." ".(MODULE_PAYMENT_QUICKPAY_ADVANCED_SUBSCRIPTION == "Normal" ? "Authorize" : "Subscription");
        $order_id = substr($cart_QuickPay_ID, strpos($cart_QuickPay_ID, '-') + 1);
        $qp_order_id = MODULE_PAYMENT_QUICKPAY_ADVANCED_ORDERPREFIX.sprintf('%04d', $order_id);
        // Calculate the total order amount for the order (the same way as in checkout_process.php)
        $qp_order_amount = 100 * $currencies->calculate($order->info['total'], true, $order->info['currency'], $order->info['currency_value'], '.', '');
        $qp_currency_code = $order->info['currency'];
        $qp_continueurl = tep_href_link(FILENAME_CHECKOUT_PROCESS, 'cart_QuickPay_ID='.$cart_QuickPay_ID, 'SSL');
        $qp_cancelurl = tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code, 'SSL');
        $qp_callbackurl = tep_href_link('callback10.php','oid='.$order_id,'SSL');
        $qp_autocapture = (MODULE_PAYMENT_QUICKPAY_ADVANCED_AUTOCAPTURE == "No" ? "0" : "1");
        $qp_version ="v10";

        // $qp_apikey = MODULE_PAYMENT_QUICKPAY_ADVANCED_APIKEY;
        // $qp_product_id = "P03";
        // $qp_category = MODULE_PAYMENT_QUICKPAY_ADVANCED_PAII_CAT;
        // $qp_reference_title = $qp_order_id;
        // $qp_vat_amount = ($order->info['tax'] ? $order->info['tax'] : "0.00");

        //custom vars
        $varsvalues = array(
            'variables[customers_id]' => $customer_id,
            'variables[customers_name]' => $order->customer['firstname'] . ' ' . $order->customer['lastname'],
            'variables[customers_company]' => $order->customer['company'],
            'variables[customers_street_address]' => $order->customer['street_address'],
            'variables[customers_suburb]' => $order->customer['suburb'],
            'variables[customers_city]' => $order->customer['city'],
            'variables[customers_postcode]' => $order->customer['postcode'],
            'variables[customers_state]' => $order->customer['state'],
            'variables[customers_country]' => $order->customer['country']['title'],
            'variables[customers_telephone]' => $order->customer['telephone'],
            'variables[customers_email_address]' => $order->customer['email_address'],
            'variables[delivery_name]' => $order->delivery['firstname'] . ' ' . $order->delivery['lastname'],
            'variables[delivery_company]' => $order->delivery['company'],
            'variables[delivery_street_address]' => $order->delivery['street_address'],
            'variables[delivery_suburb]' => $order->delivery['suburb'],
            'variables[delivery_city]' => $order->delivery['city'],
            'variables[delivery_postcode]' => $order->delivery['postcode'],
            'variables[delivery_state]' => $order->delivery['state'],
            'variables[delivery_country]' => $order->delivery['country']['title'],
            'variables[delivery_address_format_id]' => $order->delivery['format_id'],
            'variables[billing_name]' => $order->billing['firstname'] . ' ' . $order->billing['lastname'],
            'variables[billing_company]' => $order->billing['company'],
            'variables[billing_street_address]' => $order->billing['street_address'],
            'variables[billing_suburb]' => $order->billing['suburb'],
            'variables[billing_city]' => $order->billing['city'],
            'variables[billing_postcode]' => $order->billing['postcode'],
            'variables[billing_state]' => $order->billing['state'],
            'variables[billing_country]' => $order->billing['country']['title']
        );

        for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
            $order_products_id = tep_get_prid($order->products[$i]['id']);

            //------insert customer choosen option to order--------
            $attributes_exist = '0';
            $products_ordered_attributes = '';
            if (isset($order->products[$i]['attributes'])) {
                $attributes_exist = '1';
                for ($j = 0, $n2 = sizeof($order->products[$i]['attributes']); $j < $n2; $j++) {
                    if (DOWNLOAD_ENABLED == 'true') {
                        $attributes_query = "select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix, pad.products_attributes_maxdays, pad.products_attributes_maxcount , pad.products_attributes_filename
                        from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                        left join " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                        on pa.products_attributes_id=pad.products_attributes_id
                        where pa.products_id = '" . $order->products[$i]['id'] . "'
                        and pa.options_id = '" . $order->products[$i]['attributes'][$j]['option_id'] . "'
                        and pa.options_id = popt.products_options_id
                        and pa.options_values_id = '" . $order->products[$i]['attributes'][$j]['value_id'] . "'
                        and pa.options_values_id = poval.products_options_values_id
                        and popt.language_id = '" . $languages_id . "'
                        and poval.language_id = '" . $languages_id . "'";
                        $attributes = tep_db_query($attributes_query);
                    } else {
                        $attributes = tep_db_query("select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa where pa.products_id = '" . $order->products[$i]['id'] . "' and pa.options_id = '" . $order->products[$i]['attributes'][$j]['option_id'] . "' and pa.options_id = popt.products_options_id and pa.options_values_id = '" . $order->products[$i]['attributes'][$j]['value_id'] . "' and pa.options_values_id = poval.products_options_values_id and popt.language_id = '" . $languages_id . "' and poval.language_id = '" . $languages_id . "'");
                    }
                    $attributes_values = tep_db_fetch_array($attributes);

                    if ((DOWNLOAD_ENABLED == 'true') && isset($attributes_values['products_attributes_filename']) && tep_not_null($attributes_values['products_attributes_filename'])) {

                    }
                    $products_ordered_attributes .= "(" . $attributes_values['products_options_name'] . ' ' . $attributes_values['products_options_values_name'].") ";
                }
            }

            //------insert customer choosen option eof ----
            $total_weight += ( $order->products[$i]['qty'] * $order->products[$i]['weight']);
            $total_tax += tep_calculate_tax($total_products_price, $products_tax) * $order->products[$i]['qty'];
            $total_cost += $total_products_price;

            $products_ordered[] = $order->products[$i]['qty'] . ' x ' . $order->products[$i]['name'] . ' (' . $order->products[$i]['model'] . ') = ' . $currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']) . $products_ordered_attributes . "-";

        }

        $ps="";
        while (list ($key, $value) = each($products_ordered)) {
            $ps .= $value;
        }

        $varsvalues["variables[products]"] = html_entity_decode($ps);
        $varsvalues["variables[shopsystem]"] = "OsCommerce";
        //end custom vars

        // register fields to hand over
        $process_parameters = array(
            'agreement_id'                 => $qp_agreement_id,
            'amount'                       => $qp_order_amount,
            'autocapture'                  => $qp_autocapture,
            'autofee'                      => $qp_autofee,
            // 'branding_id'                  => $qp_branding_id,
            'callbackurl'                  => $qp_callbackurl,
            'cancelurl'                    => $qp_cancelurl,
            'continueurl'                  => $qp_continueurl,
            'currency'                     => $qp_currency_code,
            'description'                  => $qp_description,
            // 'google_analytics_client_id'   => $qp_google_analytics_client_id,
            // 'google_analytics_tracking_id' => $analytics_tracking_id,
            'language'                     => $qp_language,
            'merchant_id'                  => $qp_merchant_id,
            'order_id'                     => $qp_order_id,
            'payment_methods'              => $qp_cardtypelock,
            // 'product_id'                   => $qp_product_id,
            // 'category'                     => $qp_category,
            // 'reference_title'              => $qp_reference_title,
            // 'vat_amount'                   => $qp_vat_amount,
            'subscription'                 => $qp_subscription,
            'version'                      => 'v10'
        );


        $process_parameters = array_merge($process_parameters,$varsvalues);
        // $dumpvar = "-- get process parameters\n";
        // $dumpvar .= print_r($process_parameters,true)."\n";

        if($_POST['callquickpay'] == "go") {
            $apiorder= new QuickpayApi();
            $apiorder->setOptions(MODULE_PAYMENT_QUICKPAY_ADVANCED_USERAPIKEY);
            //set status request mode
            $mode = (MODULE_PAYMENT_QUICKPAY_ADVANCED_SUBSCRIPTION == "Normal" ? "" : "1");
            //been here before?
            $exists = $this->get_quickpay_order_status($order_id, $mode);

            // $dumpvar .= "-- get order status\n";
            // $dumpvar .= print_r($exists,true)."\n";
            $qid = $exists["qid"];
            //set to create/update mode
            $apiorder->mode = (MODULE_PAYMENT_QUICKPAY_ADVANCED_SUBSCRIPTION == "Normal" ? "payments/" : "subscriptions/");

            if($exists["qid"] == null){
                //create new quickpay order
                $storder = $apiorder->createorder($qp_order_id, $qp_currency_code, $process_parameters);
                $qid = $storder["id"];

            }else{
                $qid = $exists["qid"];
            }

            // $dumpvar .= "-- Create order\n";
            // $dumpvar .= print_r($storder,true)."\n";
            $storder = $apiorder->link($qid, $process_parameters);
            // $dumpvar .= "-- Get link\n";
            // $dumpvar .= print_r($storder,true)."\n";
            // exit("<pre>".$dumpvar."</pre>");

            if (substr($storder['url'],0,5) <> 'https') {
                $messageStack->add_session(MODULE_PAYMENT_QUICKPAY_ADVANCED_ERROR_COMMUNICATION_FAILURE, 'error');
                tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code, 'SSL'));
            }

            $process_button_string .= "
                <script>
                    //alert('qp ".$qp_order_id."-".$order_id."');
                    window.location.replace('".$storder['url']."');
                </script>";
        }
        $process_button_string .=  "<input type='hidden' value='go' name='callquickpay' />". "\n".
                                   "<input type='hidden' value='" . $_POST['cardlock'] . "' name='cardlock' />";

        return $process_button_string;
    }


    function before_process() {
        // called in FILENAME_CHECKOUT_PROCESS
        // check if order is approved by callback
        global $customer_id, $order, $order_id, $order_totals, $order_total_modules, $sendto, $billto, $languages_id, $payment, $currencies, $cart, $cart_QuickPay_ID;

        $order_id = substr($cart_QuickPay_ID, strpos($cart_QuickPay_ID, '-') + 1);

        $order_status_approved_id = (MODULE_PAYMENT_QUICKPAY_ADVANCED_ORDER_STATUS_ID > 0 ? (int) MODULE_PAYMENT_QUICKPAY_ADVANCED_ORDER_STATUS_ID : (int) DEFAULT_ORDERS_STATUS_ID);

        $mode = (MODULE_PAYMENT_QUICKPAY_ADVANCED_SUBSCRIPTION == "Normal" ? "" : "1");
        $checkorderid = $this->get_quickpay_order_status($order_id, $mode);
        if($checkorderid["oid"] != $order_id){
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code, 'SSL'));

        }

        if ( !class_exists('quickpay_order') ) {
            include(DIR_FS_CATALOG . DIR_WS_CLASSES . 'quickpay_order.php');
        }
        if (!($order instanceof quickpay_order)) {
            $order = new quickpay_order($order);
        }

        //for debugging with FireBug / FirePHP
        global $firephp;
        if (isset($firephp)) {
            $firephp->log($order_id, 'order_id');
        }

        tep_db_query("update " . TABLE_ORDERS . " set orders_status = '" . (int)$order_status_approved_id . "', last_modified = now() where orders_id = '" . (int)$order_id . "'");

        $sql_data_array = array(
            'orders_id' => $order_id,
            'orders_status_id' => (int)$order_status_approved_id,
            'date_added' => 'now()',
            'customer_notified' => (SEND_EMAILS == 'true') ? '1' : '0',
            'comments' => $order->info['comments']
        );

        tep_db_perform(TABLE_ORDERS_STATUS_HISTORY, $sql_data_array);

        // initialized for the email confirmation
        $products_ordered = '';

        for ($i=0, $n=sizeof($order->products); $i<$n; $i++) {
            // Stock Update - Joao Correia
            if (STOCK_LIMITED == 'true') {
                if (DOWNLOAD_ENABLED == 'true') {
                    $stock_query_raw = "SELECT products_quantity, pad.products_attributes_filename
                                        FROM " . TABLE_PRODUCTS . " p
                                        LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                        ON p.products_id=pa.products_id
                                        LEFT JOIN " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                                        ON pa.products_attributes_id=pad.products_attributes_id
                                        WHERE p.products_id = '" . tep_get_prid($order->products[$i]['id']) . "'";
                    // Will work with only one option for downloadable products
                    // otherwise, we have to build the query dynamically with a loop
                    $products_attributes = (isset($order->products[$i]['attributes'])) ? $order->products[$i]['attributes'] : '';
                    if (is_array($products_attributes)) {
                        $stock_query_raw .= " AND pa.options_id = '" . (int)$products_attributes[0]['option_id'] . "' AND pa.options_values_id = '" . (int)$products_attributes[0]['value_id'] . "'";
                    }
                    $stock_query = tep_db_query($stock_query_raw);
                } else {
                    $stock_query = tep_db_query("select products_quantity from " . TABLE_PRODUCTS . " where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
                }

                if (tep_db_num_rows($stock_query) > 0) {
                    $stock_values = tep_db_fetch_array($stock_query);
                    // do not decrement quantities if products_attributes_filename exists

                    if ((DOWNLOAD_ENABLED != 'true') || (!$stock_values['products_attributes_filename'])) {
                        $stock_left = $stock_values['products_quantity'] - $order->products[$i]['qty'];
                    } else {
                        $stock_left = $stock_values['products_quantity'];
                    }
                    tep_db_query("update " . TABLE_PRODUCTS . " set products_quantity = '" . (int)$stock_left . "' where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
                    if ( ($stock_left < 1) && (STOCK_ALLOW_CHECKOUT == 'false') ) {
                        tep_db_query("update " . TABLE_PRODUCTS . " set products_status = '0' where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");
                    }
                }
            }

            // Update products_ordered (for bestsellers list)
            tep_db_query("update " . TABLE_PRODUCTS . " set products_ordered = products_ordered + " . sprintf('%d', $order->products[$i]['qty']) . " where products_id = '" . tep_get_prid($order->products[$i]['id']) . "'");

            //------insert customer choosen option to order--------
            $attributes_exist = '0';
            $products_ordered_attributes = '';
            if (isset($order->products[$i]['attributes'])) {
                $attributes_exist = '1';
                for ($j=0, $n2=sizeof($order->products[$i]['attributes']); $j<$n2; $j++) {
                    if (DOWNLOAD_ENABLED == 'true') {
                        $attributes_query = "select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix, pad.products_attributes_maxdays, pad.products_attributes_maxcount , pad.products_attributes_filename
                                             from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa
                                             left join " . TABLE_PRODUCTS_ATTRIBUTES_DOWNLOAD . " pad
                                             on pa.products_attributes_id=pad.products_attributes_id
                                             where pa.products_id = '" . (int)$order->products[$i]['id'] . "'
                                             and pa.options_id = '" . (int)$order->products[$i]['attributes'][$j]['option_id'] . "'
                                             and pa.options_id = popt.products_options_id
                                             and pa.options_values_id = '" . (int)$order->products[$i]['attributes'][$j]['value_id'] . "'
                                             and pa.options_values_id = poval.products_options_values_id
                                             and popt.language_id = '" . (int)$languages_id . "'
                                             and poval.language_id = '" . (int)$languages_id . "'";
                        $attributes = tep_db_query($attributes_query);
                    } else {
                        $attributes = tep_db_query("select popt.products_options_name, poval.products_options_values_name, pa.options_values_price, pa.price_prefix from " . TABLE_PRODUCTS_OPTIONS . " popt, " . TABLE_PRODUCTS_OPTIONS_VALUES . " poval, " . TABLE_PRODUCTS_ATTRIBUTES . " pa where pa.products_id = '" . (int)$order->products[$i]['id'] . "' and pa.options_id = '" . (int)$order->products[$i]['attributes'][$j]['option_id'] . "' and pa.options_id = popt.products_options_id and pa.options_values_id = '" . (int)$order->products[$i]['attributes'][$j]['value_id'] . "' and pa.options_values_id = poval.products_options_values_id and popt.language_id = '" . (int)$languages_id . "' and poval.language_id = '" . (int)$languages_id . "'");
                    }
                    $attributes_values = tep_db_fetch_array($attributes);

                    $products_ordered_attributes .= "\n\t" . $attributes_values['products_options_name'] . ' ' . $attributes_values['products_options_values_name'];
                }
            }
            //------insert customer choosen option eof ----
            $products_ordered .= $order->products[$i]['qty'] . ' x ' . $order->products[$i]['name'] . ' (' . $order->products[$i]['model'] . ') = ' . $currencies->display_price($order->products[$i]['final_price'], $order->products[$i]['tax'], $order->products[$i]['qty']) . $products_ordered_attributes . "\n";
        }

        // lets start with the email confirmation
        $email_order = STORE_NAME . "\n" .
                       EMAIL_SEPARATOR . "\n" .
                       EMAIL_TEXT_ORDER_NUMBER . ' ' . $order_id . "\n" .
                       EMAIL_TEXT_INVOICE_URL . ' ' . tep_href_link(FILENAME_ACCOUNT_HISTORY_INFO, 'order_id=' . $order_id, 'SSL', false) . "\n" .
                       EMAIL_TEXT_DATE_ORDERED . ' ' . strftime(DATE_FORMAT_LONG) . "\n\n";
        if ($order->info['comments']) {
            $email_order .= tep_db_output($order->info['comments']) . "\n\n";
        }
        $email_order .= EMAIL_TEXT_PRODUCTS . "\n" . EMAIL_SEPARATOR . "\n" . $products_ordered . EMAIL_SEPARATOR . "\n";

        for ($i=0, $n=sizeof($order_totals); $i<$n; $i++) {
            $email_order .= strip_tags($order_totals[$i]['title']) . ' ' . strip_tags($order_totals[$i]['text']) . "\n";
        }

        if ($order->content_type != 'virtual') {
            $email_order .= "\n" . EMAIL_TEXT_DELIVERY_ADDRESS . "\n" . EMAIL_SEPARATOR . "\n" . tep_address_label($customer_id, $sendto, 0, '', "\n") . "\n";
        }

        $email_order .= "\n" . EMAIL_TEXT_BILLING_ADDRESS . "\n" . EMAIL_SEPARATOR . "\n" . tep_address_label($customer_id, $billto, 0, '', "\n") . "\n\n";

        if (isset($GLOBALS[$payment]) && is_object($GLOBALS[$payment])) {
            $email_order .= EMAIL_TEXT_PAYMENT_METHOD . "\n" . EMAIL_SEPARATOR . "\n";
            $payment_class = $GLOBALS[$payment];
            $email_order .= $payment_class->title . "\n\n";
            if ($payment_class->email_footer) {
                if ($order->info['cc_transactionid']) {
                    $email_order .= sprintf($payment_class->email_footer, $order->info['cc_transactionid']) . "\n\n";
                } else {
                    $email_order .= $payment_class->email_footer . "\n\n";
                }
            }
        }

        tep_mail($order->customer['firstname'] . ' ' . $order->customer['lastname'], $order->customer['email_address'], EMAIL_TEXT_SUBJECT, $email_order, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);

        // send emails to other people
        if (SEND_EXTRA_ORDER_EMAILS_TO != '') {
            tep_mail('', SEND_EXTRA_ORDER_EMAILS_TO, EMAIL_TEXT_SUBJECT, $email_order, STORE_OWNER, STORE_OWNER_EMAIL_ADDRESS);
        }

        // load the after_process function from the payment modules
        $this->after_process();
    }


    function after_process() {
        global $cart;

        $cart->reset(true);

        tep_session_unregister('cardlock');
        tep_session_unregister('order_id');
        tep_session_unregister('quickpay_fee');
        tep_session_unregister('qp_card');
        tep_session_unregister('cart_QuickPay_ID');
        tep_session_unregister('qlink');

        tep_redirect(tep_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL'));
    }


    function get_error() {
        global $cart_QuickPay_ID, $order, $currencies;
        $order_id = substr($cart_QuickPay_ID, strpos($cart_QuickPay_ID, '-') + 1);;

        if ( !class_exists('quickpay_currencies') ) {
            include(DIR_FS_CATALOG . DIR_WS_CLASSES . 'quickpay_currencies.php');
        }
        if (!($currencies instanceof quickpay_currencies)) {
            $currencies = new quickpay_currencies($currencies);
        }

        $error_desc = MODULE_PAYMENT_QUICKPAY_ADVANCED_ERROR_CANCELLED;
        $error = array('title' => MODULE_PAYMENT_QUICKPAY_ADVANCED_TEXT_ERROR, 'error' => $error_desc);


        //avoid order number already used: create a payment link if payment window was aborted
        //for some reason
        /*if(!$_SESSION["qlink"])    {
        try {

                    $apiorder= new QuickpayApi();
            $apiorder->setOptions(MODULE_PAYMENT_QUICKPAY_ADVANCED_USERAPIKEY);
            //$api->mode = 'payments?currency='.$qp_currency_code.'&order_id='.
        $qp_order_id = MODULE_PAYMENT_QUICKPAY_ADVANCED_AGGREEMENTID."_".sprintf('%04d', $order_id);
        $mode = (MODULE_PAYMENT_QUICKPAY_ADVANCED_SUBSCRIPTION == "Normal" ? "" : "1");
        $exists = $this->get_quickpay_order_status($order_id, $mode);

        if($exists["qid"] == null){

        //create new quickpay order
        $storder = $apiorder->createorder($qp_order_id, $order->info['currency']);
        $qid = $storder["id"];

        }else{
        $qid = $exists["qid"];
        }
        //create or update link
        $process_parameters = array(
        "amount" => 100 * $currencies->calculate($order->info['total'], true, $order->info['currency'], $order->info['currency_value'], '.', ''),
        "currency" => $order->info['currency']
        );

        $storder = $apiorder->link($qid, $process_parameters);
        $_SESSION['qlink'] = $storder['url'];


        } catch (Exception $e) {
        $err .= 'QuickPay Status: ';
                    // An error occured with the status request
            $err .= 'Problem: ' . $this->json_message_front($e->getMessage()) ;
                //  tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code, 'SSL'));
        $error['error'] = $error['error'].' - '.$err;

        }


        }*/
        return $error;
    }


    function output_error() {
        return false;
    }


    function check() {
        if (!isset($this->_check)) {
            $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_QUICKPAY_ADVANCED_STATUS'");
            $this->_check = tep_db_num_rows($check_query);
        }
        return $this->_check;
    }


    function install() {
        // add field to order table if not already there

        $cc_query = tep_db_query("describe " . TABLE_ORDERS . " cc_transactionid");
        if (tep_db_num_rows($cc_query) == 0) {
            tep_db_query("ALTER TABLE " . TABLE_ORDERS . " ADD cc_transactionid VARCHAR( 64 ) NULL default NULL");
        }
        $cc_query = tep_db_query("describe " . TABLE_ORDERS . " cc_cardhash");
        if (tep_db_num_rows($cc_query) == 0) {
            tep_db_query("ALTER TABLE " . TABLE_ORDERS . " ADD cc_cardhash VARCHAR( 64 ) NULL default NULL");
        }
        $cc_query = tep_db_query("describe " . TABLE_ORDERS . " cc_cardtype");
        if (tep_db_num_rows($cc_query) == 0) {
            tep_db_query("ALTER TABLE " . TABLE_ORDERS . " ADD cc_cardtype VARCHAR( 64 ) NULL default NULL");
        }
        tep_db_query("ALTER TABLE  " . TABLE_ORDERS . " CHANGE  cc_expires  cc_expires VARCHAR( 8 )  NULL DEFAULT NULL");

        // new status for quickpay prepare orders
        $check_query = tep_db_query("select orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_name = 'Quickpay [preparing]' limit 1");

        if (tep_db_num_rows($check_query) < 1) {
            $status_query = tep_db_query("select max(orders_status_id) as status_id from " . TABLE_ORDERS_STATUS);
            $status = tep_db_fetch_array($status_query);

            $status_id = $status['status_id'] + 1;

            $languages = tep_get_languages();

            for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                tep_db_query("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values ('" . $status_id . "', '" . $languages[$i]['id'] . "', 'Quickpay [preparing]')");
            }

            // compatibility ms2.2
            $flags_query = tep_db_query("describe " . TABLE_ORDERS_STATUS . " public_flag");
            if (tep_db_num_rows($flags_query) == 1) {
                tep_db_query("update " . TABLE_ORDERS_STATUS . " set public_flag = 1 and downloads_flag = 0 where orders_status_id = '" . $status_id . "'");
            }
        } else {
            $check = tep_db_fetch_array($check_query);

            $status_id = $check['orders_status_id'];

            $flags_query = tep_db_query("describe " . TABLE_ORDERS_STATUS . " public_flag");
            if (tep_db_num_rows($flags_query) == 1) {
                tep_db_query("update " . TABLE_ORDERS_STATUS . " set public_flag = 1 and downloads_flag = 0 where orders_status_id = '" . $status_id . "'");
            }
        }


        // new status for quickpay rejected orders
        $check_query = tep_db_query("select orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_name = 'Quickpay [rejected]' limit 1");

        if (tep_db_num_rows($check_query) < 1) {
            $status_query = tep_db_query("select max(orders_status_id) as status_id from " . TABLE_ORDERS_STATUS);
            $status = tep_db_fetch_array($status_query);

            $status_rejected_id = $status['status_id'] + 1;

            $languages = tep_get_languages();

            for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                tep_db_query("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values ('" . $status_rejected_id . "', '" . $languages[$i]['id'] . "', 'Quickpay [rejected]')");
            }

            // compatibility ms2.2
            $flags_query = tep_db_query("describe " . TABLE_ORDERS_STATUS . " public_flag");
            if (tep_db_num_rows($flags_query) == 1) {
                tep_db_query("update " . TABLE_ORDERS_STATUS . " set public_flag = 1 and downloads_flag = 0 where orders_status_id = '" . $status_rejected_id . "'");
            }
        } else {
            $check = tep_db_fetch_array($check_query);

            $status_rejected_id = $check['orders_status_id'];

            $flags_query = tep_db_query("describe " . TABLE_ORDERS_STATUS . " public_flag");
            if (tep_db_num_rows($flags_query) == 1) {
                tep_db_query("update " . TABLE_ORDERS_STATUS . " set public_flag = 1 and downloads_flag = 0 where orders_status_id = '" . $status_rejected_id . "'");
            }
        }

        // new status for quickpay pending orders
        $check_query = tep_db_query("select orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_name = 'Pending [Quickpay approved]' limit 1");

        if (tep_db_num_rows($check_query) < 1) {
            $status_query = tep_db_query("select max(orders_status_id) as status_id from " . TABLE_ORDERS_STATUS);
            $status = tep_db_fetch_array($status_query);

            $status_pending_id = $status['status_id'] + 1;

            $languages = tep_get_languages();

            for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                tep_db_query("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values ('" . $status_pending_id . "', '" . $languages[$i]['id'] . "', 'Pending [Quickpay approved]')");
            }

            // compatibility ms2.2
            $flags_query = tep_db_query("describe " . TABLE_ORDERS_STATUS . " public_flag");
            if (tep_db_num_rows($flags_query) == 1) {
                tep_db_query("update " . TABLE_ORDERS_STATUS . " set public_flag = 1 and downloads_flag = 0 where orders_status_id = '" . $status_pending_id . "'");
            }
        } else {
            $check = tep_db_fetch_array($check_query);

            $status_pending_id = $check['orders_status_id'];

            $flags_query = tep_db_query("describe " . TABLE_ORDERS_STATUS . " public_flag");
            if (tep_db_num_rows($flags_query) == 1) {
                tep_db_query("update " . TABLE_ORDERS_STATUS . " set public_flag = 1 and downloads_flag = 0 where orders_status_id = '" . $status_pending_id . "'");
            }
        }

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable quickpay_advanced', 'MODULE_PAYMENT_QUICKPAY_ADVANCED_STATUS', 'False', 'Do you want to accept quickpay payments?', '6', '3', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Preparing orders mode', 'MODULE_PAYMENT_QUICKPAY_ADVANCED_MODE', 'Normal', 'Choose  mode:<br><b>Normal:</b> Create when payment window is opened.<br><b>Before:</b> Create when confirmation page is opened', '6', '3', 'tep_cfg_select_option(array(\'Normal\', \'Before\'), ', now())");


        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_QUICKPAY_ADVANCED_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Quickpay Merchant Id', 'MODULE_PAYMENT_QUICKPAY_ADVANCED_MERCHANTID', '', 'Enter Merchant id', '6', '6', now())");

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Quickpay Window user Agreement Id', 'MODULE_PAYMENT_QUICKPAY_ADVANCED_AGGREEMENTID', '', 'Enter Window user Agreement id', '6', '6', now())");

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Order number prefix', 'MODULE_PAYMENT_QUICKPAY_ADVANCED_ORDERPREFIX', '000', 'Enter prefix (Ordernumbers Must contain at least 3 characters)<br>Please Note: if upgrading from previous versions of Quickpay 10, use format \"Window Agreement ID_\" ex. 1234_ if \"old\" orders statuses  are to be displayed in your order admin.<br>', '6', '6', now())");


        // tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Set Private key for your Quickpay Payment Gateway', 'MODULE_PAYMENT_QUICKPAY_ADVANCED_PRIVATEKEY', '', 'Enter your Private key.', '6', '6', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('API USER KEY', 'MODULE_PAYMENT_QUICKPAY_ADVANCED_USERAPIKEY', '', 'Used for payments, and for handling transactions from your backend order page.', '6', '6', now())");

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Subscription payment', 'MODULE_PAYMENT_QUICKPAY_ADVANCED_SUBSCRIPTION', 'Normal', 'Set Subscription payment as default (normal is single payment).', '6', '0', 'tep_cfg_select_option(array(\'Normal\', \'Subscription\'), ',now())");

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Autofee', 'MODULE_PAYMENT_QUICKPAY_ADVANCED_AUTOFEE', 'No', 'Does customer pay the cardfee?<br>Set fees in <a href=\"https://manage.quickpay.net/\" target=\"_blank\"><u>Quickpay manager</u></a>', '6', '0', 'tep_cfg_select_option(array(\'Yes\', \'No\'), ',now())");

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Autocapture', 'MODULE_PAYMENT_QUICKPAY_ADVANCED_AUTOCAPTURE', 'No', 'Use autocapture?', '6', '0', 'tep_cfg_select_option(array(\'Yes\', \'No\'), ',now())");

        for ($i = 1; $i <= $this->num_groups; $i++) {
            if($i==1){
                $defaultlock='viabill';
                // $qp_groupfee = '0:0';
            }else if($i==2){
                $defaultlock='creditcard';
                // $qp_groupfee = '0:0';
            }else{
                $defaultlock='';
                // $qp_groupfee ='0:0';
            }

            $qp_group = (defined('MODULE_PAYMENT_QUICKPAY_GROUP' . $i)) ? constant('MODULE_PAYMENT_QUICKPAY_GROUP' . $i) : $defaultlock;
            // $qp_groupfee = (defined('MODULE_PAYMENT_QUICKPAY_GROUP' . $i . '_FEE')) ? constant('MODULE_PAYMENT_QUICKPAY_GROUP' . $i . '_FEE') : $qp_groupfee;

            tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Group " . $i . " Payment Options ', 'MODULE_PAYMENT_QUICKPAY_ADVANCED_GROUP" . $i . "', '" . $qp_group . "', 'Comma seperated Quickpay payment options that are included in Group " . $i . ", maximum 255 chars (<a href=\'http://tech.quickpay.net/appendixes/payment-methods\' target=\'_blank\'><u>available options</u></a>)<br>Example: creditcard OR viabill OR dankort<br>', '6', '6', now())");
            // tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Group " . $i . " Payments fee', 'MODULE_PAYMENT_QUICKPAY_ADVANCED_GROUP" . $i . "_FEE', '" . $qp_groupfee . "', 'Fee for Group " . $i . " payments (fixed fee:percentage fee)<br>Example: <b>1.45:0.10</b>', '6', '6', now())");
        }

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_QUICKPAY_ADVANCED_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");

        // new settings
        // tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Paii shop category', 'MODULE_PAYMENT_QUICKPAY_ADVANCED_PAII_CAT','', 'Shop category must be set, if using Paii cardlock (paii), ', '6', '0','tep_cfg_pull_down_paii_list(', now())");

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Preparing Order Status', 'MODULE_PAYMENT_QUICKPAY_ADVANCED_PREPARE_ORDER_STATUS_ID', '" . $status_id . "', 'Set the status of prepared orders made with this payment module to this value', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Quickpay Acknowledged Order Status', 'MODULE_PAYMENT_QUICKPAY_ADVANCED_ORDER_STATUS_ID', '" . $status_pending_id . "', 'Set the status of orders made with this payment module to this value', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Quickpay Rejected Order Status', 'MODULE_PAYMENT_QUICKPAY_ADVANCED_REJECTED_ORDER_STATUS_ID', '" . $status_rejected_id . "', 'Set the status of rejected orders made with this payment module to this value', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Credit Card Logos', 'MODULE_PAYMENT_QUICKPAY_CARD_LOGOS', '".implode(";",MODULE_AVAILABLE_CREDITCARDS)."', 'Images related to Credit Card Payment Method. Drag & Drop to change the visibility/order', '4', '0', 'show_logos', 'edit_logos(', now())");

    }


    function remove() {
        tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }


    function keys() {
        $keys = array(
            'MODULE_PAYMENT_QUICKPAY_ADVANCED_STATUS',
            'MODULE_PAYMENT_QUICKPAY_ADVANCED_ZONE',
            'MODULE_PAYMENT_QUICKPAY_ADVANCED_SORT_ORDER',
            'MODULE_PAYMENT_QUICKPAY_ADVANCED_MERCHANTID',
            'MODULE_PAYMENT_QUICKPAY_ADVANCED_AGGREEMENTID',
            'MODULE_PAYMENT_QUICKPAY_ADVANCED_USERAPIKEY',
            'MODULE_PAYMENT_QUICKPAY_ADVANCED_ORDERPREFIX',
            'MODULE_PAYMENT_QUICKPAY_ADVANCED_PREPARE_ORDER_STATUS_ID',
            'MODULE_PAYMENT_QUICKPAY_ADVANCED_ORDER_STATUS_ID',
            'MODULE_PAYMENT_QUICKPAY_ADVANCED_REJECTED_ORDER_STATUS_ID',
            'MODULE_PAYMENT_QUICKPAY_ADVANCED_SUBSCRIPTION',
            'MODULE_PAYMENT_QUICKPAY_ADVANCED_AUTOFEE',
            'MODULE_PAYMENT_QUICKPAY_ADVANCED_AUTOCAPTURE',
            'MODULE_PAYMENT_QUICKPAY_ADVANCED_MODE',
            'MODULE_PAYMENT_QUICKPAY_CARD_LOGOS'
        );

        for ($i = 1; $i <= $this->num_groups; $i++) {
            $keys[] = 'MODULE_PAYMENT_QUICKPAY_ADVANCED_GROUP' . $i;
            // $keys[] = 'MODULE_PAYMENT_QUICKPAY_ADVANCED_GROUP' . $i . '_FEE';
        }

        return $keys;
    }

    //------------- Internal help functions-------------------------
    // $order_total parameter must be total amount for current order including tax
    // format of $fee parameter: "[fixed fee]:[percentage fee]"
    function calculate_order_fee($order_total, $fee) {
        list($fixed_fee, $percent_fee) = explode(':', $fee);

        return ((float) $fixed_fee + (float) $order_total * ($percent_fee / 100));
    }


    function get_order_fee() {
        global $_POST, $order, $currencies, $quickpay_fee;
        $quickpay_fee = 0.0;
        if (isset($_POST['qp_card']) && strpos($_POST['qp_card'], ":")) {
            $quickpay_fee = $this->calculate_order_fee($order->info['total'], $_POST['qp_card']);
        }
    }


    function get_payment_options_name($payment_option) {
        switch ($payment_option) {
            case 'creditcard': return MODULE_PAYMENT_QUICKPAY_ADVANCED_CREDITCARD_TEXT;

            case '3d-dankort': return MODULE_PAYMENT_QUICKPAY_ADVANCED_DANKORT_3D_TEXT;
            case '3d-jcb': return MODULE_PAYMENT_QUICKPAY_ADVANCED_JCB_3D_TEXT;
            case '3d-visa': return MODULE_PAYMENT_QUICKPAY_ADVANCED_VISA_3D_TEXT;
            case '3d-visa-dk': return MODULE_PAYMENT_QUICKPAY_ADVANCED_VISA_DK_3D_TEXT;
            case '3d-visa-electron': return MODULE_PAYMENT_QUICKPAY_ADVANCED_VISA_ELECTRON_3D_TEXT;
            case '3d-visa-electron-dk': return MODULE_PAYMENT_QUICKPAY_ADVANCED_VISA_ELECTRON_DK_3D_TEXT;
            case '3d-visa-debet': return MODULE_PAYMENT_QUICKPAY_ADVANCED_VISA_DEBET_3D_TEXT;
            case '3d-visa-debet-dk': return MODULE_PAYMENT_QUICKPAY_ADVANCED_VISA_DEBET_DK_3D_TEXT;
            case '3d-maestro': return MODULE_PAYMENT_QUICKPAY_ADVANCED_MAESTRO_3D_TEXT;
            case '3d-maestro-dk': return MODULE_PAYMENT_QUICKPAY_ADVANCED_MAESTRO_DK_3D_TEXT;
            case '3d-mastercard': return MODULE_PAYMENT_QUICKPAY_ADVANCED_MASTERCARD_3D_TEXT;
            case '3d-mastercard-dk': return MODULE_PAYMENT_QUICKPAY_ADVANCED_MASTERCARD_DK_3D_TEXT;
            case '3d-mastercard-debet': return MODULE_PAYMENT_QUICKPAY_ADVANCED_MASTERCARD_DEBET_3D_TEXT;
            case '3d-mastercard-debet-dk': return MODULE_PAYMENT_QUICKPAY_ADVANCED_MASTERCARD_DEBET_DK_3D_TEXT;
            case '3d-creditcard': return MODULE_PAYMENT_QUICKPAY_ADVANCED_CREDITCARD_3D_TEXT;
            case 'mastercard': return MODULE_PAYMENT_QUICKPAY_ADVANCED_MASTERCARD_TEXT;
            case 'mastercard-dk': return MODULE_PAYMENT_QUICKPAY_ADVANCED_MASTERCARD_DK_TEXT;
            case 'mastercard-debet': return MODULE_PAYMENT_QUICKPAY_ADVANCED_MASTERCARD_DEBET_TEXT;
            case 'mastercard-debet-dk': return MODULE_PAYMENT_QUICKPAY_ADVANCED_MASTERCARD_DEBET_DK_TEXT;
            case 'american-express': return MODULE_PAYMENT_QUICKPAY_ADVANCED_AMERICAN_EXPRESS_TEXT;
            case 'american-express-dk': return MODULE_PAYMENT_QUICKPAY_ADVANCED_AMERICAN_EXPRESS_DK_TEXT;
            case 'dankort': return MODULE_PAYMENT_QUICKPAY_ADVANCED_DANKORT_TEXT;
            case 'diners': return MODULE_PAYMENT_QUICKPAY_ADVANCED_DINERS_TEXT;
            case 'diners-dk': return MODULE_PAYMENT_QUICKPAY_ADVANCED_DINERS_DK_TEXT;
            case 'jcb': return MODULE_PAYMENT_QUICKPAY_ADVANCED_JCB_TEXT;
            case 'visa': return MODULE_PAYMENT_QUICKPAY_ADVANCED_VISA_TEXT;
            case 'visa-dk': return MODULE_PAYMENT_QUICKPAY_ADVANCED_VISA_DK_TEXT;
            case 'visa-electron': return MODULE_PAYMENT_QUICKPAY_ADVANCED_VISA_ELECTRON_TEXT;
            case 'visa-electron-dk': return MODULE_PAYMENT_QUICKPAY_ADVANCED_VISA_ELECTRON_DK_TEXT;
            case 'viabill': return MODULE_PAYMENT_QUICKPAY_ADVANCED_VIABILL_TEXT;
            case 'fbg1886': return MODULE_PAYMENT_QUICKPAY_ADVANCED_FBG1886_TEXT;
            case 'paypal': return MODULE_PAYMENT_QUICKPAY_ADVANCED_PAYPAL_TEXT;
            case 'sofort': return MODULE_PAYMENT_QUICKPAY_ADVANCED_SOFORT_TEXT;
            case 'mobilepay': return MODULE_PAYMENT_QUICKPAY_ADVANCED_MOBILEPAY_TEXT;
            case 'bitcoin': return MODULE_PAYMENT_QUICKPAY_ADVANCED_BITCOIN_TEXT;
            case 'swish': return MODULE_PAYMENT_QUICKPAY_ADVANCED_SWISH_TEXT;
            case 'trustly': return MODULE_PAYMENT_QUICKPAY_ADVANCED_TRUSTLY_TEXT;
            case 'klarna': return MODULE_PAYMENT_QUICKPAY_ADVANCED_KLARNA_TEXT;

            case 'maestro': return MODULE_PAYMENT_QUICKPAY_ADVANCED_MAESTRO_TEXT;
            case 'ideal': return MODULE_PAYMENT_QUICKPAY_ADVANCED_IDEAL_TEXT;
            case 'paysafecard': return MODULE_PAYMENT_QUICKPAY_ADVANCED_PAYSAFECARD_TEXT;
            case 'resurs': return MODULE_PAYMENT_QUICKPAY_ADVANCED_RESURS_TEXT;
            case 'vipps': return MODULE_PAYMENT_QUICKPAY_ADVANCED_VIPPS_TEXT;

            // case 'danske-dk': return MODULE_PAYMENT_QUICKPAY_ADVANCED_DANSKE_DK_TEXT;
            // case 'edankort': return MODULE_PAYMENT_QUICKPAY_ADVANCED_EDANKORT_TEXT;
            // case 'nordea-dk': return MODULE_PAYMENT_QUICKPAY_ADVANCED_NORDEA_DK_TEXT;
            // case 'viabill':  return MODULE_PAYMENT_QUICKPAY_ADVANCED_viabill_DESCRIPTION;
            // case 'paii': return MODULE_PAYMENT_QUICKPAY_ADVANCED_PAII_TEXT;
        }
        return '';
    }


    public function sign($params, $api_key) {
        ksort($params);
        $base = implode(" ", $params);

        return hash_hmac("sha256", $base, $api_key);
    }


    private function get_quickpay_order_status($order_id,$mode="") {
        $api= new QuickpayApi();

        $api->setOptions(MODULE_PAYMENT_QUICKPAY_ADVANCED_USERAPIKEY);

        try {
            $api->mode = ($mode=="" ? "payments?order_id=" : "subscriptions?order_id=");

            // Commit the status request, checking valid transaction id
            $st = $api->status(MODULE_PAYMENT_QUICKPAY_ADVANCED_ORDERPREFIX.sprintf('%04d', $order_id));
            $eval = array();
            if($st[0]["id"]){
                $eval["oid"] = str_replace(MODULE_PAYMENT_QUICKPAY_ADVANCED_ORDERPREFIX,"", $st[0]["order_id"]);
                $eval["qid"] = $st[0]["id"];
            }else{
                $eval["oid"] = null;
                $eval["qid"] = null;
            }

        } catch (Exception $e) {
            $eval = 'QuickPay Status: ';
            // An error occured with the status request
            $eval .= 'Problem: ' . $this->json_message_front($e->getMessage()) ;
            //  tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code, 'SSL'));
        }

        return $eval;
    }


    private function json_message_front($input){

        $dec = json_decode($input,true);

        $message= $dec["message"];

        return $message;
    }
}

/** Display logos in the admin panel in view state */
function show_logos($text) {
    $w = 55;
    $h = 'auto';
    $output = '';

    if ( !empty($text) ) {
        $output = '<ul style="list-style-type: none; margin: 0; padding: 5px; margin-bottom: 10px;">';

        $options = explode(';', $text);
        foreach ($options as $optionc) {
            $iconc = "";
            if(file_exists(DIR_FS_CATALOG . DIR_WS_IMAGES . 'icons/'.$optionc.".png")){
                $iconc = DIR_WS_CATALOG_IMAGES . 'icons/'.$optionc.".png";
            }elseif(file_exists(DIR_FS_CATALOG . DIR_WS_IMAGES . 'icons/'.$optionc.".jpg")){
                $iconc = DIR_WS_CATALOG_IMAGES . 'icons/'.$optionc.".jpg";
            }elseif(file_exists(DIR_FS_CATALOG . DIR_WS_IMAGES . 'icons/'.$optionc.".gif")){
                $iconc = DIR_WS_CATALOG_IMAGES . 'icons/'.$optionc.".gif";
            }

            if(strlen($iconc))
                $output .= '<li style="padding: 2px;">' . tep_image($iconc, $optionc , $w, $h) . '</li>';
          }
          $output .= '</ul>';
    }
    return $output;
}

/** Display logos in the admin panel in edit state */
function edit_logos($values, $key) {
    $w = 55;
    $h = 'auto';

    /** Scan images directory for logos */
    $files_array = array();
    if ( $dir = @dir(DIR_FS_CATALOG . DIR_WS_IMAGES . 'icons') ) {
        while ( $file = $dir->read() ) {
            /** Check if image is valid */
            if ( !is_dir(DIR_FS_CATALOG . DIR_WS_IMAGES . 'icons/' . $file ) && in_array(explode('.',$file)[0],MODULE_AVAILABLE_CREDITCARDS)) {
                if (in_array(substr($file, strrpos($file, '.')+1), array('gif', 'jpg', 'png')) ) {
                    $files_array[] = $file;
                }
            }
        }
        sort($files_array);
        $dir->close();
    }

    /** Display logos to be shown */
    $values_array = !empty($values) ? explode(';', $values) : array();
    $output = '<h3>' . MODULE_PAYMENT_QUICKPAY_CARD_LOGOS_SHOWN_CARDS . '</h3>' .
              '<ul id="ca_logos" style="list-style-type: none; margin: 0; padding: 5px; margin-bottom: 10px;">';

    foreach ($values_array as $optionc) {
        $iconc = "";
        if(file_exists(DIR_FS_CATALOG . DIR_WS_IMAGES . 'icons/'.$optionc.".png")){
            $iconc = DIR_WS_CATALOG_IMAGES . 'icons/' . $optionc.".png";
        }elseif(file_exists(DIR_FS_CATALOG . DIR_WS_IMAGES . 'icons/'.$optionc.".jpg")){
            $iconc = DIR_WS_CATALOG_IMAGES . 'icons/' . $optionc.".jpg";
        }elseif(file_exists(DIR_FS_CATALOG . DIR_WS_IMAGES . 'icons/'.$optionc.".gif")){
            $iconc = DIR_WS_CATALOG_IMAGES . 'icons/' . $optionc.".gif";
        }

        if(strlen($iconc))
            $output .= '<li style="padding: 2px;">' . tep_image($iconc, $optionc, $w, $h) . tep_draw_hidden_field('bm_card_acceptance_logos[]', $optionc) . '</li>';
    }

    $output .= '</ul>';

    /** Display available logos */
    $output .= '<h3>' . MODULE_PAYMENT_QUICKPAY_CARD_LOGOS_NEW_CARDS . '</h3><ul id="new_ca_logos" style="list-style-type: none; margin: 0; padding: 5px; margin-bottom: 10px;">';
    foreach ($files_array as $file) {
        /** Check if logo is not already displayed in "Available list" */
        if ( !in_array(explode(".",$file)[0], $values_array) ) {
            $output .= '<li style="padding: 2px;">' . tep_image(DIR_WS_CATALOG_IMAGES . 'icons/' . $file, explode(".",$file)[0], $w, $h) . tep_draw_hidden_field('bm_card_acceptance_logos[]', explode(".",$file)[0]) . '</li>';
        }
    }

    $output .= '</ul>';

    $output .= tep_draw_hidden_field('configuration[' . $key . ']', '', 'id="ca_logo_cards"');

    $drag_here_li = '<li id="caLogoEmpty" style="background-color: #fcf8e3; border: 1px #faedd0 solid; color: #a67d57; padding: 5px;">' . addslashes(MODULE_PAYMENT_QUICKPAY_CARD_LOGOS_DRAG_HERE) . '</li>';

    /** Drag and Drop logic */
    $output .= <<<EOD
              <script>
                  $(function() {
                      var drag_here_li = '{$drag_here_li}';
                      if ( $('#ca_logos li').size() < 1 ) {
                          $('#ca_logos').append(drag_here_li);
                      }

                      $('#ca_logos').sortable({
                          connectWith: '#new_ca_logos',
                          items: 'li:not("#caLogoEmpty")',
                          stop: function (event, ui) {
                              if ( $('#ca_logos li').size() < 1 ) {
                                  $('#ca_logos').append(drag_here_li);
                              } else if ( $('#caLogoEmpty').length > 0 ) {
                                  $('#caLogoEmpty').remove();
                              }
                          }
                      });

                      $('#new_ca_logos').sortable({
                          connectWith: '#ca_logos',
                          stop: function (event, ui) {
                              if ( $('#ca_logos li').size() < 1 ) {
                                  $('#ca_logos').append(drag_here_li);
                              } else if ( $('#caLogoEmpty').length > 0 ) {
                                  $('#caLogoEmpty').remove();
                              }
                          }
                      });

                      $('#ca_logos, #new_ca_logos').disableSelection();

                      $('form[name="modules"]').submit(function(event) {
                          var ca_selected_cards = '';

                          if ( $('#ca_logos li').size() > 0 ) {
                              $('#ca_logos li input[name="bm_card_acceptance_logos[]"]').each(function() {
                                  ca_selected_cards += $(this).attr('value') + ';';
                              });
                          }

                        if (ca_selected_cards.length > 0) {
                            ca_selected_cards = ca_selected_cards.substring(0, ca_selected_cards.length - 1);
                        }

                        $('#ca_logo_cards').val(ca_selected_cards);
                      });
                  });
              </script>
EOD;
    return $output;
}

 //redundant, but maybe reuse theese functions in the future?
 /*
$paiioptions = array(
                            ''       => '',
                            'SC00' => 'Ringetoner, baggrundsbilleder m.v.',
                            'SC01' => 'Videoklip og    tv',
                            'SC02' => 'Erotik og voksenindhold',
                            'SC03' => 'Musik, sange og albums',
                            'SC04' => 'Lydb&oslash;ger    og podcasts',
                            'SC05' => 'Mobil spil',
                            'SC06' => 'Chat    og dating',
                            'SC07' => 'Afstemning og konkurrencer',
                            'SC08' => 'Mobil betaling',
                            'SC09' => 'Nyheder og information',
                            'SC10' => 'Donationer',
                            'SC11' => 'Telemetri og service sms',
                            'SC12' => 'Diverse',
                            'SC13' => 'Kiosker & sm&aring; k&oslash;bm&aelig;nd',
                            'SC14' => 'Dagligvare, F&oslash;devarer & non-food',
                            'SC15' => 'Vin & tobak',
                            'SC16' => 'Apoteker    og medikamenter',
                            'SC17' => 'T&oslash;j, sko og accessories',
                            'SC18' => 'Hus, Have, Bolig og indretning',
                            'SC19' => 'B&oslash;ger, papirvare    og kontorartikler',
                            'SC20' => 'Elektronik, Computer & software',
                            'SC21' => '&Oslash;vrige forbrugsgoder',
                            'SC22' => 'Hotel, ophold, restaurant, cafe & v&aelig;rtshuse, Kantiner og catering',
                            'SC24' => 'Kommunikation og konnektivitet, ikke via telefonregning',
                            'SC25' => 'Kollektiv trafik',
                            'SC26' => 'Individuel trafik (Taxik&oslash;rsel)',
                            'SC27' => 'Rejse (lufttrafik, rejser, rejser med ophold)',
                            'SC28' => 'Kommunikation og konnektivitet, via telefonregning',
                            'SC29' => 'Serviceydelser',
                            'SC30' => 'Forlystelser og underholdning, ikke digital',
                            'SC31' => 'Lotteri- og anden spillevirksomhed',
                            'SC32' => 'Interesse- og hobby (Motion, Sport, udendørsaktivitet, foreninger, organisation)',
                            'SC33' => 'Personlig pleje (Fris&oslash;r, sk&oslash;nhed, sol og helse)',
                            'SC34' => 'Erotik og voksenprodukter(fysiske produkter)',
                        );
    $options = '';
    $paiique = tep_db_query("select configuration_value  from ".TABLE_CONFIGURATION. " WHERE configuration_key  =  'MODULE_PAYMENT_QUICKPAY_ADVANCED_PAII_CAT' ");
    $paiicat_values = tep_db_fetch_array($paiique);
    $selectedcat = $paiicat_values['configuration_value'];

    $option_array=array();
foreach($paiioptions as $arrid => $val){
     $option_array[] = array('id' => $arrid,
                              'text' => $val);
     $selected ='';
      if ($selectedcat == $arrid) {
        $selected = ' selected="selected"';
      }
     $options .= '<option value="'.$arrid.'" '.$selected.' >'.$val.'</option>';
}

  function tep_cfg_pull_down_paii_list($option_array) {
     global $options;
    return "<select name='configuration[MODULE_PAYMENT_QUICKPAY_ADVANCED_PAII_CAT]' />
    ".$options."
    </select>";

  }
*/


?>
