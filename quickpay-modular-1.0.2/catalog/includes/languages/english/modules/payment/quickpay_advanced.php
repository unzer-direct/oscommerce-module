<?php
/*
  quickpay.php

  The Exchange Project - Community Made Shopping!
  http://www.theexchangeproject.org

  Copyright (c) 2008 Jakob H�y Biegel

  Released under the GNU General Public License
*/
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_TEXT_TITLE', 'QuickPay: Online Payment');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_TEXT_PUBLIC_TITLE', 'QuickPay: Online Payment');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_TEXT_DESCRIPTION', 'QuickPay Advanced Online payment');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_TEXT_EMAIL_FOOTER', 'Payment is now reserved. The payment has the following transaction-number: %s.' . "\n" . 'When the order is handled, the amount is transferred to ' . STORE_NAME);
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_TEXT_SELECT_CARD', '* Select what kind of payment type you want to use for your online payment\n');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_TEXT_WAIT', 'Please wait a moment. Payment page is prepared...');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_TEXT_ERROR', 'Unable to process online payment');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_FEEINFO', '(Fee is added in payment window)');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_FEELOCKINFO', ' fee');
define('DENUNCIATION', 'The order is paid with ViaBill. The amount owed can only be paid with releasing effect to ViaBill, which sends separate collection. Payment cannot be made by offsetting claims arising from other legal matters.');

if (!defined('HEADING_RETURN_POLICY')) define('HEADING_RETURN_POLICY','Returns policy summary');
if (!defined('TEXT_VIEW')) define('TEXT_VIEW','View details');
if (!defined('TEXT_RETURN_POLICY')) define('TEXT_RETURN_POLICY','Summary of returns policy');
if (!defined('ACCEPT_CONDITIONS')) define('ACCEPT_CONDITIONS','I have read and agree to the terms &amp; conditions: ');
if (!defined('CONDITION_AGREEMENT_ERROR')) define('CONDITION_AGREEMENT_ERROR','Please agree to our terms & conditions before placing your order.');

/* Transaction errors */
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_ERROR_MERCHANT_UNKNOWN', 'Unknown Merchant Number');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_ERROR_CARDNO_NOT_VALID', 'Invalid card number');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_ERROR_CVC_NOT_VALID', 'Invalid control digits');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_ERROR_ORDERID', 'Invalid or missing OrderId');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_ERROR_TRANSACTION_DECLINED', 'Transaction was not approved');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_ERROR_WRONG_NUMBER_FORMAT', 'Invalid format of amount');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_ERROR_ILLEGAL_TRANSACTION', 'Invalid transaction');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_ERROR_TRANSACTION_EXPIRED', 'Transactionen time out');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_ERROR_NO_ANSWER', 'No reply');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_ERROR_SYSTEM_FAILURE', 'System error');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_ERROR_CARD_EXPIRED', 'Card has expired');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_ERROR_COMMUNICATION_FAILURE', 'Communication error');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_ERROR_INTERNAL_FAILURE', 'Internal error');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_ERROR_CARD_NOT_REGISTERED', 'Not in the system');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_ERROR_RETRY_FAILURE', 'Unable to process transaction twice');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_ERROR_UNKNOWN', 'Error in the typed text');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_ERROR_CANCELLED', 'Transaction was cancelled') ;

