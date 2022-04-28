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

define('MODULE_PAYMENT_UNZER_ADVANCED_TEXT_TITLE', 'Unzer: Online Payment');
define('MODULE_PAYMENT_UNZER_ADVANCED_TEXT_PUBLIC_TITLE', 'Unzer: Online Payment');
define('MODULE_PAYMENT_UNZER_ADVANCED_TEXT_DESCRIPTION', 'Unzer Advanced Online payment');
define('MODULE_PAYMENT_UNZER_ADVANCED_TEXT_EMAIL_FOOTER', 'Payment is now reserved. The payment has the following transaction-number: %s.' . "\n" . 'When the order is handled, the amount is transferred to ' . STORE_NAME);
define('MODULE_PAYMENT_UNZER_ADVANCED_TEXT_SELECT_CARD', '* Select what kind of payment type you want to use for your online payment\n');
define('MODULE_PAYMENT_UNZER_ADVANCED_TEXT_WAIT', 'Please wait a moment. Payment page is prepared...');
define('MODULE_PAYMENT_UNZER_ADVANCED_TEXT_ERROR', 'Unable to process online payment');
define('MODULE_PAYMENT_UNZER_ADVANCED_FEEINFO', '(Fee is added in payment window)');
define('MODULE_PAYMENT_UNZER_ADVANCED_FEELOCKINFO', ' fee');
define('DENUNCIATION', 'The order is paid with ViaBill. The amount owed can only be paid with releasing effect to ViaBill, which sends separate collection. Payment cannot be made by offsetting claims arising from other legal matters.');

if (!defined('HEADING_RETURN_POLICY')) define('HEADING_RETURN_POLICY','Returns policy summary');
if (!defined('TEXT_VIEW')) define('TEXT_VIEW','View details');
if (!defined('TEXT_RETURN_POLICY')) define('TEXT_RETURN_POLICY','Summary of returns policy');
if (!defined('ACCEPT_CONDITIONS')) define('ACCEPT_CONDITIONS','I have read and agree to the terms &amp; conditions: ');
if (!defined('CONDITION_AGREEMENT_ERROR')) define('CONDITION_AGREEMENT_ERROR','Please agree to our terms & conditions before placing your order.');

/* Transaction errors */
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_MERCHANT_UNKNOWN', 'Unknown Merchant Number');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_CARDNO_NOT_VALID', 'Invalid card number');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_CVC_NOT_VALID', 'Invalid control digits');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_ORDERID', 'Invalid or missing OrderId');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_TRANSACTION_DECLINED', 'Transaction was not approved');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_WRONG_NUMBER_FORMAT', 'Invalid format of amount');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_ILLEGAL_TRANSACTION', 'Invalid transaction');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_TRANSACTION_EXPIRED', 'Transaction time out');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_NO_ANSWER', 'No reply');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_SYSTEM_FAILURE', 'System error');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_CARD_EXPIRED', 'Card has expired');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_COMMUNICATION_FAILURE', 'Communication error');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_INTERNAL_FAILURE', 'Internal error');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_CARD_NOT_REGISTERED', 'Not in the system');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_RETRY_FAILURE', 'Unable to process transaction twice');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_UNKNOWN', 'Error in the typed text');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_CANCELLED', 'Transaction was cancelled') ;

/* Name of credit cards options & description */
define('MODULE_PAYMENT_UNZER_ADVANCED_CREDITCARD_TEXT', 'Creditcards');
define('MODULE_PAYMENT_UNZER_ADVANCED_CREDITCARD_DESCRIPTION', 'Pay with Visa, Mastercard or Maestro card');
define('MODULE_PAYMENT_UNZER_ADVANCED_DIRECT_INVOICE_TEXT', 'Unzer Invoice');
define('MODULE_PAYMENT_UNZER_ADVANCED_DIRECT_INVOICE_DESCRIPTION', 'Pay 14 days after delivery');
define('MODULE_PAYMENT_UNZER_ADVANCED_DIRECT_DEBIT_TEXT', 'Unzer Direct Debit');
define('MODULE_PAYMENT_UNZER_ADVANCED_DIRECT_DEBIT_DESCRIPTION', 'Pay directly from your bank account');
define('MODULE_PAYMENT_UNZER_ADVANCED_GOOGLE_PAY_TEXT', 'Google Pay');
define('MODULE_PAYMENT_UNZER_ADVANCED_GOOGLE_PAY_DESCRIPTION', 'Pay with Google Pay');
define('MODULE_PAYMENT_UNZER_ADVANCED_APPLE_PAY_TEXT', 'Apple Pay');
define('MODULE_PAYMENT_UNZER_ADVANCED_APPLE_PAY_DESCRIPTION', 'Pay with your Apple device');
define('MODULE_PAYMENT_UNZER_ADVANCED_SOFORT_TEXT', 'SOFORT');
define('MODULE_PAYMENT_UNZER_ADVANCED_SOFORT_DESCRIPTION', 'Pay with SOFORT Banking');
define('MODULE_PAYMENT_UNZER_ADVANCED_PAYPAL_TEXT', 'Paypal');
define('MODULE_PAYMENT_UNZER_ADVANCED_PAYPAL_DESCRIPTION', 'Pay with PayPal');
define('MODULE_PAYMENT_UNZER_ADVANCED_KLARNA_TEXT', 'Klarna');
define('MODULE_PAYMENT_UNZER_ADVANCED_KLARNA_DESCRIPTION', 'Pay with Klarna Invoice, Klarna Direct Debit or Klarna Instalments');

/* Admin pannel > creditcard logos configuration */
define('MODULE_PAYMENT_UNZER_CARD_LOGOS_SHOWN_CARDS', 'Shown logos');
define('MODULE_PAYMENT_UNZER_CARD_LOGOS_NEW_CARDS', 'Available logos');
define('MODULE_PAYMENT_UNZER_CARD_LOGOS_DRAG_HERE', 'Drag here');
?>
