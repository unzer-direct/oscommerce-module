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

 /** Module version. */
define('UNZER_MODULE_VERSION', '1.0.12');

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
    'mastercard',
    'visa',
    'maestro',
));

include(DIR_FS_CATALOG.DIR_WS_CLASSES.'UnzerApi.php');
include(DIR_FS_CATALOG.DIR_WS_CLASSES.'UnzerISO3166.php');

class unzer_advanced {

    var $code, $title, $description, $enabled, $creditcardgroup, $num_groups;

    function __construct() {
        global $order,$cardlock;

        if (isset($_POST['cardlock'])) $cardlock = $_POST['cardlock'];

        $this->code = 'unzer_advanced';
        $this->title = MODULE_PAYMENT_UNZER_ADVANCED_TEXT_TITLE;
        $this->public_title = MODULE_PAYMENT_UNZER_ADVANCED_TEXT_PUBLIC_TITLE;
        $this->description = MODULE_PAYMENT_UNZER_ADVANCED_TEXT_DESCRIPTION;
        $this->sort_order = defined('MODULE_PAYMENT_UNZER_ADVANCED_SORT_ORDER') ? MODULE_PAYMENT_UNZER_ADVANCED_SORT_ORDER : 0;
        $this->enabled = (defined('MODULE_PAYMENT_UNZER_ADVANCED_STATUS') && MODULE_PAYMENT_UNZER_ADVANCED_STATUS == 'True') ? (true) : (false);
        $this->creditcardgroup = array();
        $this->email_footer = ($cardlock == "viabill" || $cardlock == "viabill" ? DENUNCIATION : '');
        $this->order_status = (defined('MODULE_PAYMENT_UNZER_ADVANCED_PREPARE_ORDER_STATUS_ID') && ((int)MODULE_PAYMENT_UNZER_ADVANCED_PREPARE_ORDER_STATUS_ID > 0)) ? ((int)MODULE_PAYMENT_UNZER_ADVANCED_PREPARE_ORDER_STATUS_ID) : (0);

        // CUSTOMIZE THIS SETTING FOR THE NUMBER OF PAYMENT GROUPS NEEDED
        $this->num_groups = 5;

        if (is_object($order))
            $this->update_status;


        // Store online payment options in local variable
        for ($i = 1; $i <= $this->num_groups; $i++) {
            if (defined('MODULE_PAYMENT_UNZER_ADVANCED_GROUP' . $i) && constant('MODULE_PAYMENT_UNZER_ADVANCED_GROUP' . $i) != '') {
                // if (!isset($this->creditcardgroup[constant('MODULE_PAYMENT_UNZER_ADVANCED_GROUP' . $i . '_FEE')])) {
                    // $this->creditcardgroup[constant('MODULE_PAYMENT_UNZER_ADVANCED_GROUP' . $i . '_FEE')] = array();
                // }
                $payment_options = preg_split('[\,\;]', constant('MODULE_PAYMENT_UNZER_ADVANCED_GROUP' . $i));
                foreach ($payment_options as $option) {
                    $msg .= $option;
                    // $this->creditcardgroup[constant('MODULE_PAYMENT_UNZER_ADVANCED_GROUP' . $i . '_FEE')][] = $option;
                }
            }
        }

        /** V10 */
        if (("go" == $_POST['unzerIT']) && !isset($_SESSION['qlink'])) {
            $this->form_action_url = 'https://payment.unzerdirect.com/';
        } else {
            $this->form_action_url = tep_href_link(FILENAME_CHECKOUT_CONFIRMATION, '', 'SSL');
        }
    }

    /** Class methods */
    function update_status() {
        global $order, $unzer_fee, $HTTP_POST_VARS, $unzer_card;

        if (($this->enabled == true) && defined('MODULE_PAYMENT_UNZER_ZONE') && ((int) MODULE_PAYMENT_UNZER_ZONE > 0)) {
            $check_flag = false;
            $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_UNZER_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
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

        if (!tep_session_is_registered('unzer_card'))
            tep_session_register('unzer_card');

        if (isset($_POST['unzer_card']))
            $unzer_card = $_POST['unzer_card'];

        if (!tep_session_is_registered('cart_Unzer_ID'))
            tep_session_register('cart_Unzer_ID');

        if (isset($_GET['cart_Unzer_ID']))
            $unzer_card = $_GET['cart_Unzer_ID'];

        if (!tep_session_is_registered('unzer_fee')) {
            tep_session_register('unzer_fee');
        }
    }

    function javascript_validation() {
        $js = '  if (payment_value == "' . $this->code . '") {' . "\n" .
              '      var unzer_card_value = null;' . "\n" .
              '      if (document.checkout_payment.unzer_card.length) {' . "\n" .
              '          for (var i=0; i<document.checkout_payment.unzer_card.length; i++) {' . "\n" .
              '              if (document.checkout_payment.unzer_card[i].checked) {' . "\n" .
              '                  unzer_card_value = document.checkout_payment.unzer_card[i].value;' . "\n" .
              '              }' . "\n" .
              '          }' . "\n" .
              '      } else if (document.checkout_payment.unzer_card.checked) {' . "\n" .
              '          unzer_card_value = document.checkout_payment.unzer_card.value;' . "\n" .
              '      } else if (document.checkout_payment.unzer_card.value) {' . "\n" .
              '          unzer_card_value = document.checkout_payment.unzer_card.value;' . "\n" .
              '          document.checkout_payment.unzer_card.checked=true;' . "\n" .
              '      }' . "\n" .
              '      if (unzer_card_value == null) {' . "\n" .
              '          error_message = error_message + "' . MODULE_PAYMENT_UNZER_ADVANCED_TEXT_SELECT_CARD . '";' . "\n" .
              '          error = 1;' . "\n" .
              '      }' . "\n" .
              '      if (document.checkout_payment.cardlock.value == null) {' . "\n" .
              '          error_message = error_message + "' . MODULE_PAYMENT_UNZER_ADVANCED_TEXT_SELECT_CARD . '";' . "\n" .
              '          error = 1;' . "\n" .
              '      }' . "\n" .
              '  }' . "\n";
        return $js;
    }

    /**
     * @return bool
     */
    public function isSafariBrowser()
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $isSafari = false;
        if (preg_match('/Safari/i', $userAgent)) {
            $isSafari = (!preg_match('/Chrome/i', $userAgent));
        }

        return $isSafari;
    }

    /**
     * @return bool
     */
    public function isChromeBrowser()
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $isChrome = false;
        if (preg_match('/Chrome/i', $userAgent)) {
            $isChrome = true;
        }

