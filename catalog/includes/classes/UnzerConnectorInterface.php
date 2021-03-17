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
 * Unzer v10+ php library
 *
 * This interface must be implemented by any Unzer connectors.
 */
interface UnzerConnectorInterface {
    public function request($data);
}
?>
