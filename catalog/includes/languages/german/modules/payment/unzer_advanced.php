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

define('MODULE_PAYMENT_UNZER_ADVANCED_TEXT_TITLE', 'Unzer: Online-Zahlung');
define('MODULE_PAYMENT_UNZER_ADVANCED_TEXT_PUBLIC_TITLE', 'Unzer: Online-Zahlung');
define('MODULE_PAYMENT_UNZER_ADVANCED_TEXT_DESCRIPTION', 'Unzer Advanced Online Payment');
define('MODULE_PAYMENT_UNZER_ADVANCED_TEXT_EMAIL_FOOTER', 'Zahlung ist jetzt reserviert. Die Zahlung hat folgende Transaktionsnummer: %s.' . "\n" . 'Wenn die Bestellung bearbeitet wird, wird der Betrag an ' . STORE_NAME);
define('MODULE_PAYMENT_UNZER_ADVANCED_TEXT_SELECT_CARD', '* Wählen Sie aus, welche Zahlungsart Sie für Ihre Online-Zahlung verwenden möchten\n');
define('MODULE_PAYMENT_UNZER_ADVANCED_TEXT_WAIT', 'Bitte warten Sie einen Moment. Zahlungsseite wird vorbereitet...');
define('MODULE_PAYMENT_UNZER_ADVANCED_TEXT_ERROR', 'Online-Zahlung kann nicht verarbeitet werden');
define('MODULE_PAYMENT_UNZER_ADVANCED_FEEINFO', '(Gebühr wird im Zahlungsfenster hinzugefügt)');
define('MODULE_PAYMENT_UNZER_ADVANCED_FEELOCKINFO', 'Gebühr');
define('KÜNDIGUNG', 'Die Bestellung ist mit ViaBill bezahlt. Der geschuldete Betrag kann nur mit befreiender Wirkung an ViaBill gezahlt werden, die ein separates Inkasso sendet. Eine Zahlung kann nicht durch Verrechnung mit Forderungen aus anderen Rechtssachen erfolgen.');

if (!defined('HEADING_RETURN_POLICY')) define('HEADING_RETURN_POLICY','Zusammenfassung der Rückgaberichtlinien');
if (!defined('TEXT_VIEW')) define('TEXT_VIEW','Details anzeigen');
if (!defined('TEXT_RETURN_POLICY')) define('TEXT_RETURN_POLICY','Zusammenfassung der Rückgabebedingungen');
if (!defined('ACCEPT_CONDITIONS')) define('ACCEPT_CONDITIONS','Ich habe die Nutzungsbedingungen gelesen und stimme ihnen zu: ');
if (!defined('CONDITION_AGREEMENT_ERROR')) define('CONDITION_AGREEMENT_ERROR','Bitte stimmen Sie unseren Allgemeinen Geschäftsbedingungen zu, bevor Sie Ihre Bestellung aufgeben.');
/* Transaction errors */
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_MERCHANT_UNKNOWN', 'Unbekannte Händlernummer');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_CARDNO_NOT_VALID', 'Ungültige Kartennummer');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_CVC_NOT_VALID', 'Ungültige Kontrollziffern');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_ORDERID', 'Ungültige oder fehlende OrderID');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_TRANSACTION_DECLINED', 'Transaktion wurde nicht genehmigt');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_WRONG_NUMBER_FORMAT', 'Ungültiges Betragsformat');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_ILLEGAL_TRANSACTION', 'Ungültige Transaktion');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_TRANSACTION_EXPIRED', 'Transaktionszeitüberschreitung');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_NO_ANSWER', 'Keine Antwort');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_SYSTEM_FAILURE', 'Systemfehler');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_CARD_EXPIRED', 'Karte ist abgelaufen');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_COMMUNICATION_FAILURE', 'Kommunikationsfehler');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_INTERNAL_FAILURE', 'Interner Fehler');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_CARD_NOT_REGISTERED', 'Nicht im System');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_RETRY_FAILURE', 'Transaktion kann nicht zweimal verarbeitet werden');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_UNKNOWN', 'Fehler im eingegebenen Text');
define('MODULE_PAYMENT_UNZER_ADVANCED_ERROR_CANCELLED', 'Transaktion wurde abgebrochen') ;

/* Name of credit cards options & description*/
define('MODULE_PAYMENT_UNZER_ADVANCED_CREDITCARD_TEXT', 'Kreditkarte');
define('MODULE_PAYMENT_UNZER_ADVANCED_CREDITCARD_DESCRIPTION', 'Bezahlen mit Visa, Mastercard oder Maestro-Karte');
define('MODULE_PAYMENT_UNZER_ADVANCED_DIRECT_INVOICE_TEXT', 'Unzer Rechnungskauf');
define('MODULE_PAYMENT_UNZER_ADVANCED_DIRECT_INVOICE_DESCRIPTION', 'Zahlung 14 Tage nach Lieferung');
define('MODULE_PAYMENT_UNZER_ADVANCED_DIRECT_DEBIT_TEXT', 'Unzer Lastschrift');
define('MODULE_PAYMENT_UNZER_ADVANCED_DIRECT_DEBIT_DESCRIPTION', 'Zahlen Sie direkt von Ihrem Bankkonto');
define('MODULE_PAYMENT_UNZER_ADVANCED_GOOGLE_PAY_TEXT', 'Google Pay');
define('MODULE_PAYMENT_UNZER_ADVANCED_GOOGLE_PAY_DESCRIPTION', 'Bezahlen Sie mit Google Pay');
define('MODULE_PAYMENT_UNZER_ADVANCED_APPLE_PAY_TEXT', 'Apple Pay');
define('MODULE_PAYMENT_UNZER_ADVANCED_APPLE_PAY_DESCRIPTION', 'Bezahlen mit Ihrem Apple Gerät');
define('MODULE_PAYMENT_UNZER_ADVANCED_SOFORT_TEXT', 'SOFORT');
define('MODULE_PAYMENT_UNZER_ADVANCED_SOFORT_DESCRIPTION', 'Mit SOFORT Banking bezahlen');
define('MODULE_PAYMENT_UNZER_ADVANCED_PAYPAL_TEXT', 'Paypal');
define('MODULE_PAYMENT_UNZER_ADVANCED_PAYPAL_DESCRIPTION', 'Bezahlen Sie mit PayPal');
define('MODULE_PAYMENT_UNZER_ADVANCED_KLARNA_TEXT', 'Klarna');
define('MODULE_PAYMENT_UNZER_ADVANCED_KLARNA_DESCRIPTION', 'Bezahlen mit Klarna Rechnung, Klarna Lastschrift oder Klarna Ratenzahlung');

/* Admin pannel > creditcard logos configuration */
define('MODULE_PAYMENT_UNZER_CARD_LOGOS_SHOWN_CARDS', 'Gezeigte Logos');
define('MODULE_PAYMENT_UNZER_CARD_LOGOS_NEW_CARDS', 'Verfügbare Logos');
define('MODULE_PAYMENT_UNZER_CARD_LOGOS_DRAG_HERE', 'Hier ziehen');
?>
