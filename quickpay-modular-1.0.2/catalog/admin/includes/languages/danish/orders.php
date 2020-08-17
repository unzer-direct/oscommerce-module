<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2002 osCommerce

  Released under the GNU General Public License
*/

define('HEADING_TITLE', 'Bestillinger');
define('HEADING_TITLE_SEARCH', 'BestillingsID:');
define('HEADING_TITLE_STATUS', 'Status:');

define('TAB_TITLE_SUMMARY','Overblik');
define('TAB_TITLE_PRODUCTS','Produkter');
define('TAB_TITLE_STATUS_HISTORY','Status Historik');

define('TABLE_HEADING_COMMENTS', 'Kommentar');
define('TABLE_HEADING_CUSTOMERS', 'Kunder');
define('TABLE_HEADING_ORDER_TOTAL', 'Bestilling Total');
define('TABLE_HEADING_DATE_PURCHASED', 'Købsdato');
define('TABLE_HEADING_STATUS', 'Status');
define('TABLE_HEADING_ACTION', 'Handling');
define('TABLE_HEADING_QUANTITY', 'Antal');
define('TABLE_HEADING_PRODUCTS_MODEL', 'Model');
define('TABLE_HEADING_PRODUCTS', 'Varer');
define('TABLE_HEADING_TAX', 'Moms');
define('TABLE_HEADING_TOTAL', 'Total');
define('TABLE_HEADING_PRICE_EXCLUDING_TAX', 'Pris (excl)');
define('TABLE_HEADING_PRICE_INCLUDING_TAX', 'Pris (inkl)');
define('TABLE_HEADING_TOTAL_EXCLUDING_TAX', 'Total (excl)');
define('TABLE_HEADING_TOTAL_INCLUDING_TAX', 'Total (inkl)');

define('TABLE_HEADING_CUSTOMER_NOTIFIED', 'Kunde underrettet');
define('TABLE_HEADING_DATE_ADDED', 'Tilføjet den:');

define('ENTRY_CUSTOMER', 'Kunde:');
define('ENTRY_SOLD_TO', 'SOLGT TIL:');
define('ENTRY_DELIVERY_TO', 'Leveres til:');
define('ENTRY_SHIP_TO', 'SEND TIL:');
define('ENTRY_SHIPPING_ADDRESS', 'Leveringsadresse:');
define('ENTRY_BILLING_ADDRESS', 'Faktureringsadresse:');
define('ENTRY_PAYMENT_METHOD', 'Betalingsmåde:');
define('ENTRY_CREDIT_CARD_TYPE', 'Betalingskorttype:');
define('ENTRY_CREDIT_CARD_OWNER', 'Betalingskortejer:');
define('ENTRY_CREDIT_CARD_NUMBER', 'Betalingskortnummer:');
define('ENTRY_CREDIT_CARD_EXPIRES', 'Betalingskort udløber den:');
define('ENTRY_SUB_TOTAL', 'Subtotal:');
define('ENTRY_TAX', 'Moms:');
define('ENTRY_SHIPPING', 'Levering:');
define('ENTRY_TOTAL', 'Total:');
define('ENTRY_DATE_PURCHASED', 'Købt den:');
define('ENTRY_ADD_COMMENT', 'Tilføj en Kommentar:');
define('ENTRY_STATUS', 'Status:');
define('ENTRY_DATE_LAST_UPDATED', 'Sidst opdateret:');
define('ENTRY_NOTIFY_CUSTOMER', 'Underret Kunde:');
define('ENTRY_NOTIFY_COMMENTS', 'Tilføj Kommentar:');
define('ENTRY_PRINTABLE', 'Udskriv Faktura');

define('TEXT_INFO_HEADING_DELETE_ORDER', 'Slet Bestilling');
define('TEXT_INFO_DELETE_INTRO', 'Er du sikker på at du vil slette denne bestilling?');
define('TEXT_INFO_RESTOCK_PRODUCT_QUANTITY', 'Genetabler varebeholdning');
define('TEXT_DATE_ORDER_CREATED', 'Oprettet den:');
define('TEXT_DATE_ORDER_LAST_MODIFIED', 'Sidst Ændret:');
define('TEXT_INFO_PAYMENT_METHOD', 'Betalingsmåde:');