// Name of credit cards options (3D Secure)
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_JCB_3D_TEXT', 'JCB 3D-Secure');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_MAESTRO_3D_TEXT', 'Maestro 3D-Secure');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_MAESTRO_DK_3D_TEXT', 'Maestro 3D-Secure (Danish)');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_MASTERCARD_3D_TEXT', 'MasterCard 3D-Secure');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_MASTERCARD_DK_3D_TEXT', 'Mastercard 3D-Secure (Danish)');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_MASTERCARD_DEBET_3D_TEXT', 'MasterCard Debit 3D-Secure');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_MASTERCARD_DEBET_DK_3D_TEXT', 'Mastercard Debit 3D-Secure (Danish)');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_VISA_3D_TEXT', 'Visa 3D-Secure');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_VISA_DK_3D_TEXT', 'Visa 3D-Secure (Danish)');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_VISA_ELECTRON_3D_TEXT', 'Visa Electron 3D-Secure');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_VISA_ELECTRON_DK_3D_TEXT', 'Visa Electron 3D-Secure (Danish)');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_VISA_DEBET_3D_TEXT', 'Visacard Debit 3D-secure ');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_VISA_DEBET_DK_3D_TEXT', 'Visacard Debit 3D-secure (Danish)');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_CREDITCARD_3D_TEXT', 'Creditcards 3D-secure');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_DANKORT_3D_TEXT', 'Dankort 3D-secure');

/* Name of credit cards options */
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_AMERICAN_EXPRESS_TEXT', 'American Express');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_AMERICAN_EXPRESS_DK_TEXT', 'American Express (Danish)');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_DANKORT_TEXT', 'Dankort');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_DINERS_TEXT', 'Diners Club');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_DINERS_DK_TEXT', 'Diners Club (Danish)');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_JCB_TEXT', 'JCB');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_MASTERCARD_TEXT', 'Mastercard');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_MASTERCARD_DK_TEXT', 'Mastercard (Danish)');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_MASTERCARD_DEBET_TEXT', 'Mastercard Debit');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_MASTERCARD_DEBET_DK_TEXT', 'Mastercard Debit(Danish)');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_VISA_TEXT', 'Visa');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_VISA_DK_TEXT', 'Visa (Danish)');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_VISA_ELECTRON_TEXT', 'Visa Electron');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_VISA_ELECTRON_DK_TEXT', 'Visa Electron (Danish)');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_FBG1886_TEXT', 'Forbrugsforeningen af 1886');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_CREDITCARD_TEXT', 'Creditcards');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_PAYPAL_TEXT', 'Paypal');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_SOFORT_TEXT', 'Sofort');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_VIABILL_TEXT', 'ViaBill');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_MOBILEPAY_TEXT', 'Mobilepay payment');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_BITCOIN_TEXT', 'BitCoin');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_SWISH_TEXT', 'Swish');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_TRUSTLY_TEXT', 'Trustly');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_KLARNA_TEXT', 'Klarna');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_MAESTRO_TEXT', 'Maestro');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_IDEAL_TEXT', 'Ideal');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_PAYSAFECARD_TEXT', 'Paysafecard');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_RESURS_TEXT', 'Resurs');
define('MODULE_PAYMENT_QUICKPAY_ADVANCED_VIPPS_TEXT', 'Vipps');
/* Admin pannel > creditcard logos configuration */
define('MODULE_PAYMENT_QUICKPAY_CARD_LOGOS_SHOWN_CARDS', 'Shown logos');
define('MODULE_PAYMENT_QUICKPAY_CARD_LOGOS_NEW_CARDS', 'Available logos');
define('MODULE_PAYMENT_QUICKPAY_CARD_LOGOS_DRAG_HERE', 'Drag here');

// define('MODULE_PAYMENT_QUICKPAY_ADVANCED_DANSKE_DK_TEXT', 'Danske Netbank');
// define('MODULE_PAYMENT_QUICKPAY_ADVANCED_EDANKORT_TEXT', 'eDankort');
// define('MODULE_PAYMENT_QUICKPAY_ADVANCED_NORDEA_DK_TEXT', 'Nordea Netbank');
// define('MODULE_PAYMENT_QUICKPAY_ADVANCED_IBILL_DESCRIPTION', 'Buy now - pay when you wish');
// define('MODULE_PAYMENT_QUICKPAY_ADVANCED_PAII_TEXT', 'Paii mobile payment');
// define('MODULE_PAYMENT_QUICKPAY_ADVANCED_IBILL_TEXT', 'ViaBill payment');
?>
