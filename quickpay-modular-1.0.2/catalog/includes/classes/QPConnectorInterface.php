<?php
/**
 * Quickpay v10+ php library
 *
 * This interface must be implemented by any Quickpay connectors.
 */

interface QPConnectorInterface {
    public function request($data);
}
?>
