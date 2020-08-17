<?php
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
