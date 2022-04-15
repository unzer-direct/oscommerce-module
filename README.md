# unzer-oscommerce-module
Unzer payment module for osCommerce
Modular package.

Version 1.0.8 - 13.04.2022

Compatibility:
Unzer API v10 with
- osCommerce 2.3.4 and 2.3.4.1
- osCommerce 2.3.4 BS (Community Responsive project, Gold and Edge versions)

Can be installed without code changes on a store prepared for the Paypal App

## Installation guide & steps can be found into `docs` folder
#
Built from an existing payment module by BLKOM https://github.com/loevendahl/unzer10-oscommerce
Additional Danish translations, testing and improvements by Bo Herrmannsen @boelle
Version 1.0 sponsored by Unzerdirect.com

Support thread on osCommerce forums:
https://forums.oscommerce.com/topic/412146-unzer-payment-module-for-23/

Changelog
#### 1.0.8
- Added conditions for Unzer Direct Invoice to show in frontend
#### 1.0.7
- Added Sofort payment method
#### 1.0.6
- Added Unzer Direct Invoice payment method
- fix payment (_payment) logos height
#### 1.0.5
- Added Apple Pay & Google pay payment methods
#### 1.0.4
- Added all payment request fields in accordance to the documentation.
- Removed custom variables from payment request.
#### 1.0.3
- Added possibility to configure the text displayed for the payment options.
#### 1.0.2
- Indented all code to ease future development.
- Fixed not defined variable warnings:
  * Warning: Use of undefined constant MODULE_PAYMENT_UNZER_ZONE
  * Warning: Use of undefined constant MODULE_PAYMENT_UNZER_ADVANCED_APIKEY
- Added all unzer payment options logos.
- Added translations for missing payment options.
- Made orders visible in "My Account" section.
- Fixed clearing of selected unzer payment option when differrent payment option is selected.
#### 1.0.1
- Two files updated for minor compatibility issues. Symptoms:
   * on databases set up by a previous addon version, all orders were treated as if unzer leading to Warning: array_reverse expects parameter 1 to be an array
  * link in confirmation email sent customer to FILENAME_ACCOUNT_HISTORY_INFO