        return $isChrome;
    }

    /* Define payment method selector on checkout page */
    public function selection() {
        global $order, $currencies, $unzer_card, $cardlock, $language;
        $qty_groups = 0;

        /** Count how many MODULE_PAYMENT_UNZER_ADVANCED_GROUP are configured. */
        for ($i = 1; $i <= $this->num_groups; $i++) {
            if (constant('MODULE_PAYMENT_UNZER_ADVANCED_GROUP' . $i) == '') {
                continue;
            }

            $qty_groups++;
        }


        if($qty_groups > 1) {
            $selection = array('id' => $this->code, 'module' => $this->title. tep_draw_hidden_field('cardlock', $cardlock ));
        }

        /** Parse all the configured MODULE_PAYMENT_UNZER_ADVANCED_GROUP */
        $selection['fields'] = array();
        $msg = '<table width="100%"><tr><td style="text-align: end;">';
        $optscount=0;
        for ($i = 1; $i <= $this->num_groups; $i++) {
            $options_text = '';
            if (defined('MODULE_PAYMENT_UNZER_ADVANCED_GROUP' . $i) && constant('MODULE_PAYMENT_UNZER_ADVANCED_GROUP' . $i) != '') {
                $payment_options = preg_split('[\,\;]', constant('MODULE_PAYMENT_UNZER_ADVANCED_GROUP' . $i));

                foreach ($payment_options as $option) {
                    $cost = (MODULE_PAYMENT_UNZER_ADVANCED_AUTOFEE == "No" || $option == 'viabill' ? "0" : "1");
                    if($option=="creditcard"){
                        $optscount++;
                        /** Read the logos defined on admin panel **/
                        $cards = explode(";",MODULE_PAYMENT_UNZER_CARD_LOGOS);
                        foreach ($cards as $optionc) {
                            $iconc = "";
                            if(file_exists(DIR_WS_ICONS.$optionc.".png")){
                                $iconc = DIR_WS_ICONS.$optionc.".png";
                            }elseif(file_exists(DIR_WS_ICONS.$optionc.".jpg")){
                                $iconc = DIR_WS_ICONS.$optionc.".jpg";
                            }elseif(file_exists(DIR_WS_ICONS.$optionc.".gif")){
                                $iconc = DIR_WS_ICONS.$optionc.".gif";
                            }elseif(file_exists(DIR_WS_ICONS.$optionc.".svg")){
                                $iconc = DIR_WS_ICONS.$optionc.".svg";
                            }

                            /** Define payment icons width */
                            $w = 'auto';
                            $h = 22;
                            $space = 5;

                            $msg .= tep_image($iconc,$optionc,$w,$h,'style="position:relative;border:0px;float:left;margin:'.$space.'px;" ');
                        }

                        /** Configuring the text to be shown for the payment group. If there is an input in the text field for that payment option, that value will be shown to the user, otherwise, the default value will be used.*/
                        if(defined('MODULE_PAYMENT_UNZER_ADVANCED_GROUP'.$i.'_TEXT') && constant('MODULE_PAYMENT_UNZER_ADVANCED_GROUP' . $i . '_TEXT') != ''){
                            $msg .= constant('MODULE_PAYMENT_UNZER_ADVANCED_GROUP' . $i . '_TEXT').'</td></tr></table>';
                        }else {
                            $msg .= $this->get_payment_options_name($option).'</td></tr></table>';
                        }

                        $options_text=$msg;

                        //$cost = $this->calculate_order_fee($order->info['total'], $fees[$i]);
                        if($qty_groups==1){
                            $selection = array(
                                'id' => $this->code,
                                'module' => '<table width="100%" border="0">
                                                <tr class="moduleRow table-selection" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectUnzerRowEffect(this, ' . ($optscount-1) . ',\''.$option.'\')">
                                                    <td class="main" style="height:22px;vertical-align:middle;">' .$options_text.($cost !=0 ? '</td><td class="main" style="height:22px;vertical-align:middle;"> (+ '.MODULE_PAYMENT_UNZER_ADVANCED_FEELOCKINFO.')' :'').'
                                                        </td>
                                                </tr>'.'
                                            </table>'.tep_draw_hidden_field('cardlock', $option));


                        }else{
                            $selection['fields'][] = array(
                                'title' => '<table width="100%" border="0">
                                                <tr class="moduleRow table-selection" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectUnzerRowEffect(this, ' . ($optscount-1) . ',\''.$option.'\')">
                                                    <td class="main" style="height:22px;vertical-align:middle;">' . $options_text.($cost !=0 ? '</td><td style="height:22px;vertical-align:middle;">(+ '.MODULE_PAYMENT_UNZER_ADVANCED_FEELOCKINFO.')' :'').'
                                                        </td>
                                                </tr>'.'
                                            </table>',
                                'field' => tep_draw_radio_field(
                                    'unzer_card',
                                    '',
                                    ($option==$cardlock ? true : false),
                                    ' onClick="setUnzer(); document.checkout_payment.cardlock.value = \''.$option.'\';" '
                                )
                            );
                        }/** end qty=1 */
                    }

                    if($option != "creditcard"){
                        //upload images to images/icons corresponding to your chosen cardlock groups in your payment module settings
                        //OPTIONAL image if different from cardlogo, add _payment to filename
                        $selectedopts = explode(",", $option);
                        $icon = "";
                        foreach($selectedopts as $option){

                            /**
                             * Check if method is apple-pay & browser is NOT Safari
                             * SKIP this option if true
                             */
                            if ('apple-pay' == $option && !$this->isSafariBrowser()) {
                                continue;
                            }

                            /**
                             * Check if method is google-pay & browser is NOT Chrome
                             * SKIP this option if true
                             */
                            if ('google-pay' == $option && !$this->isChromeBrowser()) {
                                continue;
                            }

                            /**
                             * Check if the option is "sofort" & if the order currency is NOT one of the following.
                             *
                             * SKIP this option if true
                             *
                             * !!! HARDCODED currencies !!!
                             */
                            if ('sofort' == $option && !in_array($order->info['currency'], ['EUR', 'GBP', 'PLN', 'CHF'])) {
                                continue;
                            }

                            /**
                             * Check if the option is "Unzer Direct Invoice"
                             * Check if the customer country is NOT one of the following.
                             * Check if the order currency is NOT one of the following.
                             * Check if total amount in under or exceed specified limits
                             *
                             * SKIP this option if true
                             *
                             * !!! HARDCODED VALUES !!!
                             */
                            if ('unzer-pay-later-invoice' == $option) {
                                if (!in_array($order->customer['country']['iso_code_2'], ['DE', 'AT', 'CH'])) {
                                    continue;
                                }
                                if (!in_array($order->info['currency'], ['EUR', 'CHF'])) {
                                    continue;
                                }
                                if (10 > $order->info['total'] || $order->info['total'] > 3500) {
                                    continue;
                                }
                            }

                            /**
                             * Check if the option is "Unzer Direct Debit"
                             * Check if the customer country is NOT one of the following.
                             * Check if the order currency is NOT one of the following.
                             * Check if total amount in under or exceed specified limits
                             *
                             * SKIP this option if true
                             *
                             * !!! HARDCODED VALUES !!!
                             */
                            if ('unzer-direct-debit' == $option) {
                                if (!in_array($order->customer['country']['iso_code_2'], ['DE', 'AT'])) {
                                    continue;
                                }
                                if (!in_array($order->info['currency'], ['EUR'])) {
                                    continue;
                                }
                                if (10 > $order->info['total'] || $order->info['total'] > 3500) {
                                    continue;
                                }
                            }

                            $optscount++;

                            $icon = "";
                            if(file_exists(DIR_WS_ICONS.$option.".png")){
                              $icon = DIR_WS_ICONS.$option.".png";
                            }elseif(file_exists(DIR_WS_ICONS.$option.".jpg")){
                              $icon = DIR_WS_ICONS.$option.".jpg";
                            }elseif(file_exists(DIR_WS_ICONS.$option.".gif")){
                              $icon = DIR_WS_ICONS.$option.".gif";
                            }elseif(file_exists(DIR_WS_ICONS.$option.".svg")){
                              $icon = DIR_WS_ICONS.$option.".svg";
                            }elseif(file_exists(DIR_WS_ICONS . $option . "_payment.png")){
                              $icon = DIR_WS_ICONS . $option . "_payment.png";
                            }elseif(file_exists(DIR_WS_ICONS . $option . "_payment.jpg")){
                              $icon = DIR_WS_ICONS . $option . "_payment.jpg";
                            }elseif(file_exists(DIR_WS_ICONS . $option . "_payment.gif")){
                              $icon = DIR_WS_ICONS . $option . "_payment.gif";
                            }elseif(file_exists(DIR_WS_ICONS . $option . "_payment.svg")){
                              $icon = DIR_WS_ICONS . $option . "_payment.svg";
                            }

                            /**
                             * Custom check for german language to add icon with german text
                             */
                            if ('german' == $language && 'unzer-pay-later-invoice' == $option) {
                                $icon = (file_exists(DIR_WS_ICONS.$option."_DE_payment.svg") ? DIR_WS_ICONS.$option."_DE_payment.svg" : $icon);
                            }

                            /** Make icon larger for "sofort" payment method, as it is for others. */
                            if ('sofort' == $option) {
                                $icon = (file_exists(DIR_WS_ICONS.$option."_payment.svg") ? DIR_WS_ICONS.$option."_payment.svg" : $icon);
                            }

                            $space = 5;

                            //define payment icon width
                            if(strstr($icon, "_payment")){
                                $w = 'auto';
                                $h = 45;
                            }else{
                                $w = 'auto';
                                $h = 22;
                            }

                            //$cost = $this->calculate_order_fee($order->info['total'], $fees[$i]);

                            /** Configuring the text to be shown for the payment option. */
                            $options_text = '<table width="100%">
                                                <tr>
                                                    <td>'. tep_image($icon, $this->get_payment_options_name($option), $w, $h, ' style="position:relative;border:0px;float:left;margin:'.$space.'px;" ') . '</td>
                                                    <td style="text-align: end;height: 27px;white-space:nowrap;vertical-align:middle;" >';

                            /** If there is an input in the text field for that payment option, that value will be shown to the user, otherwise, the default value will be used. */
                            if(defined('MODULE_PAYMENT_UNZER_ADVANCED_GROUP'.$i.'_TEXT') && constant('MODULE_PAYMENT_UNZER_ADVANCED_GROUP' . $i . '_TEXT') != ''){
                                $options_text .= constant('MODULE_PAYMENT_UNZER_ADVANCED_GROUP' . $i . '_TEXT').'</td></tr></table>';
                            }else {
                                $options_text .= $this->get_payment_options_name($option).'</td></tr></table>';
                            }


                            if($qty_groups==1){
                                $selection = array(
                                    'id' => $this->code,
                                    'module' => '<table width="100%" border="0">
                                                    <tr class="moduleRow table-selection" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectUnzerRowEffect(this, ' . ($optscount-1) . ',\''.$option.'\')">
                                                        <td class="main" style="height: 27px;white-space:nowrap;vertical-align:middle;">' .$options_text.($cost !=0 ? '</td><td style="height:22px;vertical-align:middle;"> (+ '.MODULE_PAYMENT_UNZER_ADVANCED_FEELOCKINFO.')' :'').'
                                                            </td>
                                                    </tr>'.'
                                                </table>'.tep_draw_hidden_field('cardlock', $option).tep_draw_hidden_field('unzer_card', (isset($fees[1])) ? $fees[1] : '0'));
                            }else{
                                $selection['fields'][] = array(
                                    'title' => '<table width="100%" border="0">
                                                    <tr class="moduleRow table-selection" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="selectUnzerRowEffect(this, ' . ($optscount-1) . ',\''.$option.'\')">
                                                        <td class="main" style="height: 27px;white-space:nowrap;vertical-align:middle;">' . $options_text.($cost !=0 ? '</td><td style="height:22px;vertical-align:middle;"> (+ '.MODULE_PAYMENT_UNZER_ADVANCED_FEELOCKINFO.')' :'').'
                                                            </td>
                                                    </tr>'.'
                                                </table>',
                                    'field' => tep_draw_radio_field(
                                        'unzer_card',
                                        '',
                                        ($option==$cardlock ? true : false),
                                        ' onClick="setUnzer();document.checkout_payment.cardlock.value = \''.$option.'\';" '
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

                function setUnzer() {
                    var radioLength = document.checkout_payment.payment.length;
                    for(var i = 0; i < radioLength; i++) {
                        document.checkout_payment.payment[i].checked = false;
                        if(document.checkout_payment.payment[i].value == "unzer_advanced") {
                            document.checkout_payment.payment[i].checked = true;
                        }
                    }
                }

                function selectUnzerRowEffect(object, buttonSelect, option) {
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
                    document.checkout_payment.unzer_card.checked = false;
                    if (document.checkout_payment.unzer_card[0]) {
                        document.checkout_payment.unzer_card[buttonSelect].checked=true;
                    } else {
                        document.checkout_payment.unzer_card.checked=true;
                    }
                    setUnzer();
                }

                document.addEventListener("DOMContentLoaded", function(){
                    /* For each payment method */
                    document.checkout_payment.payment.forEach(function(node) {
                      /* Apply for radio input and for his parent row */
                      [node, node.parentElement.parentElement].forEach(item => {
                        /* When a payment method is selected deselect all subpayment methods */
                        item.addEventListener("click", function() {
                            document.checkout_payment.unzer_card.forEach(function(sub_node) {
                                sub_node.checked=false;
                            });
                        });
                      })
                    })
                });

                /** Adjust width of payment methods table. */
                document.addEventListener("DOMContentLoaded", function(){
                    var unzerFirstRadio = document.querySelector("input[name=unzer_card]");
                    unzerFirstRadio.parentNode.parentNode.parentNode.parentNode.style = "min-width:100%;";

                    /** Align payment name to right. */
                    var unzerInputRadio = document.querySelectorAll("input[name=unzer_card]");
                    unzerInputRadio.forEach(item => {
                        item.parentNode.style = "text-align: end;";
                    });
                });

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
        global $cartID, $cart_Unzer_ID, $customer_id, $languages_id, $order, $order_total_modules, $oscTemplate;
        $order_id = substr($cart_Unzer_ID, strpos($cart_Unzer_ID, '-') + 1);

        //do not create preparing order id before payment confirmation is chosen by customer
        $mode = false;
        if(MODULE_PAYMENT_UNZER_ADVANCED_MODE == "Before" || $_POST['callunzer'] == "go"){
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
            $cart_Unzer_ID = $cartID . '-' . $insert_id;
            tep_session_register('cart_Unzer_ID');

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
        $fee_info = (MODULE_PAYMENT_UNZER_ADVANCED_AUTOFEE =="Yes" && $_POST["cardlock"] !="viabill" ? MODULE_PAYMENT_UNZER_ADVANCED_FEEINFO . '<br />' : '');

        return array('title' => $fee_info . $this->email_footer . $agree_terms);

        // if($this->email_footer !='' && $addorder==false){
            // return array('title' => $fee_info . $this->email_footer . $agree_terms);
        // }else{return false;}
    }

    /* Define payment button and data array to be sent */
    function process_button() {
        global $_POST, $customer_id, $order, $currencies, $currency, $languages_id, $language, $cart_Unzer_ID, $order_total_modules, $messageStack;
        /** collect all post fields and attach as hiddenfieds to button */

        if ( !class_exists('unzer_currencies') ) {
            include(DIR_FS_CATALOG . DIR_WS_CLASSES . 'unzer_currencies.php');
        }
        if (!($currencies instanceof unzer_currencies)) {
            $currencies = new unzer_currencies($currencies);
        }

        $process_button_string = '';
        $process_parameters = null;

        $unzer_merchant_id = MODULE_PAYMENT_UNZER_ADVANCED_MERCHANTID;
        $unzer_agreement_id = MODULE_PAYMENT_UNZER_ADVANCED_AGGREEMENTID;

        /** TODO: dynamic language switching instead of hardcoded mapping */
        $unzer_language = "da";
        switch ($language) {
            case "english": $unzer_language = "en";
                break;
            case "swedish": $unzer_language = "se";
                break;
            case "norwegian": $unzer_language = "no";
                break;
            case "german": $unzer_language = "de";
                break;
            case "french": $unzer_language = "fr";
                break;
        }
        $unzer_branding_id = "";

        $unzer_subscription = (MODULE_PAYMENT_UNZER_ADVANCED_SUBSCRIPTION == "Normal" ? "" : "1");
        $unzer_cardtypelock = $_POST['cardlock'];
        $unzer_autofee = (MODULE_PAYMENT_UNZER_ADVANCED_AUTOFEE == "No" || $unzer_cardtypelock == 'viabill' ? "0" : "1");
        $unzer_description = "Merchant ".$unzer_merchant_id." ".(MODULE_PAYMENT_UNZER_ADVANCED_SUBSCRIPTION == "Normal" ? "Authorize" : "Subscription");
        $order_id = substr($cart_Unzer_ID, strpos($cart_Unzer_ID, '-') + 1);
        $unzer_order_id = MODULE_PAYMENT_UNZER_ADVANCED_ORDERPREFIX.sprintf('%04d', $order_id);
        /** Calculate the total order amount for the order (the same way as in checkout_process.php) */
        $unzer_order_amount = 100 * $currencies->calculate($order->info['total'], true, $order->info['currency'], $order->info['currency_value'], '.', '');
        $unzer_currency_code = $order->info['currency'];
        $unzer_continueurl = tep_href_link(FILENAME_CHECKOUT_PROCESS, 'cart_Unzer_ID='.$cart_Unzer_ID, 'SSL');
        $unzer_cancelurl = tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code, 'SSL');
        $unzer_callbackurl = tep_href_link('callback10.php','oid='.$order_id,'SSL');
        $unzer_autocapture = (MODULE_PAYMENT_UNZER_ADVANCED_AUTOCAPTURE == "No" ? "0" : "1");
        $unzer_version ="v10";

        // $unzer_apikey = MODULE_PAYMENT_UNZER_ADVANCED_APIKEY;
        // $unzer_product_id = "P03";
        // $unzer_category = MODULE_PAYMENT_UNZER_ADVANCED_PAII_CAT;
        // $unzer_reference_title = $unzer_order_id;
        // $unzer_vat_amount = ($order->info['tax'] ? $order->info['tax'] : "0.00");


        //custom vars
        $process_parameters = [
            'agreement_id' => $unzer_agreement_id,
            'amount' => $unzer_order_amount,
            'autocapture' => $unzer_autocapture,
            'autofee' => $unzer_autofee,
            'callbackurl' => $unzer_callbackurl,
            'cancelurl' => $unzer_cancelurl,
            'continueurl' => $unzer_continueurl,
            'currency' => $unzer_currency_code,
            'description' => $unzer_description,
            'language' => $unzer_language,
            'merchant_id' => $unzer_merchant_id,
            'order_id' => $unzer_order_id,
            'payment_methods' => $unzer_cardtypelock,
            'subscription' => $unzer_subscription,
            'version' => 'v10',

            'invoice_address' => [
                'name' => ((isset($order->billing['firstname'])) ? ($order->billing['firstname']) : ('')) . ((isset($order->billing['lastname'])) ? (' ' . $order->billing['lastname']) : ('')),
                'att' => '',
                'company_name' => (isset($order->billing['company'])) ? ($order->billing['company']) : (''),
                'street' => (isset($order->billing['street_address'])) ? ($order->billing['street_address']) : (''),
                'house_number' => '',
                'house_extension' => '',
                'city' => (isset($order->billing['city'])) ? ($order->billing['city']) : (''),
                'zip_code' => (isset($order->billing['postcode'])) ? ($order->billing['postcode']) : (''),
                'region' => (isset($order->billing['state'])) ? ($order->billing['state']): (''),
                'country_code' => UnzerISO3166::alpha3($order->billing['country']['title']),
                'vat_no' => '',
                'phone_number' => '',
                'mobile_number' => (isset($order->customer['telephone'])) ? ($order->customer['telephone']) : (''),
                'email' => (isset($order->customer['email_address'])) ? ($order->customer['email_address']) : ('')
            ],

            'shipping_address' => [
                'name' => ((isset($order->delivery['firstname'])) ? ($order->delivery['firstname']) : ('')) . ((isset($order->billing['lastname'])) ? (' ' . $order->billing['lastname']) : ('')),
                'att' => '',
                'company_name' => (isset($order->delivery['company'])) ? ($order->delivery['company']) : (''),
                'street' => (isset($order->delivery['street_address'])) ? ($order->delivery['street_address']) : (''),
                'house_number' => '',
                'house_extension' => '',
                'city' => (isset($order->delivery['city'])) ? ($order->delivery['city']) : (''),
                'zip_code' => (isset($order->delivery['postcode'])) ? ($order->delivery['postcode']) : (''),
                'region' => (isset($order->delivery['state'])) ? ($order->delivery['state']) : (''),
                'country_code' => UnzerISO3166::alpha3($order->delivery['country']['title']),
                'vat_no' => '',
                'phone_number' => '',
                'mobile_number' => (isset($order->customer['telephone'])) ? ($order->customer['telephone']) : (''),
                'email' => (isset($order->customer['email_address'])) ? ($order->customer['email_address']) : ('')
            ],

            'basket' => [],

            'shopsystem' => [
                'name' => "OsCommerce",
                'version' => UNZER_MODULE_VERSION
            ]
        ];

        for ($i = 0, $n = sizeof($order->products); $i < $n; $i++) {
            $process_parameters['basket'][] = [
                'qty' =>  $order->products[$i]['qty'],
                'item_no' =>  $order->products[$i]['id'],
                'item_name' =>  $order->products[$i]['name'],
                'item_price' =>  ($order->products[$i]['final_price'] * $order->products[$i]['qty']),
                'vat_rate' =>  ''
            ];
        }

        if("go" == $_POST['callunzer']) {
            $apiorder = new UnzerApi();
            $apiorder->setOptions(MODULE_PAYMENT_UNZER_ADVANCED_USERAPIKEY);

            /** Set status request mode */
            $mode = (("Normal" == MODULE_PAYMENT_UNZER_ADVANCED_SUBSCRIPTION) ? ("") : ("1"));

            /** Set to create/update mode */
            $apiorder->mode = (("Normal" == MODULE_PAYMENT_UNZER_ADVANCED_SUBSCRIPTION) ? ("payments/") : ("subscriptions/"));

            /** Check if order exists. */
            $qid = null;
            $exists = $this->get_unzer_order_status($order_id, $mode);
            if (null == $exists["qid"]) {
                /** Create new unzer order */
                $storder = $apiorder->createorder($unzer_order_id, $unzer_currency_code, $process_parameters);
                $qid = $storder["id"];
            } else {
                $qid = $exists["qid"];
            }

            $storder = $apiorder->link($qid, $process_parameters);

            if (substr($storder['url'], 0, 5) <> 'https') {
                $messageStack->add_session(MODULE_PAYMENT_UNZER_ADVANCED_ERROR_COMMUNICATION_FAILURE, 'error');
                tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code, 'SSL'));
            }

            $process_button_string .= "<script>window.location.replace('" . $storder['url'] . "');</script>";
        }

        $process_button_string .=  "<input type='hidden' value='go' name='callunzer' />" . "\n".
                                   "<input type='hidden' value='" . $_POST['cardlock'] . "' name='cardlock' />";

        return $process_button_string;
    }

    /* Before order is processed */
    function before_process() {
        /** Called in FILENAME_CHECKOUT_PROCESS */
        /** check if order is approved by callback */
        global $customer_id, $order, $order_id, $order_totals, $order_total_modules, $sendto, $billto, $languages_id, $payment, $currencies, $cart, $cart_Unzer_ID;

        $order_id = substr($cart_Unzer_ID, strpos($cart_Unzer_ID, '-') + 1);

        $order_status_approved_id = (MODULE_PAYMENT_UNZER_ADVANCED_ORDER_STATUS_ID > 0 ? (int) MODULE_PAYMENT_UNZER_ADVANCED_ORDER_STATUS_ID : (int) DEFAULT_ORDERS_STATUS_ID);

        $mode = (MODULE_PAYMENT_UNZER_ADVANCED_SUBSCRIPTION == "Normal" ? "" : "1");
        $checkorderid = $this->get_unzer_order_status($order_id, $mode);
        if($checkorderid["oid"] != $order_id){
            tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code, 'SSL'));

        }

        if ( !class_exists('unzer_order') ) {
            include(DIR_FS_CATALOG . DIR_WS_CLASSES . 'unzer_order.php');
        }
        if (!($order instanceof unzer_order)) {
            $order = new unzer_order($order);
        }

        /** For debugging with FireBug / FirePHP */
        global $firephp;
        if (isset($firephp)) {
            $firephp->log($order_id, 'order_id');
        }

        /** Update order status */
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

    /* After order is processed */
    function after_process() {
        global $cart;

        $cart->reset(true);

        tep_session_unregister('cardlock');
        tep_session_unregister('order_id');
        tep_session_unregister('unzer_fee');
        tep_session_unregister('unzer_card');
        tep_session_unregister('cart_Unzer_ID');
        tep_session_unregister('qlink');

        tep_redirect(tep_href_link(FILENAME_CHECKOUT_SUCCESS, '', 'SSL'));
    }


    function get_error() {
        global $cart_Unzer_ID, $order, $currencies;
        $order_id = substr($cart_Unzer_ID, strpos($cart_Unzer_ID, '-') + 1);;

        if ( !class_exists('unzer_currencies') ) {
            include(DIR_FS_CATALOG . DIR_WS_CLASSES . 'unzer_currencies.php');
        }
        if (!($currencies instanceof unzer_currencies)) {
            $currencies = new unzer_currencies($currencies);
        }

        $error_desc = MODULE_PAYMENT_UNZER_ADVANCED_ERROR_CANCELLED;
        $error = array('title' => MODULE_PAYMENT_UNZER_ADVANCED_TEXT_ERROR, 'error' => $error_desc);


        //avoid order number already used: create a payment link if payment window was aborted
        //for some reason
        /*if(!$_SESSION["qlink"])    {
        try {

                    $apiorder= new UnzerApi();
            $apiorder->setOptions(MODULE_PAYMENT_UNZER_ADVANCED_USERAPIKEY);
            //$api->mode = 'payments?currency='.$unzer_currency_code.'&order_id='.
        $unzer_order_id = MODULE_PAYMENT_UNZER_ADVANCED_AGGREEMENTID."_".sprintf('%04d', $order_id);
        $mode = (MODULE_PAYMENT_UNZER_ADVANCED_SUBSCRIPTION == "Normal" ? "" : "1");
        $exists = $this->get_unzer_order_status($order_id, $mode);

        if($exists["qid"] == null){

        //create new unzer order
        $storder = $apiorder->createorder($unzer_order_id, $order->info['currency']);
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
        $err .= 'Unzer Status: ';
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
            $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_UNZER_ADVANCED_STATUS'");
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

        // new status for unzer prepare orders
        $check_query = tep_db_query("select orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_name = 'Unzer [preparing]' limit 1");

        if (tep_db_num_rows($check_query) < 1) {
            $status_query = tep_db_query("select max(orders_status_id) as status_id from " . TABLE_ORDERS_STATUS);
            $status = tep_db_fetch_array($status_query);

            $status_id = $status['status_id'] + 1;

            $languages = tep_get_languages();

            for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                tep_db_query("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values ('" . $status_id . "', '" . $languages[$i]['id'] . "', 'Unzer [preparing]')");
            }
        } else {
            $check = tep_db_fetch_array($check_query);
            $status_id = $check['orders_status_id'];
        }
        // compatibility ms2.2
        $flags_query = tep_db_query("describe " . TABLE_ORDERS_STATUS . " public_flag");
        if (tep_db_num_rows($flags_query) == 1) {
            tep_db_query("update " . TABLE_ORDERS_STATUS . " set public_flag = 1 and downloads_flag = 0 where orders_status_id = '" . $status_id . "'");
        }


        // new status for unzer rejected orders
        $check_query = tep_db_query("select orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_name = 'Unzer [rejected]' limit 1");

        if (tep_db_num_rows($check_query) < 1) {
            $status_query = tep_db_query("select max(orders_status_id) as status_id from " . TABLE_ORDERS_STATUS);
            $status = tep_db_fetch_array($status_query);

            $status_rejected_id = $status['status_id'] + 1;

            $languages = tep_get_languages();

            for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                tep_db_query("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values ('" . $status_rejected_id . "', '" . $languages[$i]['id'] . "', 'Unzer [rejected]')");
            }

        } else {
            $check = tep_db_fetch_array($check_query);

            $status_rejected_id = $check['orders_status_id'];
        }
        // compatibility ms2.2
        $flags_query = tep_db_query("describe " . TABLE_ORDERS_STATUS . " public_flag");
        if (tep_db_num_rows($flags_query) == 1) {
            tep_db_query("update " . TABLE_ORDERS_STATUS . " set public_flag = 1 and downloads_flag = 0 where orders_status_id = '" . $status_rejected_id . "'");
        }

        // new status for unzer pending orders
        $check_query = tep_db_query("select orders_status_id from " . TABLE_ORDERS_STATUS . " where orders_status_name = 'Pending [Unzer approved]' limit 1");

        if (tep_db_num_rows($check_query) < 1) {
            $status_query = tep_db_query("select max(orders_status_id) as status_id from " . TABLE_ORDERS_STATUS);
            $status = tep_db_fetch_array($status_query);

            $status_pending_id = $status['status_id'] + 1;

            $languages = tep_get_languages();

            for ($i = 0, $n = sizeof($languages); $i < $n; $i++) {
                tep_db_query("insert into " . TABLE_ORDERS_STATUS . " (orders_status_id, language_id, orders_status_name) values ('" . $status_pending_id . "', '" . $languages[$i]['id'] . "', 'Pending [Unzer approved]')");
            }
        } else {
            $check = tep_db_fetch_array($check_query);

            $status_pending_id = $check['orders_status_id'];
        }
        // compatibility ms2.2
        $flags_query = tep_db_query("describe " . TABLE_ORDERS_STATUS . " public_flag");
        if (tep_db_num_rows($flags_query) == 1) {
            tep_db_query("update " . TABLE_ORDERS_STATUS . " set public_flag = 1 and downloads_flag = 0 where orders_status_id = '" . $status_pending_id . "'");
        }

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable unzer_advanced', 'MODULE_PAYMENT_UNZER_ADVANCED_STATUS', 'False', 'Do you want to accept unzer payments?', '6', '3', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Preparing orders mode', 'MODULE_PAYMENT_UNZER_ADVANCED_MODE', 'Normal', 'Choose  mode:<br><b>Normal:</b> Create when payment window is opened.<br><b>Before:</b> Create when confirmation page is opened', '6', '3', 'tep_cfg_select_option(array(\'Normal\', \'Before\'), ', now())");


        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_UNZER_ADVANCED_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Unzer Merchant Id', 'MODULE_PAYMENT_UNZER_ADVANCED_MERCHANTID', '', 'Enter Merchant id', '6', '6', now())");

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Unzer Window user Agreement Id', 'MODULE_PAYMENT_UNZER_ADVANCED_AGGREEMENTID', '', 'Enter Window user Agreement id', '6', '6', now())");

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Order number prefix', 'MODULE_PAYMENT_UNZER_ADVANCED_ORDERPREFIX', '000', 'Enter prefix (Ordernumbers Must contain at least 3 characters)<br>Please Note: if upgrading from previous versions of Unzer 10, use format \"Window Agreement ID_\" ex. 1234_ if \"old\" orders statuses  are to be displayed in your order admin.<br>', '6', '6', now())");


        // tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Set Private key for your Unzer Payment Gateway', 'MODULE_PAYMENT_UNZER_ADVANCED_PRIVATEKEY', '', 'Enter your Private key.', '6', '6', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('API USER KEY', 'MODULE_PAYMENT_UNZER_ADVANCED_USERAPIKEY', '', 'Used for payments, and for handling transactions from your backend order page.', '6', '6', now())");

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Subscription payment', 'MODULE_PAYMENT_UNZER_ADVANCED_SUBSCRIPTION', 'Normal', 'Set Subscription payment as default (normal is single payment).', '6', '0', 'tep_cfg_select_option(array(\'Normal\', \'Subscription\'), ',now())");

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Autofee', 'MODULE_PAYMENT_UNZER_ADVANCED_AUTOFEE', 'No', 'Does customer pay the cardfee?<br>Set fees in <a href=\"https://insights.unzerdirect.com/\" target=\"_blank\"><u>Unzer manager</u></a>', '6', '0', 'tep_cfg_select_option(array(\'Yes\', \'No\'), ',now())");

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Autocapture', 'MODULE_PAYMENT_UNZER_ADVANCED_AUTOCAPTURE', 'No', 'Use autocapture?', '6', '0', 'tep_cfg_select_option(array(\'Yes\', \'No\'), ',now())");

        for ($i = 1; $i <= $this->num_groups; $i++) {
            if($i==1){
                $defaultlock='viabill';
                // $unzer_groupfee = '0:0';
            }else if($i==2){
                $defaultlock='creditcard';
                // $unzer_groupfee = '0:0';
            }else{
                $defaultlock='';
                // $unzer_groupfee ='0:0';
            }

            $unzer_group = (defined('MODULE_PAYMENT_UNZER_GROUP' . $i)) ? constant('MODULE_PAYMENT_UNZER_GROUP' . $i) : $defaultlock;

            // $unzer_groupfee = (defined('MODULE_PAYMENT_UNZER_GROUP' . $i . '_FEE')) ? constant('MODULE_PAYMENT_UNZER_GROUP' . $i . '_FEE') : $unzer_groupfee;

            tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Group " . $i . " Payment Options ', 'MODULE_PAYMENT_UNZER_ADVANCED_GROUP" . $i . "', '" . $unzer_group . "', 'Comma seperated Unzer payment options that are included in Group " . $i . ", maximum 255 chars (<a href=\'http://unzerdirect.com/documentation/appendixes/payment-methods\' target=\'_blank\'><u>available options</u></a>)<br>Example: creditcard OR viabill OR dankort<br>', '6', '6', now())");

            tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Group " . $i . " Payment Text ', 'MODULE_PAYMENT_UNZER_ADVANCED_GROUP" . $i . "_TEXT', '" . "" . "', 'Define text to be displayed for Group " . $i . " Payment Option. If this is not defined, the default text will be shown.<br>', '6', '6', now())");

            // tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Group " . $i . " Payments fee', 'MODULE_PAYMENT_UNZER_ADVANCED_GROUP" . $i . "_FEE', '" . $unzer_groupfee . "', 'Fee for Group " . $i . " payments (fixed fee:percentage fee)<br>Example: <b>1.45:0.10</b>', '6', '6', now())");
        }

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_UNZER_ADVANCED_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");

        // new settings
        // tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Paii shop category', 'MODULE_PAYMENT_UNZER_ADVANCED_PAII_CAT','', 'Shop category must be set, if using Paii cardlock (paii), ', '6', '0','tep_cfg_pull_down_paii_list(', now())");

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Preparing Order Status', 'MODULE_PAYMENT_UNZER_ADVANCED_PREPARE_ORDER_STATUS_ID', '" . $status_id . "', 'Set the status of prepared orders made with this payment module to this value', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Unzer Acknowledged Order Status', 'MODULE_PAYMENT_UNZER_ADVANCED_ORDER_STATUS_ID', '" . $status_pending_id . "', 'Set the status of orders made with this payment module to this value', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Unzer Rejected Order Status', 'MODULE_PAYMENT_UNZER_ADVANCED_REJECTED_ORDER_STATUS_ID', '" . $status_rejected_id . "', 'Set the status of rejected orders made with this payment module to this value', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");

        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Credit Card Logos', 'MODULE_PAYMENT_UNZER_CARD_LOGOS', '".implode(";",MODULE_AVAILABLE_CREDITCARDS)."', 'Images related to Credit Card Payment Method. Drag & Drop to change the visibility/order', '4', '0', 'show_logos', 'edit_logos(', now())");

    }


    function remove() {
        tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }


    function keys() {
        $keys = array(
            'MODULE_PAYMENT_UNZER_ADVANCED_STATUS',
            'MODULE_PAYMENT_UNZER_ADVANCED_ZONE',
            'MODULE_PAYMENT_UNZER_ADVANCED_SORT_ORDER',
            'MODULE_PAYMENT_UNZER_ADVANCED_MERCHANTID',
            'MODULE_PAYMENT_UNZER_ADVANCED_AGGREEMENTID',
            'MODULE_PAYMENT_UNZER_ADVANCED_USERAPIKEY',
            'MODULE_PAYMENT_UNZER_ADVANCED_ORDERPREFIX',
            'MODULE_PAYMENT_UNZER_ADVANCED_PREPARE_ORDER_STATUS_ID',
            'MODULE_PAYMENT_UNZER_ADVANCED_ORDER_STATUS_ID',
            'MODULE_PAYMENT_UNZER_ADVANCED_REJECTED_ORDER_STATUS_ID',
            'MODULE_PAYMENT_UNZER_ADVANCED_SUBSCRIPTION',
            'MODULE_PAYMENT_UNZER_ADVANCED_AUTOFEE',
            'MODULE_PAYMENT_UNZER_ADVANCED_AUTOCAPTURE',
            'MODULE_PAYMENT_UNZER_ADVANCED_MODE',
            'MODULE_PAYMENT_UNZER_CARD_LOGOS',
        );

        for ($i = 1; $i <= $this->num_groups; $i++) {
            $keys[] = 'MODULE_PAYMENT_UNZER_ADVANCED_GROUP' . $i;

            $keys[] = 'MODULE_PAYMENT_UNZER_ADVANCED_GROUP'. $i. '_TEXT';


            // $keys[] = 'MODULE_PAYMENT_UNZER_ADVANCED_GROUP' . $i . '_FEE';
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
        global $_POST, $order, $currencies, $unzer_fee;
        $unzer_fee = 0.0;
        if (isset($_POST['unzer_card']) && strpos($_POST['unzer_card'], ":")) {
            $unzer_fee = $this->calculate_order_fee($order->info['total'], $_POST['unzer_card']);
        }
    }


    function get_payment_options_name($payment_option) {
        switch ($payment_option) {
            case 'creditcard': return MODULE_PAYMENT_UNZER_ADVANCED_CREDITCARD_TEXT . "<br>" . MODULE_PAYMENT_UNZER_ADVANCED_CREDITCARD_DESCRIPTION;
            case 'unzer-pay-later-invoice': return MODULE_PAYMENT_UNZER_ADVANCED_DIRECT_INVOICE_TEXT . "<br>" . MODULE_PAYMENT_UNZER_ADVANCED_DIRECT_INVOICE_DESCRIPTION;
            case 'unzer-direct-debit': return MODULE_PAYMENT_UNZER_ADVANCED_DIRECT_DEBIT_TEXT . "<br>" . MODULE_PAYMENT_UNZER_ADVANCED_DIRECT_DEBIT_DESCRIPTION;
            case 'google-pay': return MODULE_PAYMENT_UNZER_ADVANCED_GOOGLE_PAY_TEXT . "<br>" . MODULE_PAYMENT_UNZER_ADVANCED_GOOGLE_PAY_DESCRIPTION;
            case 'apple-pay': return MODULE_PAYMENT_UNZER_ADVANCED_APPLE_PAY_TEXT . "<br>" . MODULE_PAYMENT_UNZER_ADVANCED_APPLE_PAY_DESCRIPTION;
            case 'sofort': return MODULE_PAYMENT_UNZER_ADVANCED_SOFORT_TEXT . "<br>" . MODULE_PAYMENT_UNZER_ADVANCED_SOFORT_DESCRIPTION;
            case 'paypal': return MODULE_PAYMENT_UNZER_ADVANCED_PAYPAL_TEXT . "<br>" . MODULE_PAYMENT_UNZER_ADVANCED_PAYPAL_DESCRIPTION;
            case 'klarna': return MODULE_PAYMENT_UNZER_ADVANCED_KLARNA_TEXT . "<br>" . MODULE_PAYMENT_UNZER_ADVANCED_KLARNA_DESCRIPTION;
        }
        return '';
    }


    public function sign($params, $api_key) {
        ksort($params);
        $base = implode(" ", $params);

        return hash_hmac("sha256", $base, $api_key);
    }


    private function get_unzer_order_status($order_id, $mode="") {
        $api = new UnzerApi();
        $api->setOptions(MODULE_PAYMENT_UNZER_ADVANCED_USERAPIKEY);

        try {
            $api->mode = ($mode == "" ? "payments?order_id=" : "subscriptions?order_id=");

            // Commit the status request, checking valid transaction id
            $st = $api->status(MODULE_PAYMENT_UNZER_ADVANCED_ORDERPREFIX . sprintf('%04d', $order_id));

            $eval = array();
            if ($st[0]["id"]) {
                $eval["oid"] = str_replace(MODULE_PAYMENT_UNZER_ADVANCED_ORDERPREFIX, "", $st[0]["order_id"]);
                $eval["qid"] = $st[0]["id"];
            } else {
                $eval["oid"] = null;
                $eval["qid"] = null;
            }

        } catch (Exception $e) {
            $eval = 'Unzer Status: ';
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
    $w = 'auto';
    $h = 22;
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
            }elseif(file_exists(DIR_FS_CATALOG . DIR_WS_IMAGES . 'icons/'.$optionc.".svg")){
                $iconc = DIR_WS_CATALOG_IMAGES . 'icons/'.$optionc.".svg";
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
    $w = 'auto';
    $h = 22;

    /** Scan images directory for logos */
    $files_array = array();
    if ( $dir = @dir(DIR_FS_CATALOG . DIR_WS_IMAGES . 'icons') ) {
        while ( $file = $dir->read() ) {
            /** Check if image is valid */
            if ( !is_dir(DIR_FS_CATALOG . DIR_WS_IMAGES . 'icons/' . $file ) && in_array(explode('.',$file)[0],MODULE_AVAILABLE_CREDITCARDS)) {
                if (in_array(substr($file, strrpos($file, '.')+1), array('gif', 'jpg', 'png', 'svg')) ) {
                    $files_array[] = $file;
                }
            }
        }
        sort($files_array);
        $dir->close();
    }

    /** Display logos to be shown */
    $values_array = !empty($values) ? explode(';', $values) : array();
    $output = '<h3>' . MODULE_PAYMENT_UNZER_CARD_LOGOS_SHOWN_CARDS . '</h3>' .
              '<ul id="ca_logos" style="list-style-type: none; margin: 0; padding: 5px; margin-bottom: 10px;">';

    foreach ($values_array as $optionc) {
        $iconc = "";
        if(file_exists(DIR_FS_CATALOG . DIR_WS_IMAGES . 'icons/'.$optionc.".png")){
            $iconc = DIR_WS_CATALOG_IMAGES . 'icons/' . $optionc.".png";
        }elseif(file_exists(DIR_FS_CATALOG . DIR_WS_IMAGES . 'icons/'.$optionc.".jpg")){
            $iconc = DIR_WS_CATALOG_IMAGES . 'icons/' . $optionc.".jpg";
        }elseif(file_exists(DIR_FS_CATALOG . DIR_WS_IMAGES . 'icons/'.$optionc.".gif")){
            $iconc = DIR_WS_CATALOG_IMAGES . 'icons/' . $optionc.".gif";
        }elseif(file_exists(DIR_FS_CATALOG . DIR_WS_IMAGES . 'icons/'.$optionc.".svg")){
            $iconc = DIR_WS_CATALOG_IMAGES . 'icons/' . $optionc.".svg";
        }

        if(strlen($iconc))
            $output .= '<li style="padding: 2px;">' . tep_image($iconc, $optionc, $w, $h) . tep_draw_hidden_field('bm_card_acceptance_logos[]', $optionc) . '</li>';
    }

    $output .= '</ul>';

    /** Display available logos */
    $output .= '<h3>' . MODULE_PAYMENT_UNZER_CARD_LOGOS_NEW_CARDS . '</h3><ul id="new_ca_logos" style="list-style-type: none; margin: 0; padding: 5px; margin-bottom: 10px;">';
    foreach ($files_array as $file) {
        /** Check if logo is not already displayed in "Available list" */
        if ( !in_array(explode(".",$file)[0], $values_array) ) {
            $output .= '<li style="padding: 2px;">' . tep_image(DIR_WS_CATALOG_IMAGES . 'icons/' . $file, explode(".",$file)[0], $w, $h) . tep_draw_hidden_field('bm_card_acceptance_logos[]', explode(".",$file)[0]) . '</li>';
        }
    }

    $output .= '</ul>';

    $output .= tep_draw_hidden_field('configuration[' . $key . ']', '', 'id="ca_logo_cards"');

    $drag_here_li = '<li id="caLogoEmpty" style="background-color: #fcf8e3; border: 1px #faedd0 solid; color: #a67d57; padding: 5px;">' . addslashes(MODULE_PAYMENT_UNZER_CARD_LOGOS_DRAG_HERE) . '</li>';

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
    $paiique = tep_db_query("select configuration_value  from ".TABLE_CONFIGURATION. " WHERE configuration_key  =  'MODULE_PAYMENT_UNZER_ADVANCED_PAII_CAT' ");
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
    return "<select name='configuration[MODULE_PAYMENT_UNZER_ADVANCED_PAII_CAT]' />
    ".$options."
    </select>";

  }
*/


?>
