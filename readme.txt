=== InstantSearch+ for WooCommerce ===
Contributors: instantsearchplus
Donate link:
Tags: search, autocomplete, suggest, woocommerce, instant search, autosuggest, better search, product search, custom search, relevant search, category search, typeahead, woocommerce search, woocommerce product search, did you mean, e-commerce, live search, wordpress ecommerce, highlight terms, search highlight, search product
Requires at least: 3.3
Tested up to: 4.0
Stable tag: 1.2.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Boost conversion with fast, search as-you-type product suggestions. Find out what people are looking for on your site.

== Description ==
Add the fastest, most advanced, cloud-based instant search to your WooCommerce store, and see your conversion rates go up. 
Search-as-you-type product names, descriptions, images, and prices from the first typed character. 
Fast cloud-CDN-based results, Product promotions, and personalized real-time search query suggestions  make it a must-have for your eCommerce store.

Play with our demo site at http://woocommerce.instantsearchplus.com  

Enjoy our 7- day FREE TRIAL and then sign up to one of our affordable monthly subscriptions from $9.99. To learn more please visit: http://www.instantsearchplus.com/instantsearchplus-autocomplete-woocommerce
InstantSearch+ is free up to 50 products.


[youtube="https://www.youtube.com/watch?v=GbbzkIMcXoM"]


## Features
= Lightning-Fast Instant Search for WooCommerce Stores =
* **100% Cloud-based, CDN-backed**, product search results with the lowest possible latency
* Search-as-you-type product names, images, and prices from the **first typed character**
* In search, speed is a big deal. Google knows it. Amazon knows it. Faster results equal better conversion. That's why we make such a big deal of our lightning-fast service. Your users deserve it. 

= Product Instant Search & Promotions =
* Contextual product promotions based on what visitors type - Promote specific products
* Product suggestions based on your WooCommerce store catalog
* Turbolinks that link destinations to non-product searches & synonyms

= Personal Real Time Search Suggestions =
* Popular Searches based on what other people type
* Personal history Searches based on what the visitor previously searched
* Advanced word matching and typo correction. Your visitor will get suggestions even when they misspell

= Online InstantSearch Portal and Search Terms Report =
* Usage dashboard and email reports
* Top search suggestions - what people searched for on your WooCommerce store
* Top products - which product suggestions were chosen
* Top under-served searches - what people look for but cannot find

= Seamless Integration with Your WooCommerce Store =
* 2-minute installation
* 100% pure additive Javascript and CSS goodness - does not break or replace your store's original functionality or built-in search
* Customizable look and feel to fit your store frontend
* Desktop, tablet and mobile theme supported

= Enhanced Search Results Page =
* **Did you mean** - provides alternative suggestions for misspelled searches
* **search term highlighting** - visual indication of the end user search term in found results

== Installation ==
1. Navigate to the 'Add New' in the plugins dashboard
2. Search for 'InstantSearch+ for WooCommerce'
3. Click 'Install Now'
4. Activate the plugin on the Plugin dashboard
5. In case you do not have a search box do one of the following:
	* Add search box widget - go to WordPress admin page ==> Appearance ==> Widgets and drag InstantSearch+ Search Box widget (or any other search widget) to your preferred location.
	* Add html code to your theme - Simply add HTML based form to your WordPress theme like this and we’ll pick it from there  
	  `<form action="/">`  
		  `<input type="text" name="s" placeholder="Product search">`  
		  `<input type="hidden" name="post_type" value="product">`  
	  `</form>`

Having issues?  contact support@instantsearchplus.com




== Frequently asked questions ==
= Do I need to sign up for this service? =

No.  Once you install the plugin you will have our dashboard and customization right in the WordPress admin.

= What is the difference between the free and the Premium service versions =

Full details are here: http://www.instantsearchplus.com/instantsearchplus-autocomplete-woocommerce/
Basically our Premium versions contain more advanced customization, support, additional service capacity and monitoring.  You can always start with our Free plan and upgrade.

