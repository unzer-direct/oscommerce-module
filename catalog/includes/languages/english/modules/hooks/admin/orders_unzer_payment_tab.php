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

define('TAB_UNZER_TRANSACTION','Unzer Transaction [%s]');
define('UNZER_AUTHORISED','authorised');
define('UNZER_CAPTURED','captured');
define('UNZER_PART_CAPTURED','part captured');
define('UNZER_REFUNDED','refunded');
define('UNZER_PART_REFUNDED','part refunded');
define('UNZER_REVERSED','reversed');
define('UNZER_ERROR','ERROR %s');
define('ENTRY_UNZER_TRANSACTION_TYPE','reversed');
define('ENTRY_UNZER_PAYMENT_LINK','reversed');
define('ENTRY_UNZER_TRANSACTION', 'Unzer balance:');
define('ENTRY_UNZER_CARDHASH', 'Transaction type:');
define('IMAGE_TRANSACTION_CAPTURE_INFO', 'Capture transaction');
define('IMAGE_TRANSACTION_REVERSE_INFO', 'Cancel payment');
define('IMAGE_TRANSACTION_CREDIT_INFO', 'Credit payment');
define('IMAGE_TRANSACTION_TIME_INFO_GREEN', 'Capture possible wihtin PBS-guaranteed period');
define('IMAGE_TRANSACTION_TIME_INFO_YELLOW', 'Last day of PBS-guaranteed capture');
define('IMAGE_TRANSACTION_TIME_INFO_RED', 'Last day of PBS-guaranteed capture passedd');
define('INFO_UNZER_CAPTURED', 'Payment is captured');
define('INFO_UNZER_CREDITED', 'Amount is credited');
define('INFO_UNZER_REVERSED', 'Payment is cancelled');
define('ENTRY_UNZER_TRANSACTION_ID', 'Transaction-id:');
define('CONFIRM_REVERSE', 'Do you want to cancel this payment?');
define('CONFIRM_CAPTURE', 'Warning: Transaction amount is not identical to order amount. Do you want to capture the payment?');
define('CONFIRM_CREDIT', 'Do you want to credit the customer this amout?');
define('PENDING_STATUS', 'Awaiting aquirer approval.');
define('PAYMENTLINK_INFO', 'Send this link to customer if transaction problem.');
define('SUBSCRIPTION_ADMIN'	, 'Subscription payment and recurring subscription payment is not implemented in this basic free version.<br>Use the Unzer manager for administration. Please contact <a href="mailto:info@blkom.dk" ><b>developer</b></a> for implementation of extended subscription version<br>');
define('ENTRY_UNZER_STATUS','Gateway status');
if (!defined('ENTRY_ADD_COMMENT')) define('ENTRY_ADD_COMMENT','Comment:');
