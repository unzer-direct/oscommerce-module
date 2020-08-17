<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2017 osCommerce

  Released under the GNU General Public License
*/

// 2.3.4BS Edge compatibility
if (!defined('DIR_WS_ICONS')) define('DIR_WS_ICONS','images/icons/');

class bm_quickpay {
    var $group = 'boxes';
    var $title;
    var $description;
    var $sort_order;
    var $enabled = false;

    function __construct() {
        $this->code = get_class($this);
        $this->title = MODULE_BOXES_QUICKPAY_TITLE;
        $this->description = MODULE_BOXES_QUICKPAY_DESCRIPTION;

        if ( defined('MODULE_BOXES_QUICKPAY_STATUS') ) {
            $this->sort_order = MODULE_BOXES_QUICKPAY_SORT_ORDER;
            $this->enabled = (MODULE_BOXES_QUICKPAY_STATUS == 'True');
            $this->group = ((MODULE_BOXES_QUICKPAY_CONTENT_PLACEMENT == 'Left Column') ? 'boxes_column_left' : 'boxes_column_right');
        }
    }

    function execute() {
        global $oscTemplate;

        //display accepted payments. Use option creditcard (=all creditcards) OR specified cardtype locks
        $output = '';
        //define payment icon width
        $w = 35;
        $h = 22;
        $space = 5;
        for ($i = 1; $i <= 5; $i++) {
            if (!defined('MODULE_PAYMENT_QUICKPAY_ADVANCED_GROUP' . $i) || constant('MODULE_PAYMENT_QUICKPAY_ADVANCED_GROUP' . $i) == '') {
                continue;
            }
            $qty_groups++;
        }

        for ($i = 1; $i <= 5; $i++) {
            if (defined('MODULE_PAYMENT_QUICKPAY_ADVANCED_GROUP' . $i) && constant('MODULE_PAYMENT_QUICKPAY_ADVANCED_GROUP' . $i) != '') {
                $payment_options = preg_split('[\,\;]', constant('MODULE_PAYMENT_QUICKPAY_ADVANCED_GROUP' . $i));
                foreach ($payment_options as $option) {
                    switch($option) {
                        case "creditcard":
                            //You can extend the following cards-array and upload corresponding titled images to images/icons
                            $cards= array('dankort','visa','american-express','jcb','maestro','mastercard');
                            foreach ($cards as $optionc) {
                                $iconc ="";
                                $iconc = (file_exists(DIR_WS_ICONS.$optionc.".png") ? DIR_WS_ICONS.$optionc.".png": $iconc);
                                $iconc = (file_exists(DIR_WS_ICONS.$optionc.".jpg") ? DIR_WS_ICONS.$optionc.".jpg": $iconc);
                                $iconc = (file_exists(DIR_WS_ICONS.$optionc.".gif") ? DIR_WS_ICONS.$optionc.".gif": $iconc);

                                $output .= tep_image($iconc,$optionc,$w,$h,'style="position:relative;border:0px;float:left;margin:'.$space.'px; " ');
                            }
                        break;
                        case "3d-creditcard":
                            //You can extend the following cards-array and upload corresponding titled images to images/icons
                            $cards= array('3d-visa','3d-jcb','3d-maestro','3d-mastercard');
                            foreach ($cards as $optionc) {
                                $iconc ="";
                                $iconc = (file_exists(DIR_WS_ICONS.$optionc.".png") ? DIR_WS_ICONS.$optionc.".png": $iconc);
                                $iconc = (file_exists(DIR_WS_ICONS.$optionc.".jpg") ? DIR_WS_ICONS.$optionc.".jpg": $iconc);
                                $iconc = (file_exists(DIR_WS_ICONS.$optionc.".gif") ? DIR_WS_ICONS.$optionc.".gif": $iconc);

                                $output .= tep_image($iconc,$optionc,$w,$h,'style="position:relative;border:0px;float:left;margin:'.$space.'px; " ');
                            }
                        break;

                        default:
                            //upload images to images/icons corresponding to your chosen cardlock groups in your payment module settings
                            $selectedopts = explode(",",$option);
                            foreach($selectedopts as $option){
                                $icon ="";
                                $icon = (file_exists(DIR_WS_ICONS.$option.".png") ? DIR_WS_ICONS.$option.".png": $icon);
                                $icon = (file_exists(DIR_WS_ICONS.$option.".jpg") ? DIR_WS_ICONS.$option.".jpg": $icon);
                                $icon = (file_exists(DIR_WS_ICONS.$option.".gif") ? DIR_WS_ICONS.$option.".gif": $icon);
                                $icon = (file_exists(DIR_WS_ICONS.$option."_payment.png") && $qty_groups == 1? DIR_WS_ICONS.$option."_payment.png": $icon);

                                //define payment icon width
                                if (strstr($icon, "_payment")) {
                                    $w = 120;
                                    $h = 27;
                                    $space = 9;
                                } else {
                                    $w = 36;
                                    $h = 22;
                                    $space = 5;
                                }
                                $output .= tep_image($icon,str_replace("ibill", "ViaBill",$option),$w,$h,' style="position:relative;border:0px;float:left;margin:'.$space.'px; " ');
                            }

                    } // end switch
                } // end foreach $payment_options
            } // end if defined
        } // end for 1 to 5 advanced group

        if($output) {
            $output = '<div class="ui-widget-content infoBoxContents" style="display:inline-block;border-top:0px;text-align:center;" >'.$output.'</div>';
        }

        if (strlen($output)) {
            ob_start();
            include('includes/modules/boxes/templates/quickpay.php');
            $data = ob_get_clean();

            $oscTemplate->addBlock($data, $this->group);
        }
    }

    function isEnabled() {
        return $this->enabled;
    }

    function check() {
        return defined('MODULE_BOXES_QUICKPAY_STATUS');
    }

    function install() {
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable Information Module', 'MODULE_BOXES_QUICKPAY_STATUS', 'True', 'Do you want to add the module to your shop?', '6', '1', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Content Placement', 'MODULE_BOXES_QUICKPAY_CONTENT_PLACEMENT', 'Left Column', 'Should the module be loaded in the left or right column?', '6', '1', 'tep_cfg_select_option(array(\'Left Column\', \'Right Column\'), ', now())");
        tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort Order', 'MODULE_BOXES_QUICKPAY_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
    }

    function remove() {
        tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
        return array('MODULE_BOXES_QUICKPAY_STATUS', 'MODULE_BOXES_QUICKPAY_CONTENT_PLACEMENT', 'MODULE_BOXES_QUICKPAY_SORT_ORDER');
    }
}
?>
