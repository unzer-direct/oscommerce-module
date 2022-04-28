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

define('MODULE_PAYMENT_UNZER_ADVANCED_TEXT_TITLE', 'Kreditkort');
define('MODULE_PAYMENT_UNZER_ADVANCED_TEXT_PUBLIC_TITLE', 'Kreditkort');
define('MODULE_PAYMENT_UNZER_ADVANCED_TEXT_DESCRIPTION', 'Betalingen overføres elektronisk, ved betaling med');
define('MODULE_PAYMENT_UNZER_ADVANCED_TEXT_EMAIL_FOOTER', 'Betalingen er nu reserveret hos PBS. Din online betaling har fået transaktions-id: %s.' . "\n" . 'Når ordren ekspederes bliver beløbet overført til ' . STORE_NAME);
define('MODULE_PAYMENT_UNZER_ADVANCED_TEXT_SELECT_CARD', '* Vælg hvilken måde du vil benytte til online betaling\n');
define('MODULE_PAYMENT_UNZER_ADVANCED_TEXT_WAIT', 'Vent venligst et øjeblik. Betalingsside forberedes...');
define('MODULE_PAYMENT_UNZER_ADVANCED_TEXT_ERROR', 'Online betaling kunne ikke gennemføres');
define('MODULE_PAYMENT_UNZER_ADVANCED_FEEINFO', '(evt. gebyr tilføjes ved betaling)');
define('MODULE_PAYMENT_UNZER_ADVANCED_FEELOCKINFO', ' evt. gebyr');
define('DENUNCIATION', 'Ordren betales med ViaBill. Det skyldige beløb kan alene betales med frigørende virkning til ViaBill, som fremsender særskilt opkrævning. Betaling kan ikke ske ved modregning af krav, der udspringer af andre retsforhold.');

if (!defined('HEADING_RETURN_POLICY')) define('HEADING_RETURN_POLICY','Handelsbetingelser');
if (!defined('TEXT_VIEW')) define('TEXT_VIEW','Læs dem');
if (!defined('TEXT_RETURN_POLICY')) define('TEXT_RETURN_POLICY','Der er ingen returret på denne ordre da den omhandler madvare. </font>');
if (!defined('ACCEPT_CONDITIONS')) define('ACCEPT_CONDITIONS','Jeg har læst og accepteret betingelserne: ');
if (!defined('CONDITION_AGREEMENT_ERROR')) define('CONDITION_AGREEMENT_ERROR','Du skal acceptere vores betingelser før du kan bestille');

// Transaction errors
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_MERCHANT_UNKNOWN', 'Ukendt Merchant Nr');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_CARDNO_NOT_VALID', 'Ugyldigt kortnummer');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_CVC_NOT_VALID', 'Ugyldige kontrolcifre');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_ORDERID', 'OrderID ugyldigt eller mangler');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_TRANSACTION_DECLINED', 'Transaktionen blev afbrudt');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_WRONG_NUMBER_FORMAT', 'Beløbet blev angivet i et forkert format');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_ILLEGAL_TRANSACTION', 'Ugyldig transaktion');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_TRANSACTION_EXPIRED', 'Transaktionen er udløbet');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_NO_ANSWER', 'Intet svar');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_SYSTEM_FAILURE', 'Systemfejl');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_CARD_EXPIRED', 'Kortet er udløbet');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_COMMUNICATION_FAILURE', 'Kommunikationsfejl');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_INTERNAL_FAILURE', 'Intern fejl');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_CARD_NOT_REGISTERED', 'Kunden ikke oprettet i systemet');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_RETRY_FAILURE', 'Kan ikke betale samme transaktion flere gange');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_UNKNOWN', 'Fejl i indtastede oplysninger');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_CANCELLED', 'Transaktionen blev afbrudt') ;

// Name of credit cards options
define('MODULE_PAYMENT_UNZER_ADVANCED_CREDITCARD_TEXT', 'Kreditkort');
define('MODULE_PAYMENT_UNZER_ADVANCED_CREDITCARD_DESCRIPTION', 'Betal med Visa, Mastercard eller Maestro kort');
define('MODULE_PAYMENT_UNZER_ADVANCED_DIRECT_INVOICE_TEXT', 'Unzer Invoice');
define('MODULE_PAYMENT_UNZER_ADVANCED_DIRECT_INVOICE_DESCRIPTION', 'Betal 14 dage efter levering');
define('MODULE_PAYMENT_UNZER_ADVANCED_DIRECT_DEBIT_TEXT', 'Unzer Direct Debit');
define('MODULE_PAYMENT_UNZER_ADVANCED_DIRECT_DEBIT_DESCRIPTION', 'Betal direkte fra din bankkonto');
define('MODULE_PAYMENT_UNZER_ADVANCED_GOOGLE_PAY_TEXT', 'Google Pay');
define('MODULE_PAYMENT_UNZER_ADVANCED_GOOGLE_PAY_DESCRIPTION', 'Betal med Google Pay');
define('MODULE_PAYMENT_UNZER_ADVANCED_APPLE_PAY_TEXT', 'Apple Pay');
define('MODULE_PAYMENT_UNZER_ADVANCED_APPLE_PAY_DESCRIPTION', 'Betal med din Apple-enhed');
define('MODULE_PAYMENT_UNZER_ADVANCED_SOFORT_TEXT', 'SOFORT');
define('MODULE_PAYMENT_UNZER_ADVANCED_SOFORT_DESCRIPTION', 'Betal med SOFORT Banking');
define('MODULE_PAYMENT_UNZER_ADVANCED_PAYPAL_TEXT', 'Paypal');
define('MODULE_PAYMENT_UNZER_ADVANCED_PAYPAL_DESCRIPTION', 'Betal med PayPal');
define('MODULE_PAYMENT_UNZER_ADVANCED_KLARNA_TEXT', 'Klarna');
define('MODULE_PAYMENT_UNZER_ADVANCED_KLARNA_DESCRIPTION', 'Betal med Klarna Invoice, Klarna Direct Debit eller Klarna rater');
?>
