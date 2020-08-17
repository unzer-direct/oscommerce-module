# quickpay-osc-module
Quickpay payment module for osCommerce
Modular package.

Version 1.0.1 - October 2017

Compatibility:
Quickpay API v10 with
- osCommerce 2.3.4 and 2.3.4.1
- osCommerce 2.3.4 BS (Community Responsive project, Gold and Edge versions)

Can be installed without code changes on a store prepared for the Paypal App

Built from an existing payment module by BLKOM https://github.com/loevendahl/quickpay10-oscommerce
Additional Danish translations, testing and improvements by Bo Herrmannsen @boelle
Version 1.0 sponsored by Quickpay.net

Support thread on osCommerce forums:
https://forums.oscommerce.com/topic/412146-quickpay-payment-module-for-23/

Changelog
1.0.1 Two files updated for minor compatibility issues. Symptoms:
      on databases set up by a previous addon version, all orders were treated as if quickpay leading to Warning: array_reverse expects parameter 1 to be an array
	  link in confirmation email sent customer to FILENAME_ACCOUNT_HISTORY_INFO