= Do you offer Trials? =

Yes - 7 days free trial

= Can InstantSearch+ work with my searchbox? =

Yes - InstantSearch+ can work with any html inputbox on your page. Be that a WordPress Widget or a custom template

= How can I add a searchbox to my site header? =

Go to Wordpress admin => Appearance => Widgets and include the InstantSearch+ Search Widget. 

= I want to add a searchbox to my theme and make it work with InstantSearch+ - how can I? =
Simply add HTML based form to your Wordpress theme like this and we’ll pick it from there:
`<form action="/">
 	<input type="text" name="s" placeholder="Product search">
 	<input type="hidden" name="post_type" value="product">
</form>`

== Screenshots ==

1. World-class autocomplete dropdown with WooCommerce products search and popular searches.
2. WordPress compatible dashboard to see insights and customize the widget.
3. Full text product search with "did-you-mean", typo correction and search terms highlight - full page
4. Full text product search with "did-you-mean", typo correction and search terms highlight - zoom




== Changelog ==

= 1.2.7 = 
* bug fix - product quantity update after orders
* bug fix - warning after install
* WooCommerce integration bug fix for WooCommerce versions 2.0.x
* Compatible with WooCommerce 2.2.x

= 1.2.6 = 
* support to scheduled sale prices dates - product's sale price will be up-to-date
* out of stock update - when product's order turns its quantity to 0 (if manage stock is enabled)
* Compatible with WordPress 4.0

= 1.2.5 = 
* support for localhost and sites in maintenance mode

= 1.2.4 = 
* highlight terms on full text search improvements (few new classes)
* new option (InstantsSearch+ dashboard => Customize tab => Force searchbox to products-only search) - if chosen, won't take over search boxes that does not have post_type=product input, those search boxes will continue to return WordPress default results.  

= 1.2.3 = 
* new - search by categories
* multiple languages support on "did you mean"
* fix - handle multiple full text search requests

= 1.2.2 = 
* InstantSearch+ search box widget improvements + widget configurations

= 1.2.1 = 
* new InstantSearch+ search box widget

= 1.2.0 = 
* WooCommerce integration
* admin notice after install

= 1.1.1 = 
* fix - security issues
* WooCommerce version 2.1.12 compatible
* fix - strict PHP warnings

= 1.1.0 = 
* highlight search terms
* additional fix - shortcode filter

= 1.0.20 = 
* added removal of shortcodes which are not in $shortcode_tags global parameter
* fix - dashboard load from secured site ("https")
* improvement to search queries (request side)

= 1.0.19 = 
* removal of shortcode tags & php snippets from description & short_description on search/autocomplete

= 1.0.18 = 
* better integration with multi-language plugins ("WooCommerce Multilingual" in particular) 

= 1.0.17 = 
* "not ready" admin message after installation on full text search
* fix - on full text search, wrong posts_per_page value
* page injection change

= 1.0.15 = 
* additional logging

= 1.0.14 = 
* modified price fields on autocomplete - new display
* new 'settings' link from plugins page to InstantSearch+ Dashboard
* fix - full text search error handler
* fix - warnings

= 1.0.13 = 
* installation error fix when WooCommerce's version is below 2.1
* added - notification on sync products status after install
* fix - on full text search, results per page parameter modification

= 1.0.12 = 
* small bug fix + logging

= 1.0.11 = 
* cron schedule for site's alert query
* admin notice for quota exceeded alert

= 1.0.10 =
* full text search disable on "ordering" + additional install logging

= 1.0.9 =
* Enhanced WooCommerce Full Text search with InstantSearch+ the best cloud-based search

= 1.0.8 =
* added new youtube video
* multiple install logging

= 1.0.7 =
* additional log was added (multisite)

= 1.0.6 =
* added Cron events (fix import products issue)
* added retry request on activation failure (when server returns an error)  

= 1.0.4 =
* Fix - fatal error on sending large number of products after install/activation

= 1.0.0 =
* First version!

== Updates ==