define('TEXT_ALL_ORDERS', 'Alle Bestillinger');
define('TEXT_NO_ORDER_HISTORY', 'Ingen bestillingshistorik tilgængelig');

define('EMAIL_SEPARATOR', '------------------------------------------------------');
define('EMAIL_TEXT_SUBJECT', 'Bestillingsopdatering');
define('EMAIL_TEXT_ORDER_NUMBER', 'Bestillingsnummer:');
define('EMAIL_TEXT_INVOICE_URL', 'Detaljeret Faktura:');
define('EMAIL_TEXT_DATE_ORDERED', 'Ordredato:');
define('EMAIL_TEXT_STATUS_UPDATE', 'Din bestilling er blevet opdateret til føgende status' . "\n\n" . 'Ny status: %s' . "\n\n" . 'Ring venligst, hvis du har nogen spørgsmål.' . "\n");
define('EMAIL_TEXT_COMMENTS_UPDATE', 'Bemærkningerne til din bestilling er' . "\n\n%s\n\n");

define('ERROR_ORDER_DOES_NOT_EXIST', 'Fejl: Bestilling findes ikke.');
define('SUCCESS_ORDER_UPDATED', 'Success: Bestillingen er nu opdateret.');
define('WARNING_ORDER_NOT_UPDATED', 'Advarsel: Ingen ændringer. Bestillingen blev ikke opdateret.');
// QuickPay added start
define('ENTRY_QUICKPAY_TRANSACTION', 'QuickPay transaktion:');
define('ENTRY_QUICKPAY_CARDHASH', 'Type:');
define('IMAGE_TRANSACTION_CAPTURE_INFO', 'Gennemfør betaling');
define('IMAGE_TRANSACTION_REVERSE_INFO', 'Annulér betaling');
define('IMAGE_TRANSACTION_CREDIT_INFO', 'Krediter betaling');
define('IMAGE_TRANSACTION_TIME_INFO_GREEN', 'Kan stadig hæves inden for PBS-garanteret periode');
define('IMAGE_TRANSACTION_TIME_INFO_YELLOW', 'Sidste dag for PBS-garanteret hævning');
define('IMAGE_TRANSACTION_TIME_INFO_RED', 'Sidste dag for PBS-garanteret hævning er overskredet');
define('INFO_QUICKPAY_CAPTURED', 'Betalingen er gennemført');
define('INFO_QUICKPAY_CREDITED', 'Beløbet er krediteret');
define('INFO_QUICKPAY_REVERSED', 'Betalingen er annulleret');
define('ENTRY_QUICKPAY_TRANSACTION_ID', 'Transaktions-id:');
define('CONFIRM_REVERSE', 'Vil du annullere denne betaling?');
define('CONFIRM_CAPTURE', 'Advarsel: Transaktionsbeløb er ikke identisk med ordrens total. Vil du gennemføre betalingen?');
define('CONFIRM_CREDIT', 'Vil du kreditere kunden dette beløb?');
define('PENDING_STATUS', 'Afventer indløser godkendelse.');
define('PAYMENTLINK_INFO', 'Send dette link til kunde ved transaktionsproblem.');
define('SUBSCRIPTION_ADMIN', 'Administration af abonnement betaling samt gentagne abonnementsbetalinger er ikke implementeret i denne gratis distribuerede version.<br>Brug Quickpay manager til administration af abonnementsbetalinger. Venligst kontakt <a href="mailto:info@blkom.dk" ><b>udvikler</b></a> for implementering af gentagne abonnementsbetalinger og abonnementsadministration <br>');
define('ENTRY_QUICKPAY_STATUS','Gateway status');
// QuickPay added end

?>
