=== WooCommerce Quaderno ===
Author URI: https://quaderno.io
Contributors: polimorfico
Tags: woocommerce, quaderno, woocommerce quaderno, vat, eu vat, vatmoss, vat moss, european vat, eu tax, european tax, billing, invoices, receipts
Requires at least: 4.6
Tested up to: 4.9
Stable tag: 1.14.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Quaderno replaces and improves the default WooCommerce receipts. Setup in less than 5 minutes.

== Description ==

Quaderno for WooCommerce takes taxes off your plate by automatically calculating tax on every sale and following up with beautiful invoices or receipts, no matter where your customer is located. Transactions and invoices processed through Quaderno always comply with ever-changing local tax rules, including in the US, Canada, Australia, New Zealand, Singapore, and the European Union.

https://www.youtube.com/watch?v=298MR-kf1is

= What you get =

* **Automatic invoices, receipts, and credit notes** with every order in your store, in **multiple languages and currencies**.
* **Comply with local tax laws** in countries around the world, including the EU VAT rules for digital goods & services.
* **100% customizable invoices** to reflect your brand or add a personal touch.
* **Manage all your invoices and revenue sources** in one easy-to-use dashboard.
* **Real-time reports of your revenue**. Track profits, losses, and patterns in the data to identify where your business is going strong and where it may need some TLC.

**Setup in less than 5 minutes**. Fast and easy!

1. Download & activate this plugin
2. [Sign up](https://quaderno.io/integrations/woocommerce/) for a Quaderno account
3. Paste your API key in your WooCommerce site
4. Brand your Quaderno invoices with custom design

**Note:** this plugin requires both a [WooCommerce](https://woocommerce.com) and [Quaderno](https://quaderno.io/integrations/woocommerce/) account.

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

