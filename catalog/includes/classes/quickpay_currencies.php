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

class quickpay_currencies extends currencies {

    // subclass constructor
    function __construct($currencies) {
        foreach (get_object_vars($currencies) as $key => $value) {
            $this->$key = $value;
        }
    }

    // extension methods
    function calculate($number, $calculate_currency_value = true, $currency_type = '', $currency_value = '', $currency_decimal_point = '', $currency_thousands_point = '@') {
        global $currency;

        if (empty($currency_type))
            $currency_type = $currency;
        if (empty($currency_decimal_point))
            $currency_decimal_point = $this->currencies[$currency_type]['decimal_point'];
        if ($currency_thousands_point == '@')
            $currency_thousands_point = $this->currencies[$currency_type]['thousands_point'];

        $number_display = $number;
        if ($calculate_currency_value) {
            $rate = (tep_not_null($currency_value)) ? $currency_value : $this->currencies[$currency_type]['value'];
            $number_display = $number * $rate;
        }

        return number_format($number_display, $this->currencies[$currency_type]['decimal_places'], $currency_decimal_point, $currency_thousands_point);
    }
}
