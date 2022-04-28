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

define('TAB_UNZER_TRANSACTION','Unzer Transaktion [%s]');
define('UNZER_AUTHORISED','autorisiert');
define('UNZER_CAPTURED','gefangen');
define('UNZER_PART_CAPTURED','Teil erfasst');
define('UNZER_REFUNDED','refunded');
define('UNZER_PART_REFUNDED','Teil erstattet');
define('UNZER_REVERSED','umgekehrt');
define('UNZER_ERROR','ERROR %s');
define('ENTRY_UNZER_TRANSACTION_TYPE','umgekehrt');
define('ENTRY_UNZER_PAYMENT_LINK','umgekehrt');
define('ENTRY_UNZER_TRANSACTION', 'Unzer Saldo:');
define('ENTRY_UNZER_CARDHASH', 'Transaktionstyp:');
define('IMAGE_TRANSACTION_CAPTURE_INFO', 'Transaktion erfassen');
define('IMAGE_TRANSACTION_REVERSE_INFO', 'Zahlung stornieren');
define('IMAGE_TRANSACTION_CREDIT_INFO', 'Kreditzahlung');
define('IMAGE_TRANSACTION_TIME_INFO_GREEN', 'Erfassung innerhalb des PBS-Garantiezeitraums möglich');
define('IMAGE_TRANSACTION_TIME_INFO_YELLOW', 'Letzter Tag der PBS-garantierten Erfassung');
define('IMAGE_TRANSACTION_TIME_INFO_RED', 'Letzter Tag der PBS-garantierten Erfassung bestanden');
define('INFO_UNZER_CAPTURED', 'Zahlung wird erfasst');
define('INFO_UNZER_CREDITED', 'Betrag wird gutgeschrieben');
define('INFO_UNZER_REVERSED', 'Zahlung wurde storniert');
define('ENTRY_UNZER_TRANSACTION_ID', 'Transaktion-id:');
define('CONFIRM_REVERSE', 'Möchten Sie diese Zahlung stornieren?');
define('CONFIRM_CAPTURE', 'Warnung: Der Transaktionsbetrag ist nicht identisch mit dem Bestellbetrag. Möchten Sie die Zahlung erfassen?');
define('CONFIRM_CREDIT', 'Wollen Sie dem Kunden diesen Betrag gutschreiben?');
define('PENDING_STATUS', 'Warten auf Genehmigung des Käufers.');
define('PAYMENTLINK_INFO', 'Diesen Link bei Transaktionsproblemen an den Kunden senden.');
define('SUBSCRIPTION_ADMIN' , 'Abonnementzahlung und wiederkehrende Abonnementzahlung sind in dieser kostenlosen Basisversion nicht implementiert.<br>Verwenden Sie den Unzer-Manager für die Verwaltung. Bitte wenden Sie sich an <a href="mailto:info@blkom.dk" ><b >Entwickler</b></a> für die Implementierung der erweiterten Abonnementversion<br>');
define('ENTRY_UNZER_STATUS','Gateway-Status');
if (!defined('ENTRY_ADD_COMMENT')) define('ENTRY_ADD_COMMENT','Kommentar:');
