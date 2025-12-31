=== Quaderno: Global Tax & Invoicing Automation for WooCommerce ===
Contributors: polimorfico
Tags: sales tax, vat, gst, verifactu, ticketbai
Tested up to: 6.9
Requires PHP: 7.4
Stable tag: 2.7.13
License: GPLv3

Automate global tax calculations and compliant invoicing for WooCommerce. Handle sales tax, VAT, GST worldwide with instant reports.

== Description ==

Transform tax complexity into seamless growth with automated global tax compliance. The Quaderno plugin eliminates manual tax calculations and invoice generation, ensuring your WooCommerce store stays compliant across all markets while you focus on scaling your business.

Selling globally shouldn't mean drowning in tax paperwork. Quaderno handles the intricate web of international sales tax, VAT, and GST regulations automatically, so you can concentrate on what matters most: growing your business.

https://www.youtube.com/watch?v=NyWw4Dye2ag

= How it works =

Quaderno works behind the scenes to automatically calculate precise taxes for every sale and generate compliant invoices and credit notes, regardless of your customer's location. Our intelligent system continuously updates with the latest tax regulations, seamlessly handling complex requirements including US sales tax, EU VAT, Canadian GST, and mandatory e-invoicing systems like Verifactu and TicketBAI.

= Key Benefits & Features = 

* **Effortless Global Tax Compliance**: Effortlessly comply with ever-changing local tax laws in countries around the world. Rest easy knowing your business is always in line with regulations.
* **Proactive Tax Notifications**: Get instant alerts when you become liable for taxes by surpassing a tax registration threshold worldwide. We'll also notify you when a tax rate changes in any region where you sell.
* **Real-Time, Accurate Tax Calculation**: Our intelligent database instantly identifies the precise tax rate and amount based on your product and your customer's exact location, ensuring accuracy every time.
* **Instant, Comprehensive Tax Reports**: Generate all the vital information you need for your tax returns at a glance. Quaderno's intuitive dashboard provides immediate, actionable insights into your tax obligations.
* **Automated E-invoicing & Credit Notes**: Ditch the manual work. Professionally crafted invoices and credit notes are automatically generated and sent for every order, available in multiple languages and currencies. We also provide support for legally compliant e-invoicing and e-reporting systems, including Verifactu/VERI*FACTU and TicketBAI.
* **Worldwide Registration & Filing Service**: Simplify your tax obligations from end to end. We offer a comprehensive service to handle your tax registration and filing, wherever you do business.
* **Customer Self-Service**: Empower your customers to download their invoices and credit notes directly from their WooCommerce orders page, reducing customer service inquiries and improving their experience.

== Frequently Asked Questions ==

