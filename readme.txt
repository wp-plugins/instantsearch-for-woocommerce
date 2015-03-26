=== InstantSearch+ for WooCommerce ===
Contributors: instantsearchplus
Tags: search, autocomplete, suggest, woocommerce, instant search, autosuggest, better search, product search, custom search, relevant search, category search, typeahead, woocommerce search, woocommerce product search, did you mean, e-commerce, live search, wordpress ecommerce, highlight terms, search highlight, search product, predictive search, woocommerce plugin, best search, instant-search
Requires at least: 3.3
Tested up to: 4.1.1
Stable tag: 1.3.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

InstantSearch+ for WooCommerce boosts conversion with fast, search as-you-type product suggestions. Find out what people are looking for on your site.

== Description ==
**Note: InstandSearch+ is FREE for stores with up to 50 products, and offers affordable paid subscriptions for larger stores from $4.99 per month or the discounted price of $48 per year. 
There is 30 day trial period where you can experience all of InstantSearch+ premium features. 
Full pricing information can be found at http://www.instantsearchplus.com/instantsearchplus-autocomplete-woocommerce**  

Add the fastest, most advanced, **cloud-based** instant search to your WooCommerce store, and see your conversion rates go up. 
**Search-as-you-type** product names, descriptions, images, and prices from the first typed character. 
Fast cloud-CDN-based results, Product promotions, and personalized real-time search query suggestions make it a must-have for your eCommerce store.  

Play with our demo site at http://woocommerce.instantsearchplus.com  

[youtube="https://www.youtube.com/watch?v=GbbzkIMcXoM"]


= Lightning-Fast Instant Search for WooCommerce Stores =
* **100% Cloud-based, CDN-backed**, product search results with the lowest possible latency
* Search-as-you-type product names, images, and prices from the **first typed character**
* In search, speed is a big deal. Google knows it. Amazon knows it. Faster results equal better conversion. That's why we make such a big deal of our lightning-fast service. Your users deserve it. 

= Advanced, Relevant, & Integrated Search Results Page =
* Contemporary, professionally-looking **search results page**
* **Did You Mean** auto typo correction
* Learning search algorithm that continuously gets better
* **Search term highlighting** - visual indication of the end user search term in found results

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

= Automatically Generated Search Filters =
* Automatically generated, fully customizable **filters**
* Can include price, vendor, weight, type, tags, or any other product option
* Help users narrow down search results


== Installation ==
1. Navigate to the 'Add New' in the plugins dashboard
2. Search for 'InstantSearch+ for WooCommerce'
3. Click 'Install Now'
4. Activate the plugin on the Plugin dashboard
5. In case you do not have a search box do one of the following:
	* Add search box widget - go to WordPress admin page ==> Appearance ==> Widgets and drag InstantSearch+ Search Box widget (or any other search widget) to your preferred location.
	* Add html code to your theme - Simply add HTML based form to your WordPress theme like this and we’ll pick it from there  
	  `<form class="isp_search_box_form" name="isp_search_box" action="/" style="width:10rem; float:none;">`  
	  		`<input type="text" name="s" class="isp_search_box_input" placeholder="Search..." autocomplete="Off" autocorrect="off" autocapitalize="off" style="outline: none; width:10rem; height:2.3rem;" id="isp_search">`  
	  		`<input type="hidden" name="post_type" value="product">`  
	  		`<input type="image" src="/wp-content/plugins/instantsearch-for-woocommerce/widget/assets/images/magnifying_glass.png" class="isp_widget_btn" value="">`  
	  `</form>`  

Having issues?  contact support@instantsearchplus.com




== Frequently asked questions ==
= Do I need to sign up for this service? =

No.  Once you install the plugin you will have our dashboard and customization right in the WordPress admin.

= What is the difference between the free and the Premium service versions =

Full details are here: http://www.instantsearchplus.com/instantsearchplus-autocomplete-woocommerce/
Basically our Premium versions contain more advanced customization, support, additional service capacity and monitoring.  You can always start with our Free plan and upgrade.

= Do you offer Trials? =

Yes - 30 days free trial

= Can InstantSearch+ work with my searchbox? =

Yes - InstantSearch+ can work with any html inputbox on your page. Be that a WordPress Widget or a custom template

= How can I add a searchbox to my site header? =

Go to Wordpress admin => Appearance => Widgets and include the InstantSearch+ Search Widget. 

= How can I add a search box to my web page using shortcode? =

Add the **shortcode isp_search_box** to your prefered location:
simply add **[isp_search_box]** or you can add search box with some extra configurations   
**[isp_search_box width=10 height=2.3 text_size=1 inner_text="Search..."]**   
* parameters:  width - search box's horizontal measurement | height - search box's vertical measurement | 
      text_size - text size | inner_text - the text inside the search box

= I want to add a searchbox to my theme and make it work with InstantSearch+ - how can I? =

Simply add HTML based form to your Wordpress theme like this and we’ll pick it from there:
	  `<form class="isp_search_box_form" name="isp_search_box" action="/" style="width:10rem; float:none;">`  
	  		`<input type="text" name="s" class="isp_search_box_input" placeholder="Search..." autocomplete="OfF" autocorrect="off" autocapitalize="off" style="outline: none; width:10rem; height:2.3rem;" id="isp_search">`  
	  		`<input type="hidden" name="post_type" value="product">`  
	  		`<input type="image" src="/wp-content/plugins/instantsearch-for-woocommerce/widget/assets/images/magnifying_glass.png" class="isp_widget_btn" value="">`  
	  `</form>`  


== Screenshots ==

1. Worldclass autocomplete and search suggestions for WooCommerce.
2. Analytics Dashboard to help you serve your users better.
3. Search Analytics to see what people search on your site, what they find, and what they don't.
4. Contemporary, professional search results page, with Did-you-mean
5. State of the art search results page, Did you mean, and search terms highlight
6. Automatically generated search filters for WooCommerce




== Changelog ==

= 1.3.0 = 
* Some server changes and adjustments
* Bug fix on multiple triggering of "the_posts" filter hook on search

= 1.2.18 = 
* Compatible with WooCommerce 2.3.5 (tested)
* product's page bug fix (WC_Product_Simple to string conversion)

= 1.2.17 = 
* Search result page performance improvement.
* Search box widget/shortcode css small adjustment.

= 1.2.16 = 
* InstantSearch+ search box widget warning fix. 
* compatible with WP-Rocket LazyLoad
* InstantSearch+ search box shortcode html error fix

= 1.2.15 = 
* analytics improvement
* new configuration option - change number of products per page through InstantSearch+ dashboard

= 1.2.14 = 
* new search box shortcode [isp_search_box] with optional parameters -> [isp_search_box width=10 height=2.3 text_size=1 inner_text="Search..."]

= 1.2.13 = 
* timeout increase on install
* compatible with Lazy Load plugin (thumbnails)
* search term highlighting bug fix

= 1.2.12 = 
* highlight same search query's stem words on search result page

= 1.2.11 = 
* filter by attributes - WooCommerce 2.0.x compatibility

= 1.2.10 = 
* filter by attributes - added simple product's attributes

= 1.2.9 = 
* on-demand product update sync (from the dashboard) 

= 1.2.8 = 
* fix - error on strict php mode when doing product update
* fix - out-of-stock update according to variation's stock quantity
* new - search by variation's sku

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

