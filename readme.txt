=== WooCommerce Quaderno ===
Author URI: https://quaderno.io/integrations/woocommerce/?utm_source=wordpress&utm_campaign=woocommerce
Contributors: polimorfico
Tags: tax, taxes, sales tax, vat, gst, vatmoss, vat moss, billing, invoices, receipts, credit notes, woocommerce, quaderno
Requires at least: 4.6
Tested up to: 5.5
Stable tag: 1.21.18
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Automatically calculate tax rates & create instant tax reports for your WooCommerce store. Setup in less than 5 minutes.

== Description ==

Quaderno for WooCommerce takes taxes off your plate by automatically calculating tax on every sale and following up with beautiful receipts and credit notes, no matter where your customer is located. Transactions and invoices processed through Quaderno always comply with ever-changing local tax rules, including US sales tax, EU VAT, and Canadian GST.

https://www.youtube.com/watch?v=mGs6SVOr7fU

= What you get =

* **Comply with local tax laws** in countries around the world.
* **Get notified any time you surpass a tax threshold**, or when a tax rate changes anywhere you sell your products or services.
* **Get all the information you need for your tax returns**, at a glance, in mere seconds.
* **Automatic receipts and credit notes** with every order in your store, in **multiple languages and currencies**.
* Let your customers to **download receipts and credit notes** right from your WooCommerce's orders page.  
* **Manage all your revenue sources** in one easy-to-use dashboard.

**Setup in less than 5 minutes**. Fast and easy!

1. Download & activate this plugin
2. [Sign up](https://quaderno.io/integrations/woocommerce/?utm_source=wordpress&utm_campaign=woocommerce) for a Quaderno account
3. Paste your API key in your WooCommerce site
4. That's all!

**Note:** this plugin requires a [Quaderno](https://quaderno.io/integrations/woocommerce/?utm_source=wordpress&utm_campaign=woocommerce) account.

== Installation ==

Following are the steps to install the WooCommerce Quaderno

1. Unpack the entire contents of this plugin zip file into your wp-content/plugins/ folder locally.
2. Upload to your site.
3. Navigate to wp-admin/plugins.php on your site (your WP Admin plugin page).
4. Activate this plugin.
5. Configure the options from WooCommerce > Settings > Integration.

OR you can just install it with WordPress by going to Plugins > Add New > and type this plugin's name

That's it! You can now customize your WooCommerce receipts and be EU VAT compliant.

== Frequently Asked Questions ==

= Do I need to modify any code? =
Nope - we take care of everything you. Just install the plugin, add your API token and you’ll be good to go!

= Does Quaderno work with any WooCommerce themes? =
Yes, Quaderno works with any WooCommerce theme - whether free, commercial or custom. You just need WooCommerce activated for Quaderno to work.

If you have any questions please get in touch with us at hello@quaderno.io.

== Screenshots ==

1. Copy your API token and API URL from your Quaderno account
2. Paste it on the Quaderno settings page
3. Calculate taxes on the fly if you sell digital goods to EU customers
4. Example of a receipt

== Changelog ==

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
* New: Support WordPress 5.5
* New: Support WooCommerce 4.3

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
* German: Deutsch - translated by [Alex Gahr](http://alexgahr.com)
* French: Français - translated by [Sébastien Jacobs](http://xando.be)
* Dutch: Nederlands - translated by [Sébastien Jacobs](http://xando.be)

*Note:* This plugin is fully localized. This is very important for all users worldwide. So please contribute your language to the plugin to make it even more useful. For translating we recommend the ["Poedit Editor"](http://www.poedit.net/).