= How does Quaderno automatically handle taxes for my WooCommerce sales? =
After [connecting to your WooCommerce store](https://support.quaderno.io/article/500-connecting-woocommerce/?utm_source=wordpress&utm_campaign=woocommerce), Quaderno runs automatically in the background. It instantly identifies the correct tax rate for each transaction based on your products and customer location, seamlessly integrating into your checkout process. It's truly set-and-forget tax automation.

= Does Quaderno support e-invoicing and e-reporting formats? =
Absolutely. Quaderno ensures compliance with global e-invoicing regulations, automatically supporting legally mandated systems including Spain's **Verifactu/VERI*FACTU** and **TicketBAI**. Your invoices are automatically formatted and submitted to tax authorities in the required format, eliminating manual compliance work while keeping your business fully compliant.

= Can I use Quaderno with other e-commerce platforms besides WooCommerce? =
Yes! Quaderno centralizes tax and invoicing across multiple sales channels. Beyond WooCommerce, you can integrate with platforms like Amazon FBA and Shopify, managing all your sales data, tax calculations, and compliance reporting from a single unified dashboard.

= What are the pricing options for Quaderno? =
Quaderno offers transparent, transaction-based pricing that scales with your business. Plans start at just $29 per month with no hidden fees. View all available [pricing plans here](https://www.quaderno.io/pricing/?utm_source=wordpress&utm_campaign=woocommerce).

== Installation ==

= Minimum Requirements =
* PHP 7.4 or greater is required (PHP 8.0 or greater is recommended)
* WooCommerce 3.2 or greater
* A [Quaderno account](https://quadernoapp.com/signup/?utm_source=wordpress&utm_campaign=woocommerce).

= Automatic installation =
Automatic installation is the easiest option -- WordPress will handle the file transfer, and you won’t need to leave your web browser. To do an automatic install of WooCommerce Quaderno, log in to your WordPress dashboard, navigate to the Plugins menu, and click “Add New.”

In the search field type “WooCommerce Quaderno,” then click “Search Plugins.” Once you’ve found us, you can view details about it such as the point release, rating, and description. Most importantly of course, you can install it by! Click “Install Now,” and WordPress will take it from there.

= Manual installation =
Manual installation method requires downloading the WooCommerce Quaderno plugin and uploading it to your web server via your favorite FTP application. The WordPress codex contains [instructions on how to do this here](https://wordpress.org/support/article/managing-plugins/#manual-plugin-installation).


== Screenshots ==

1. Copy your API token and API URL from your Quaderno account
2. Paste it on the Quaderno settings page
3. Calculate worldwide taxes on the fly 
4. Example of an invoice


== Changelog ==

= 2.7.13 – Dec 31, 2025 =
* Update: improve code efficiency in invoice manager class

= 2.7.12 – Dec 27, 2025 =
* New: add validation warnings to integration settings page
* New: add tool to send recent orders to Quaderno
* New: add "Quaderno -" prefix to all tools for better identification
* New: detect checkout type (Block vs Classic) in status page
* Update: translations

= 2.7.11 – Dec 13, 2025 =
* New: improve status page with validation warnings
* New: track new payment method
* Update: translations

= 2.7.10 – Nov 28, 2025 =
* Fix: multiple minor errors

= 2.7.9 – Nov 25, 2025 =
* New: publish changelog file in WordPress

= 2.7.8 – Nov 13, 2025 =
* Fix: issue with tax ID validation

= 2.7.7 – Nov 12, 2025 =
* New: Validate Swiss and Quebec VAT

= 2.7.6 – Nov 4, 2025 =
* New changelog file 

= 2.7.5 – Oct 11, 2025 =
* Fix: error in plugin activation

= 2.7.4 – Oct 10, 2025 =
* Update images

= 2.7.3 – Oct 10, 2025 =
* Update: remove obsolete code

= 2.7.2 – Sep 4, 2025 =
* Update: README content

= 2.7.1 – Aug 23, 2025 =
* Fix: warning message when tax status is not present

= 2.7.0 – Aug 8, 2025 =
* New: autosend invoices if the autosend preference is active in Quaderno
* New: recalculate taxes for all subscriptions with no taxes if necessary
* New: remove the function to activate universal pricing
* New: improve security

= 2.6.2 – May 22, 2025 =
* Fix: error when tax ID is not set
* Fix: error when a subscription contains shipping items

= 2.6.1 – May 15, 2025 =
* New: suppport for subscriptions with variations
* New: use tax ID for tax calculations of shipping orders

= 2.6.0 – May 14, 2025 =
* New: show billing period on invoices with subscriptions
* Fix: remove unnecessary calls to tax calculation API

= 2.5.6 – May 12, 2025 =
* Fix: taxes will be added to invoices only if WooCommerce option has been activated 

= 2.5.5 – April 29, 2025 =
* Fix: reverse-charge checking in subscriptions

= 2.5.4 – April 16, 2025 =
* New: support for WordPress 6.8
* New: support for WooCommerce 9.8

= 2.5.3 – March 31, 2025 =
* Fix: javascript issue
* New: use street address to calculate taxes

= 2.5.2 – March 12, 2025 =
* Fix: javascript issue

= 2.5.1 – March 3, 2025 =
* New: migrate jQuery scripts to vanilla javascript

= 2.5.0 – March 2, 2025 =
* New: support for Canadian provincial taxes
* New: show admin notice when an error is detected

= 2.4.1 – February 21, 2025 =
* New: send error messages to WooCommerce logs

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
* Update: unknow products are classified as goods
* New: Support for WooCommerce 6.0

= 1.23.9 – November 14, 2021 =
* Update: delete tax ids validations from cache
* Update: do not cache tax ids when validation service is down

= 1.23.8 – August 30, 2021 =
* Update: send shipping address to Quaderno only if it exists
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
* Update: Identify payments via PayPal Express

= 1.21.5 – March 19, 2020 =
* New: Allow users to remove actions from invoice, credit, and order managers.

= 1.21.4 – March 18, 2020 =
* New: Support WooCommerce 4.0

= 1.21.3 – March 2, 2020 =
* Update: Recurring customer can store their Tax ID in their billing details
* Update: Quaderno invoices are always opened in a new tab

= 1.21.2 – February 21, 2020 =
* Update: Better management of tax IDs
* Update: Add an order note when tax IDs cannot be validated
* Fix: issue credit note for receipts

= 1.21.1 – February 5, 2020 =
* Update: remove non-word characters from tax ID

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
* Update: create a new contact in Quaderno if the customer changes their name
* New: the checkout form supports any tax ID

= 1.17.7 - October 2, 2019 =
* Update: only use VAT number if exists

= 1.17.6 - September 25, 2019 =
* Update: use original name for fees
* Update: add receipts threshold

= 1.17.5 - September 23, 2019 =
* Update: always send customer billing address
* Update: user WordPress current_time function 

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
* Update: Send products SKUs to Quaderno
* Update: Link refunds with invoices
* Update: Send order URL to Quaderno

= 1.15.8 - February 26, 2019 =
* Tested with WordPress 5.1

= 1.15.7 - January 8, 2019 =
* Update: simplify settings

= 1.15.6 - December 12, 2018 =
* Update: use a default name when customer's first name is not present

= 1.15.5 - November 15, 2018 =
* Update: delete transients when plugin is deactivated

= 1.15.4 - November 2, 2018 =
* Fix: VAT number is displayed when customer lives in the shop country

= 1.15.3 - October 24, 2018 =
* Update: Add reverse charge note when VAT number is present

= 1.15.2 - October 2, 2018 =
* Fix: Cannot mixed different tax classes in the same cart

= 1.15.1 - September 18, 2018 =
* Fix: Use billing state to calculate tax rates

= 1.15.0 - September 14, 2018 =
* Update: Use Quaderno tax calculator as fallback for non-digital products
* Update: Show region name on invoices
* Update: Send tax county, state and transaction type to Quaderno
* Update: Refactoring credit note creation

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
* Update: Invoices and credits generation
* New: Stop base taxes being taken off when dealing with out of base locations

= 1.5.2 =
* Fix: javascript was not updated

= 1.5.1 =
* Update: Hide VAT Number field when customer is based in the store country

= 1.5.0 =
* New: Validate EU VAT Numbers
* New: Compatibility with Sequential Order Numbers Pro
* New: Track different payment methods on Quaderno

= 1.4.2 =
* Fix: issue when postal code contains whitespaces

= 1.4.0 =
* Refactoring code
* Fix: issue in sending documents
* Update: Update descriptions

= 1.3.2 =
* Minor fixes to improve security

= 1.3.1 =
* Update: Use wp_remote_request instead of curl

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
