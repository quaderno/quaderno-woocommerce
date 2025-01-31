=== WooCommerce Quaderno – Tax Automation ===
Author URI: https://quaderno.io/integrations/woocommerce/?utm_source=wordpress&utm_campaign=woocommerce
Contributors: polimorfico
Tags: woocommerce, tax, taxes, sales tax, vat, gst, vatmoss, vat moss, vat oss, billing, invoices, receipts, credit notes
Requires at least: 4.6
Tested up to: 6.7
Stable tag: 2.4.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Automatically calculate tax rates & create instant tax reports for your WooCommerce store. Setup in less than 5 minutes.

== Description ==

The Quaderno plugin for WooCommerce simplifies your business by automatically calculating tax on every sale and following up with tax-compliant receipts and credit notes, no matter where your customer is located. Transactions and invoices processed through Quaderno are compliant with ever-changing local [tax rules for WooCommerce sellers](https://www.quaderno.io/digital-tax-guides/woocommerce-sales-tax-guide/?utm_source=wordpress&utm_campaign=woocommerce), including US sales tax, EU VAT, and Canadian GST.

https://www.youtube.com/watch?v=NyWw4Dye2ag

= What you get =

* **Comply with local tax laws** in countries around the world.
* **Get notified any time you become liable for taxes** by surpassing a tax registration or [US economic nexus threshold](https://www.quaderno.io/digital-tax-guides/us-economic-nexus-guide/?utm_source=wordpress&utm_campaign=woocommerce), or when a tax rate changes anywhere you sell your products or services. You’ll always know [when to charge sales tax](https://www.quaderno.io/blog/do-i-need-to-collect-sales-tax-for-selling-online/?utm_source=wordpress&utm_campaign=woocommerce), and Quaderno does that for you!
* **Automatic tax calculation on every transaction**. Our database identifies the correct tax rate and amount based on your product and the customer's location.
* **Get all the information you need for your tax returns** at a glance, with Quaderno’s instant tax reports.
* **Automatic receipts and credit notes** sent for every order in your store, in **multiple languages and currencies**.
* Let your customers **download receipts and credit notes** directly from your WooCommerce orders page. Hands-free customer service!
* **Manage all your revenue sources** and other business data in one easy-to-use dashboard.

**Setup in less than 5 minutes**. Quick and easy!

1. Download & activate this plugin
2. [Sign up](https://quadernoapp.com/signup/?utm_source=wordpress&utm_campaign=woocommerce) for a Quaderno account
3. Paste your API key in your WooCommerce site
4. That's all!

**Note:** this plugin requires a [Quaderno](https://quadernoapp.com/signup/?utm_source=wordpress&utm_campaign=woocommerce) account.

== Installation ==

Follow these steps to install the WooCommerce Quaderno - Tax Automation plugin.

1. Unpack the entire contents of this plugin zip file to your wp-content/plugins/ folder locally.
2. Upload to your site.
3. Navigate to wp-admin/plugins.php on your site (your WP Admin plugin page).
4. Activate this plugin.
5. Configure the options from WooCommerce > Settings > Integration.

OR you can just install it with WordPress by going to Plugins > Add New > and typing this plugin’s name.

That’s it! You can now customize your WooCommerce receipts and stay compliant with US sales tax, EU VAT, and other consumption taxes around the world.

Check out our [WooCommerce Quaderno support documentation](https://support.quaderno.io/category/465-woocommerce/?utm_source=wordpress&utm_campaign=woocommerce) for more information.

== Frequently Asked Questions ==

= How do I add tax to my WooCommerce sales with Quaderno? =
Our plugin will automatically do this for you! Once the [integration with WooCommerce](https://support.quaderno.io/article/500-connecting-woocommerce/?utm_source=wordpress&utm_campaign=woocommerce) is complete, you’ll see the correct tax added to each transaction directly in the checkout process.

= Can I integrate other e-commerce platforms with Quaderno? =
Sure, if you sell on Amazon FBA or Shopify, you can connect those platforms to your Quaderno account as well. This way you can have all your sales and taxes in one dashboard.

= What pricing plans are available? =
We offer simple, transaction-based pricing that scales with your business. [Quaderno plans](https://www.quaderno.io/pricing/?utm_source=wordpress&utm_campaign=woocommerce) start at $29 per month.

== Screenshots ==

1. Copy your API token and API URL from your Quaderno account
2. Paste it on the Quaderno settings page
3. Calculate taxes on the fly if you sell digital goods to EU customers
4. Example of an invoice

== Changelog ==

= 2.4.0 – January 31, 2025 =
* New: do not cache tax rate if the validation service is down
* New: Support for WooCommerce 9.6

= 2.3.5 – January 8, 2025 =
* New: use the function get_order_number instead of using the order ID

= 2.3.4 – December 19, 2024 =
* New: prioritize the quaderno_invoices_skip filter

= 2.3.3 – December 15, 2024 =
* Fix: error in caching tax ID validations

= 2.3.2 – December 2, 2024 =
* Fix: error in validating some tax IDs

= 2.3.1 – November 14, 2024 =
* New: Support for WordPress 6.7

= 2.3.0 – October 25, 2024 =
* New: show the customer tax ID on the order email
* New: show the customer tax ID on the order details page

= 2.2.8 – October 21, 2024 =
* Fix: businesses must pay local taxes for standard services

= 2.2.7 – July 17, 2024 =
* New: Support for WordPress 6.6

= 2.2.6 – July 9, 2024 =
* New: Show incompatibility with Cart and Checkout Blocks

= 2.2.5 – May 14, 2024 =
* New: Hide the Tax ID field when the customer selected Turkey

= 2.2.3 – April 2, 2024 =
* New: Support for WordPress 6.5

= 2.2.2 – February 19, 2024 =
* Fix: The item tax class is not used for the shipping tax class when is needed

= 2.2.1 – December 28, 2023 =
* Fix: Tax ID is marked as mandatory in checkout form when the related required option is selected in Quaderno settings
* New: Show the "subscriptions update" option only when the WooCommmerce Subscriptions plugin is active
* New: Show all the standard tax rates in the Quaderno status page

= 2.2.0 – August 25, 2023 =
* New: compatibility with HPOS

= 2.1.24 – August 15, 2023 =
* Fix: compatibility with FOX – Currency Switcher Professional for WooCommerce (formely WOOCS)

= 2.1.23 – August 9, 2023 =
* New: Support for WordPress 6.3

= 2.1.22 – Jul 31, 2023 =
* Fix: use standard payment processors

= 2.1.21 – June 27, 2023 =
* Fix: region is not send to Quaderno when WooCommerce tax calculator is used

= 2.1.20 – April 16, 2023 =
* Fix: minor error in tax calculations on the cart page
* New: invoices are not shown in the order detail page if the autosend option is not selected

= 2.1.19 – March 30, 2023 =
* New: Support for WordPress 6.2

= 2.1.18 – March 23, 2023 =
* Update: Show taxes in cart only if tax calculations are enabled
* Update: Use another filter to show taxes in cart

= 2.1.17 – March 20, 2023 =
* Fix: warning in cart page when tax_id parameter not set

= 2.1.16 – March 15, 2023 =
* New: we now calculate taxes in the shopping cart

= 2.1.15 – January 30, 2023 =
* New: Support FunnelKit payment method

= 2.1.14 – November 2, 2022 =
* New: Support for WordPress 6.0

= 2.1.13 – October 10, 2022 =
* Fix: problem when the order state has not been set

= 2.1.12 – October 4, 2022 =
* Fix: tags of product variants are not sent to Quaderno

= 2.1.11 – September 22, 2022 =
* Update: readme content

= 2.1.10 – August 31, 2022 =
* Fix: use the variation SKU if exists

= 2.1.9 – August 31, 2022 =
* Fix: we cannot recalculate subtotal for shipping item in subscriptions

= 2.1.8 – July 11, 2022 =
* Fix: tax ID is not saved in programatically created orders

= 2.1.7 – June 8, 2022 =
* Fix: ignore reverse-charge subscriptions in renewal
* Fix: error in subscriptions renewals with multiple items
* New: add subscription renewal option to status page

= 2.1.6 – May 25, 2022 =
* New: identify new payment methods
* New: Support for WordPress 6.0

= 2.1.5 – May 2, 2022 =
* New: recalculate taxes for existent subscriptions if needed

= 2.1.4 – April 19, 2022 =
* New: do not cache tax calculations when Quaderno tax calculator fails

= 2.1.3 – April 12, 2022 =
* Fix: calculate legacy tax class at variation level

= 2.1.2 – April 1, 2022 =
* New: fallback when Quaderno tax calculator fails
* Fix: remove call to old function

= 2.1.1 – March 14, 2022 =
* Fix: some taxes are separated on final invoices

= 2.1.0 – March 5, 2022 =
* Fix: Issue in tax calculation for product variants
* New: Tools to delete tax cache and Quaderno data

= 2.0.0 – February 21, 2022 =
* New: Products can be assigned to a particular Quaderno tax code
* New: Support for WooCommerce 6.2

= 1.23.12 – January 27, 2021 =
* New: Support for WordPress 5.9

= 1.23.11 – December 23, 2021 =
* Fix: New WooCommerce PayPal plugin introduced a new payment method

= 1.23.10 – December 20, 2021 =
* Improvement: unknow products are classified as goods
* New: Support for WooCommerce 6.0

= 1.23.9 – November 14, 2021 =
* Improvement: delete tax ids validations from cache
* Improvement: do not cache tax ids when validation service is down

= 1.23.8 – August 30, 2021 =
* Improvement: send shipping address to Quaderno only if it exists
* New: Support for WooCommerce 5.6
* New: Support for WordPress 5.8

= 1.23.7 – April 26, 2021 =
* New: Hooks to customize item descriptions, payment methods, tax rates, and tax locations.

= 1.23.6 – April 22, 2021 =
* New: Hooks to customize PO number and notes fields

= 1.23.5 – April 15, 2021 =
* New: Support for Braintree

= 1.23.4 – April 7, 2021 =
* Fix: Create invoices with 0% tax rate when there are no taxes
* Fix: Cannot deliver receipts

= 1.23.3 – March 22, 2021 =
* New: Track new payment methods

= 1.23.2 – March 20, 2021 =
* New: Use the customer processor data only if the customer is registered
* New: Add Quaderno status page to improve our customer support
* New: Use the new Tax API to validate tax ids and calculate tax rates

= 1.23.1 – March 18, 2021 =
* Fix: Problem with evidence creation

= 1.23.0 – March 17, 2021 =
* New: Add new hook to skip invoices and credit notes 
* New: Use new Quaderno Transactions API

= 1.22.5 – March 11, 2021 =
* New: Support for WooCommerce 5.1
* New: Support for WordPress 5.7

= 1.22.4 – February 17, 2021 =
* Fix: Error in tax calculations for custom tax classes

= 1.22.3 – February 10, 20201 =
* Fix: Quaderno is creating invoice for $0 orders

= 1.22.2 – February 9, 2021 =
* New: Show variation description on invoices

= 1.22.1 – February 4, 2021 =
* Fix: Location evidence are not stored in Quaderno

= 1.22.0 – December 21, 2020 =
* New: Support for new UK VAT after Brexit, including VAT calculation for overseas goods sold to UK customers under £135

= 1.21.21 – December 9, 2020 =
* New: Support for WordPress 5.6

= 1.21.20 – December 3, 2020 =
* Fix: Do not calculate taxes for tax exempted products

= 1.21.19 – December 2, 2020 =
* Fix: Wrong tax class for subscription variations

= 1.21.18 – November 27, 2020 =
* Fix: Support subscriptions variations

= 1.21.17 – October 7, 2020 =
* New: WC tested up to 4.5.2

= 1.21.16 – October 6, 2020 =
* Fix: Error in shipping tax calculations

= 1.21.15 – September 28, 2020 =
* Fix: Remove tax from shipping costs if they're exempted

= 1.21.14 – September 20, 2020 =
* New: Option to clear tax cache
* New: Tax id can be required only in certain countries

= 1.21.13 – August 27, 2020 =
* Fix: Tax region is not sending when using custom taxes

= 1.21.12 – August 20, 2020 =
* Fix: Default location is not used in the cart

= 1.21.11 – August 11, 2020 =
* New: Support for WordPress 5.5
* New: Support for WooCommerce 4.3

= 1.21.10 – July 15, 2020 =
* New: Re-validate the customer's tax id if the completed order doesn't have the custom file "is_vat_exempted"

= 1.21.9 – June 16, 2020 =
* Fix: VAT numbers are validated when the field is empty

= 1.21.8 – June 12, 2020 =
* New: Support WooCommerce 4.2
* New: Support multiple stores with the same order numbers

= 1.21.7 – April 2, 2020 =
* New: Support WordPress 5.4

= 1.21.6 – March 30, 2020 =
* Improvement: Identify payments via PayPal Express

= 1.21.5 – March 19, 2020 =
* New: Allow users to remove actions from invoice, credit, and order managers.

= 1.21.4 – March 18, 2020 =
* New: Support WooCommerce 4.0

= 1.21.3 – March 2, 2020 =
* Improvement: Recurring customer can store their Tax ID in their billing details
* Improvement: Quaderno invoices are always opened in a new tab

= 1.21.2 – February 21, 2020 =
* Improvement: Better management of tax IDs
* Improvement: Add an order note when tax IDs cannot be validated
* Fix: issue credit note for receipts

= 1.21.1 – February 5, 2020 =
* Improvement: remove non-word characters from tax ID

= 1.21.0 – December 27, 2019 =
* New: option to force universal pricing

= 1.20.3 – December 10, 2019 =
* Update plugin description

= 1.20.2 – December 10, 2019 =
* Fix: tax calculation is wrong for product variations
* Fix: error in tax calculations for shipping in particular cases
* New: remove tax name when taxes are not applied

= 1.20.1 – December 10, 2019 =
* Remove: Support for secondary tax rates

= 1.20.0 – November 26, 2019 =
* New: Support for provincial sales tax in Canada
* New: WordPress 5.3 compatibility

= 1.19.0 - November 13, 2019 =
* New: Support WooCommerce 3.8
* New: Option to require tax ID in local sales

= 1.18.3 - October 21, 2019 =
* Fix: contact ID must updated

= 1.18.2 - October 18, 2019 =
* Fix: stored VAT numbers are not being used in recurring invoices

= 1.18.1 - October 17, 2019 =
* New: tax ID is always optional
* New: receipt threshold field is no longer necessary 

= 1.18.0 - October 16, 2019 =
* Improvement: create a new contact in Quaderno if the customer changes their name
* New: the checkout form supports any tax ID

= 1.17.7 - October 2, 2019 =
* Improvement: only use VAT number if exists

= 1.17.6 - September 25, 2019 =
* Improvement: use original name for fees
* Improvement: add receipts threshold

= 1.17.5 - September 23, 2019 =
* Improvement: always send customer billing address
* Improvement: user WordPress current_time function 

= 1.17.4 - August 27, 2019 =
* Fix: error in VAT validation for existent customers

= 1.17.3 - August 25, 2019 =
* Fix: reverse charge is not applied in tax inclusive receipts

= 1.17.2 - July 25, 2019 =
* New: require Tax ID only in particular countries

= 1.17.1 - July 24, 2019 =
* New: use city to calculate taxes

= 1.17.0 - July 19, 2019 =
* New: send shipping address to Quadernp
* New: send US tax codes to Quaderno

= 1.16.1 - June 26, 2019 =
* New: send payment ids to Quaderno

= 1.15.12 - April 12, 2019 =
* Fix: Invoices are not created when products do not exist

= 1.15.11 - April 5, 2019 =
* Fix: Don't validate VAT number when customer is based in shop's country

= 1.15.10 - March 18, 2019 =
* Fix: Stop Chrome to autocomplete VAT numbers

= 1.15.9 - March 8, 2019 =
* Improvement: Send products SKUs to Quaderno
* Improvement: Link refunds with invoices
* Improvement: Send order URL to Quaderno

= 1.15.8 - February 26, 2019 =
* Tested with WordPress 5.1

= 1.15.7 - January 8, 2019 =
* Improvement: simplify settings

= 1.15.6 - December 12, 2018 =
* Improvement: use a default name when customer's first name is not present

= 1.15.5 - November 15, 2018 =
* Improvement: delete transients when plugin is deactivated

= 1.15.4 - November 2, 2018 =
* Fix: VAT number is displayed when customer lives in the shop country

= 1.15.3 - October 24, 2018 =
* Improvement: Add reverse charge note when VAT number is present

= 1.15.2 - October 2, 2018 =
* Fix: Cannot mixed different tax classes in the same cart

= 1.15.1 - September 18, 2018 =
* Fix: Use billing state to calculate tax rates

= 1.15.0 - September 14, 2018 =
* Improvement: Use Quaderno tax calculator as fallback for non-digital products
* Improvement: Show region name on invoices
* Improvement: Send tax county, state and transaction type to Quaderno
* Improvement: Refactoring credit note creation

= 1.14.4 - September 7, 2018 =
* Fix: Problem in tax calculation for non-digital products

= 1.14.3 - August 30, 2018 =
* Fix: Discounts are not calculated right when using bundles 

= 1.14.2 - August 23, 2018 =
* Fix: Non-admin users can see the review request

= 1.14.1 - August 16, 2018 =
* Fix: Shipping based taxes are wrong

= 1.14.0 - August 8, 2018 =
* New: Calculate tax for non-digital products

= 1.13.1 - August 8, 2018 =
* Fix: customers are duplicated

= 1.13.0 - July 25, 2018 =
* New: allow custmers to download invoices from "my account"
* New: allow admins to view invoices from order details page

= 1.12.0 - July 8, 2018 =
* New: store Tax ID and VAT numbers in customers' profile

= 1.11.7 - July 3, 2018 =
* Fix: use table preffix to show the review notice
* New: translate review notice

= 1.11.6 - July 2, 2018 =
* New: use singleton pattern for WooCommerce_Quaderno

= 1.11.5 - July 2, 2018 =
* New: improve tax id validation

= 1.11.4 - June 11, 2018 =
* New: Support WooCommerce 3.4.2
* New: Ask for plugin review

= 1.11 =
* New: Use translations from wordpress.org
* New: Admins can show/hide the Tax ID field

= 1.10 =
* New: Support for WooCommerce Payment Gateway Based Fees
* New: Collect Tax ID for customers in Slovakia
* New: Use a new default payment method

= 1.9 =
* New: Valid VAT numbers during checkout
* New: Send receipts only to local customers
* New: Use the WC_Geolocation function
* Fix: Get shipping total
* Fix: Require Tax ID only when purchase overrides threshold
* New: Update Quaderno API version
* Fix: Error in shipping taxes
* New: Record billing phone
* Fix: Use tax incl prices for shipping costs
* New: Add discounts to invoices
* New: Show Tax ID as mandatory when necessary

= 1.8 =
* New: Collect Tax ID for customers in Spain, Belgium, Germany, and Italy
* New: Translations to German, Dutch, and French
* New: WordPress 4.7 compatibility
* New: Register shipping costs
* Fix: Unit price is not correct when ordering more than 1 product
* New: Manage tax rates for physical products and shipping
* New: Do not issue invoices for free orders
* Fix: Syntax error for PHP version under 5.4
* New: WordPress 4.8 compatibility
* New: Improve to geoplugin service
* Fix: Minor bugs

= 1.7 =
* New: Send sales receipts
* New: Track transaction ID

= 1.6 =
* Improvement: Invoices and credits generation
* New: Stop base taxes being taken off when dealing with out of base locations

= 1.5.2 =
* Fix: javascript was not updated

= 1.5.1 =
* Improvement: Hide VAT Number field when customer is based in the store country

= 1.5.0 =
* New: Validate EU VAT Numbers
* New: Compatibility with Sequential Order Numbers Pro
* New: Track different payment methods on Quaderno

= 1.4.2 =
* Fix: issue when postal code contains whitespaces

= 1.4.0 =
* Refactoring code
* Fix: issue in sending documents
* Improvement: Update descriptions

= 1.3.2 =
* Minor fixes to improve security

= 1.3.1 =
* Improvement: Use wp_remote_request instead of curl

= 1.3.0 =
* New: Send credit notes for WooCommerce refunds

= 1.2.3 =
* Fix: Unit price calculation

= 1.2.0 =
* New: Tag invoices from WooCommerce

= 1.1.1 =
* Fix: Typo and test on Wordpress 4.3

= 1.1.0 =
* New: Integration with WooCommerce Currency Switcher

= 1.0.1 =
* New: Show generic tax name when no taxes apply

= 1.0.0 =
* First version

== Translations ==

* English - default, always included
* Spanish: Español - siempre incluido
* German: Deutsch - translated by [Alex Gahr](https://germantakeaways.com)
* French: Français - translated by [Sébastien Jacobs](https://xando.pro)
* Dutch: Nederlands - translated by [Sébastien Jacobs](https://xando.pro)

*Note:* This plugin is fully localized. This is very important for all users worldwide. So please contribute your language to the plugin to make it even more useful. For translating we recommend the ["Poedit Editor"](http://www.poedit.net/).

