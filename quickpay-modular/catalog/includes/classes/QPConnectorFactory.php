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

/**
 * Quickpay v10+ php library
 *
 * Singleton for easy retrieval of the Quickpay connector implementation.
 */

class QPConnectorFactory {

    public static function getConnector() {
        static $inst = null;
        if ($inst === null) {
            $inst = new QPConnectorCurl();
        }
        return $inst;
    }

    private function __construct() {
    }
}
?>
