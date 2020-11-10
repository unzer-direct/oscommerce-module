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
 * This interface must be implemented by any Quickpay connectors.
 */
interface QPConnectorInterface {
    public function request($data);
}
?>
