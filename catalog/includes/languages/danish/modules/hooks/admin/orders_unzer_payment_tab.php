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
define('UNZER_AUTHORISED','godkendt');
define('UNZER_CAPTURED','gennemført');
define('UNZER_PART_CAPTURED','delvis gennemført');
define('UNZER_REFUNDED','krediteret');
define('UNZER_PART_REFUNDED','delvis krediteret');
define('UNZER_REVERSED','annulleret');
define('UNZER_ERROR','FEJL %s');
define('ENTRY_UNZER_TRANSACTION_TYPE','Type');
define('ENTRY_UNZER_PAYMENT_LINK','Link');
define('ENTRY_UNZER_TRANSACTION', 'Unzer transaktion:');
define('ENTRY_UNZER_CARDHASH', 'Type:');
define('IMAGE_TRANSACTION_CAPTURE_INFO', 'Gennemfør betaling');
define('IMAGE_TRANSACTION_REVERSE_INFO', 'Annulér betaling');
define('IMAGE_TRANSACTION_CREDIT_INFO', 'Krediter betaling');
define('IMAGE_TRANSACTION_TIME_INFO_GREEN', 'Kan stadig hæves inden for PBS-garanteret periode');
define('IMAGE_TRANSACTION_TIME_INFO_YELLOW', 'Sidste dag for PBS-garanteret hævning');
define('IMAGE_TRANSACTION_TIME_INFO_RED', 'Sidste dag for PBS-garanteret hævning er overskredet');
define('INFO_UNZER_CAPTURED', 'Betalingen er gennemført');
define('INFO_UNZER_CREDITED', 'Beløbet er krediteret');
define('INFO_UNZER_REVERSED', 'Betalingen er annulleret');
define('ENTRY_UNZER_TRANSACTION_ID', 'Transaktions-id:');
define('CONFIRM_REVERSE', 'Vil du annullere denne betaling?');
define('CONFIRM_CAPTURE', 'Advarsel: Transaktionsbeløb er ikke identisk med ordrens total. Vil du gennemføre betalingen?');
define('CONFIRM_CREDIT', 'Vil du kreditere kunden dette beløb?');
define('PENDING_STATUS', 'Afventer indløser godkendelse.');
define('PAYMENTLINK_INFO', 'Send dette link til kunde ved transaktionsproblem.');
define('SUBSCRIPTION_ADMIN'	, 'Administration af abonnement betaling samt gentagne abonnementsbetalinger er ikke implementeret i denne gratis distribuerede version.<br>Brug Unzer manager til administration af abonnementsbetalinger. Venligst kontakt <a href="mailto:info@blkom.dk" ><b>udvikler</b></a> for implementering af gentagne abonnementsbetalinger og abonnementsadministration <br>');
define('ENTRY_UNZER_STATUS','Gateway status');
if (!defined('ENTRY_ADD_COMMENT')) define('ENTRY_ADD_COMMENT','Kommentar:');
