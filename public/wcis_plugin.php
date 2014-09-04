<?php
/**
 * InstantSearch+ for WooCommerce.
 *
 * @package   WCISPlugin
 * @author    InstantSearchPlus
 * @license   GPL-2.0+
 * @link      http://www.instantsearchplus.com
 * @copyright 2014 InstantSearchPlus
 */

if ( ! defined( 'ABSPATH' ) ) 
	exit; // Exit if accessed directly

/**
 * @package WCISPlugin
 * @author  InstantSearchPlus <support@instantsearchplus.com>
 */
class WCISPlugin {      
//     const SERVER_URL = 'http://woo.instantsearchplus.com/';
	const SERVER_URL = 'http://0-1vk.acp-magento.appspot.com/';

	const VERSION = '1.2.6';
	
	// cron const variables
	const CRON_THRESHOLD_TIME 				 = 1200; 	// -> 20 minutes
	const CRON_EXECUTION_TIME 				 = 900; 		// -> 15 minutes
	const SINGLES_TO_BATCH_THRESHOLD		 = 10;		// if more then 10 products send as batch
	const CRON_SEND_CATEGORIES_TIME_INTERVAL = 30;       // -> 30 secunds
	
	const ELEMENT_TYPE_PRODUCT				 = 0;
	const ELEMENT_TYPE_CATEGORY				 = 1;

	/**
	 *
	 * Unique identifier for your plugin.
	 *
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'WCISPlugin';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;  
	
	/*
	 * Full text search parameters
	 */
	private $wcis_fulltext_ids = null;
	private $wcis_total_results = 0;
	private $wcis_did_you_mean_fields = null;
	private $wcis_search_query = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {
		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Activate plugin when new blog is added
        add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );
                
//         add_action( 'publish_post', array( $this, 'on_product_update' ));

        // product change handlers
        add_action('save_post', array( $this, 'on_product_save'));
        add_action('trashed_post', array( $this, 'on_product_delete'));
        add_action('woocommerce_order_status_on-hold', array( $this, 'quantity_change_handler'), 501);        
        
		add_action ( 'edit_product_cat', array( $this, 'on_category_edit' )); 
		add_action ( 'create_product_cat', array( $this, 'on_category_create' )); 
		add_action ( 'delete_product_cat', array( $this, 'on_category_delete' ));
           
            // profile changes (url/email update handler)
//             add_action( 'profile_update', array( $this, 'on_profile_update') );
//             add_action('admin_init', array( $this, 'on_profile_update'));

        // Load public-facing style sheet and JavaScript.
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
            
        add_action('parse_request', array($this, 'process_instantsearchplus_request'));
        add_filter('query_vars', array($this, 'filter_instantsearchplus_request'));   

        // cron
        add_action('instantsearchplus_cron_request_event', array( $this, 'execute_update_request' ) );   
        add_action('instantsearchplus_cron_check_alerst', array( $this, 'check_for_alerts' ) );
        add_action('instantsearchplus_send_logging_record', array($this, 'send_logging_record'), 10, 1);
        
        add_action('instantsearchplus_send_all_categories', array($this, 'send_categories_as_batch'));
        add_action('instantsearchplus_send_batches_if_unreachable', array($this, 'send_batches_if_unreachable'));
        
        // FullText search
        add_filter( 'posts_search', array( $this, 'posts_search_handler' ) );
        add_action( 'pre_get_posts', array( $this, 'pre_get_posts_handler' ) );
        add_filter( 'post_limits', array( $this, 'post_limits_handler' ) );
        add_filter( 'the_posts', array( $this, 'the_posts_handler' ) );
        // Highlight search terms
        add_filter( 'the_title', array($this, 'highlight_result_handler'), 50);
        add_filter( 'the_content', array($this, 'highlight_result_handler'), 50);
        add_filter( 'the_excerpt', array($this, 'highlight_result_handler'), 50);
        add_filter( 'the_tags', array($this, 'highlight_result_handler'), 50);
        
        // admin message     
        add_action('admin_notices',  array( $this, 'show_admin_message'));
        add_action('admin_init', array( $this, 'admin_init_handler'));
        
        // WooCommerce Integration
        add_filter( 'woocommerce_integrations', array( $this, 'add_woocommerce_integrations_handler' ) );
        
        // InstantSearch+ search box widget
        add_action( 'widgets_init', array( $this, 'widgets_registration_handler' ) );
        
        add_action( 'woocommerce_scheduled_sales', array( $this, 'on_scheduled_sales'));
        
	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
	}
	
	public static function wcis_add_action_links( $links ) {				
		$url = 'http://woo.instantsearchplus.com/wc_dashboard';
		$params = '?site_id=' . get_option( 'wcis_site_id' );
		$params .= '&authentication_key=' . get_option('authentication_key');
		$params .= '&new_tab=1';
		$params .= '&v=' . WCISPlugin::VERSION;
				
		return array_merge(
				array(
						'Settings' => '<a href="' . $url . $params . '" target="_blank">'. 
							__( 'Settings', WCISPlugin::get_instance()->get_plugin_slug() ) .'</a>'
				),
				$links
		);
		
	}
	
	public function widgets_registration_handler(){
		register_widget('WCISPluginWidget');
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public function activate( $network_wide )
	{			
		self::single_activate();
		
// 		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
// 			if ( $network_wide  ) {
// 				// Get all blog ids
//                 $blog_ids = self::get_blog_ids();

//                 foreach ( $blog_ids as $blog_id ) {
// 	                switch_to_blog( $blog_id );
// 	                self::single_activate();
//                 }
// 				restore_current_blog();
// 			} else {
//             	self::single_activate();
//             }

// 		} else {
// 			self::single_activate();
// 		}
//             wp_redirect( admin_url( 'admin.php?page=WCISPlugin' ) );
	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public function deactivate( $network_wide ) {		
		wp_clear_scheduled_hook( 'instantsearchplus_cron_request_event' );
		wp_clear_scheduled_hook( 'instantsearchplus_cron_check_alerst' ); 
		wp_clear_scheduled_hook( 'instantsearchplus_send_logging_record' );
		wp_clear_scheduled_hook( 'instantsearchplus_send_all_categories' );
		wp_clear_scheduled_hook( 'instantsearchplus_send_batches_if_unreachable' );
		
		delete_option('is_activation_triggered');
		
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
		
			if ( $network_wide ) {
		
				// Get all blog ids
				$blog_ids = self::get_blog_ids();
		
				foreach ( $blog_ids as $blog_id ) {
		
					switch_to_blog( $blog_id );
					self::single_deactivate();
		
				}
		
				restore_current_blog();
		
			} else {
				self::single_deactivate();
			}
		
		} else {
			self::single_deactivate();
		}	
        
	}

	/**
	 * Fired when the plugin is uninstalled.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function uninstall( $network_wide ) {		
		if ( ! current_user_can( 'activate_plugins' ) ){
			return;
		}
	
		$url = self::SERVER_URL . 'wc_update_site_state';
		
		$args = array(
				'body' => array('site' => get_option('siteurl'),
						'site_id' => get_option( 'wcis_site_id'),
						'authentication_key' => get_option('authentication_key'),
						'email' => get_option( 'admin_email'),
						'site_status' => 'uninstall' )
		);
		
		$resp = wp_remote_post( $url, $args );
	
		// deleting the database
		delete_option('wcis_site_id');
		delete_option('wcis_batch_size');
		delete_option('authentication_key');
		delete_option('wcis_timeframe');
		delete_option('wcis_logging');
		delete_option('max_num_of_batches');
		delete_option('wcis_total_results');
		
		delete_option('fulltext_disabled');
		delete_option('just_created_site');
		
		// compatibility 
		delete_option('wcis_fulltext_ids');
		delete_option('wcis_did_you_mean_enabled');
		delete_option('wcis_did_you_mean_fields');
		delete_option('wcis_search_query');
		
		delete_option('wcis_enable_highlight');
		
		delete_option('cron_product_list');
		delete_option('cron_category_list');
		delete_option('cron_in_progress');
		delete_option('cron_send_batches_disable');
		delete_option('wcic_site_alert');
		delete_option('wcis_just_created_alert');
	}
	
	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}
		
		$err_msg = "activate_new_site triggered";
		self::send_error_report($err_msg);

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();
	}
    
	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private function single_activate($is_retry = false) {
		if (get_option('is_activation_triggered')){
			return;
		}
		update_option('is_activation_triggered', true);
		update_option('wcis_just_created_alert', true);
				
		if (!wp_next_scheduled( 'instantsearchplus_cron_check_alerst' )){	
			wp_schedule_event(time(), 'daily', 'instantsearchplus_cron_check_alerst');
		}
		
		$url = self::SERVER_URL . 'wc_install';  
		$multisite_array = array();
        try{
        	// multisite data
	    	if ( function_exists( 'is_multisite' ) && is_multisite() ) {
	        	// Get all blog ids
	        	$blog_ids = self::get_blog_ids();
	        	$i = 0;
	        	foreach ( $blog_ids as $blog_id ) {
	            	switch_to_blog( $blog_id );
	            	$current_site_url = get_site_url( get_current_blog_id() );
	            	$blog_details = get_blog_details($blog_id);
	            			
	            	$multisite_info = array(
		            			'i' 				   			=> $i, 
		                		'current_site_url'				=> $current_site_url, 
		                		'blog_id' 						=> $blog_id, 
	            				'name'							=> $blog_details->blogname,
	            				'site_id'						=> $blog_details->site_id,
	            				'blog_details_site_id'			=> $blog_details->blog_id,
		        	);
	            	$multisite_array[] = $multisite_info;
	           		$i++;            		
	            }
	            restore_current_blog();
			}
	        if (function_exists( 'is_multisite' ) && is_multisite()){
	        	$is_multisite_on = true;
	        } else {
	        	$is_multisite_on = false;
	        }
	
	        $json_multisite = json_encode($multisite_array);
		} catch (Exception $e){
        	$is_multisite_on = false;
		}
            // end multisite
            
        $args = array(
        	'body' => array('site' 			=> get_option('siteurl'), 
        				'email' 			=> get_option( 'admin_email' ), 
        				'product_count' 	=> wp_count_posts('product')->publish,
        				'store_id' 			=> get_current_blog_id(),
        				'is_multisite'		=> $is_multisite_on,
        				'multisite_info'	=> $json_multisite,
        				'version'			=> self::VERSION,
        	)		
        );
            
        $resp = wp_remote_post( $url, $args );
			
        if (is_wp_error($resp) || $resp['response']['code'] != 200)
        {             	
        	$err_msg = "install req returned with an error code, sending retry install request: " . $is_retry;
        	try{
            	if (is_wp_error($resp)){
            		$err_msg = $err_msg . " - error msg: " . $resp->get_error_message();
            	}
        	} catch (Exception $e) {}
            	
        	self::send_error_report($err_msg);
       		if (!$is_retry){
            	self::single_activate(true);
       		}

        } else {	// $resp['response']['code'] == 200
            	// the server returns site id in the body of the response, save it in the options
        	try{
	        	$response_json = json_decode($resp['body']);
	        	if ($response_json == Null){
	            	$err_msg = "After install json_decode returned null";
	            	self::send_error_report($err_msg);
	            	if (!$is_retry){
	            		self::single_activate(true);
	            	}
	            	return;
	            }

            	$site_id = $response_json->{'site_id'};
            	$batch_size = $response_json->{'batch_size'};
            	update_option('wcis_site_id', $site_id);
            	update_option('wcis_batch_size', $batch_size);
            	$max_num_of_batches = $response_json->{'max_num_of_batches'};
            	update_option('max_num_of_batches', $max_num_of_batches);
	            	
            	$authentication_key = $response_json->{'authentication_key'};
            	update_option('authentication_key', $authentication_key);
            	
            	$update_product_timeframe = $response_json->{'wcis_timeframe'};
            	update_option('wcis_timeframe', $update_product_timeframe);
	            	
			} catch (Exception $e){
            	$err_msg = "After install internal exception raised msg: ". $e->getMessage();
            	self::send_error_report($err_msg);
            }

            self::push_wc_products();
            
            wp_schedule_single_event(time() + self::CRON_SEND_CATEGORIES_TIME_INTERVAL, 'instantsearchplus_send_all_categories');
		}
	}
   
	
	public function on_category_edit($category_id = null){
		if ($category_id == null)
			return;		
		self::on_category_update($category_id, 'edit');
	}
	
	public function on_category_create($category_id){
		if ($category_id == null)
			return;		
		self::on_category_update($category_id, 'create');
	}
	
	public function on_category_delete($category_id){
		if ($category_id == null)
			return;		
		self::on_category_update($category_id, 'delete');
	}
	
	public function on_category_update($category_id, $action){
	    $categorys_list = get_option('cron_category_list');
	    $timestamp = wp_next_scheduled( 'instantsearchplus_cron_request_event' );
	    if(get_option('wcis_timeframe')){
	        $timeframe = get_option('wcis_timeframe');
	    } else {
	        $timeframe = self::CRON_EXECUTION_TIME;
	    }
	    
	    if ($timestamp != false){ // event already scheduled
	        if ($categorys_list){ // category cron list is not empty
	            self::insert_element_to_cron_list($category_id, $action, self::ELEMENT_TYPE_CATEGORY);
	        } else {
	            wp_clear_scheduled_hook('instantsearchplus_cron_request_event');
	            $err_msg = "event scheduled to Cron but category's list is empty";
	            self::send_error_report($err_msg);
	        }
	    } else {  // event not scheduled
	        if ($categorys_list){ 
	            if (!get_option('cron_in_progress')){	                
	                self::execute_update_request();
	                wp_schedule_single_event(time() + $timeframe, 'instantsearchplus_cron_request_event');
	            }
	            // add current category to the list and schedule cron event
	            self::insert_element_to_cron_list($category_id, $action, self::ELEMENT_TYPE_CATEGORY);
	        } else {
	            self::insert_element_to_cron_list($category_id, $action, self::ELEMENT_TYPE_CATEGORY);
	            wp_schedule_single_event(time() + $timeframe, 'instantsearchplus_cron_request_event');
	        }
	    }	
	}
	

    private function query_products($page = 1) {
    	include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    	if(is_plugin_active('woocommerce-multilingual/wpml-woocommerce.php')){
			// set base query arguments for multi-language site
			$query_args = array(
				'fields'      => 'ids',
				'post_type'   => 'product',
				'post_status' => 'publish',
	            'posts_per_page' => get_option( 'wcis_batch_size' ), 
				'meta_query' => array(),
	            'paged' => $page,
				'suppress_filters' => true
			);
    	} else {
    		// set base query arguments
    		$query_args = array(
    			'fields'      => 'ids',
    			'post_type'   => 'product',
    			'post_status' => 'publish',
    			'posts_per_page' => get_option( 'wcis_batch_size' ),
    			'meta_query' => array(),
    			'paged' => $page,
    		);    		
    	}

		return new WP_Query( $query_args );
	}
    
    private function push_wc_products()
    {
        /**
         * Check if WooCommerce is active
         **/
        try{
	        if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	        	try {	     
	        		if (get_option('wcis_logging')){
	        			$err_msg = "found woocommerce extension";
	        			self::send_error_report($err_msg);
	        		}
	        		
		            $product_array = array();
		            $loop = self::query_products();
		            $page        = $loop->get( 'paged' );	// batch number
					$total       = $loop->found_posts;		// total number of products
					$total_pages = $loop->max_num_pages;	// total number of batches

		            $max_num_of_batches = get_option('max_num_of_batches');
		            $is_additional_fetch_required = false;
		            
		            while ($page <= $total_pages){
		            	if ($loop->have_posts()){
		            		foreach( $loop->posts as $id ){
		            			$product = self::get_product_from_post($id);
		            			$product_array[] = $product;
		            		}
		            	}
		                
		                if($max_num_of_batches == $page && $total_pages > $max_num_of_batches){
		                	// need to schedule request from server side to send the rest of the batches after activation ends
		                    wp_schedule_single_event(time() + (self::CRON_SEND_CATEGORIES_TIME_INTERVAL * 10 /*5 minutes*/), 'instantsearchplus_send_batches_if_unreachable');
		                	$is_additional_fetch_required = true;
		                }
		                
		                $send_products = array(
		                		'total_pages' 				   	=> $total_pages, 
		                		'total_products'				=> $total, 
		                		'current_page' 					=> $page, 
		                		'products'						=> $product_array,
		                		'is_additional_fetch_required' 	=> $is_additional_fetch_required,
		                );

		                if (get_option('wcis_logging')){
		                	$err_msg = "sending batch...";
		                	self::send_error_report($err_msg);
		                }
		                self::send_products_batch($send_products);
		                
		                // clearing array
		                unset($product_array);	
		                $product_array = array();
		                unset($send_products);
		                
		                $page = $page + 1;
		                
		                // too many products on activation, will get the rest of the products, by server request, after the activation is done
		                if ($is_additional_fetch_required){
		                	break;
		                }
		                
		                $loop = self::query_products($page);
		            }
		           
	            } catch (Exception $e) {
	            	$err_msg = "exception on woocommerce check, msg: " . $e->getMessage();
	            	self::send_error_report($err_msg);
	            }
	                        
	        } else {        	
	        	// alternative way  
	        	try{
		        	include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); 
		        	if (is_plugin_active( 'woocommerce/woocommerce.php')){
		        		$is_woo = 'true';
		        	} else { 
		        		$is_woo = 'false';
		        	}
	        	} catch (Exception $e){
	        		$is_woo = 'false (Exception)';
	        	}
	        	
	        	$err_msg = "can't find active plugin of woocommerce, alternative check: " . $is_woo;
	        	self::send_error_report($err_msg);
	        }
        } catch (Exception $e){
        	$err_msg = "before fetch products Exception was raised";
        	self::send_error_report($err_msg);
        }
    }
    
    private function push_wc_batch($batch_num){
    	$loop = self::query_products($batch_num);
    	$product_array = array();
    	$total       = $loop->found_posts;		// total number of products
    	$total_pages = $loop->max_num_pages;	// total number of batches
    	while ( $loop->have_posts() ){
    		$loop->the_post();
    		$product = self::get_product_from_post(get_the_ID());
    		$product_array[] = $product;
    	}
    	
    	$send_products = array(
    			'total_pages' 				   	=> $total_pages,
    			'total_products'				=> $total,
    			'current_page' 					=> $batch_num,
    			'products'						=> $product_array,
    			'is_additional_fetch_required' 	=> false,
    	);    	
    	wp_reset_postdata();
    	self::send_products_batch($send_products);	
    }
    
    
    private function get_product_from_post($post_id)
    {
    	$woocommerce_ver_below_2_1 = false;
    	if ( version_compare( WOOCOMMERCE_VERSION, '2.1', '<' ) ){
    		$woocommerce_ver_below_2_1 = true;
    	}

    	if ($woocommerce_ver_below_2_1){
    		$product = 	new WC_Product_Simple($post_id);
    	} else {
        	$product = new WC_Product( $post_id );
    	}
        
        //$post_categories = wp_get_post_categories( $post_id );
        //$categories = get_the_category();

    	$thumbnail = $product->get_image();    	
    	if ($thumbnail){
    		preg_match('/<img(.*)src(.*)=(.*)"(.*)"/U', $thumbnail, $result);
    		$thumbnail = array_pop($result);
    	}
    	
    	// handling scheduled sale price update
    	if (!$woocommerce_ver_below_2_1){
    	    $sale_price_dates_from = get_post_meta( $post_id, '_sale_price_dates_from', true );
    	    $sale_price_dates_to = get_post_meta( $post_id, '_sale_price_dates_to', true );
    	    self::schedule_sale_price_update($post_id, null);
    	    if ($sale_price_dates_from){
    	        self::schedule_sale_price_update($post_id, $sale_price_dates_from);
    	    } 
    	    if ($sale_price_dates_to){
    	        self::schedule_sale_price_update($post_id, $sale_price_dates_to);
    	    }
    	}
    	
    	$send_product = array('product_id' => $product->id,
    			'currency' => get_woocommerce_currency(),
    			'price' =>$product->get_price(),
    			'url' =>get_permalink($product->id),
    			'thumbnail_url' =>$thumbnail,
    			'action'=>'insert',
    			'description'=>$product->get_post_data()->post_content,
    			'short_description'=>$product->get_post_data()->post_excerpt,
    			'name'=>$product->get_title(),
    			'sku' => $product->get_sku(),
    			'categories'=>$product->get_categories(),
    			'store_id'=>get_current_blog_id(),
    			'identifier' => (string)$product->id,
    	
    			'sellable' => $product->is_purchasable(),
    			'visibility' =>$product->is_visible(),
    	
    			'stock_quantity' => $product->get_stock_quantity(),
    			'is_managing_stock' => $product->managing_stock(),
    			'is_backorders_allowed' => $product->backorders_allowed(),
    			'is_purchasable' => $product->is_purchasable(),
    			'is_in_stock' => $product->is_in_stock( ),
    			 
    			'product_status' => get_post_status($post_id),
    	);
    	
    	if(!$woocommerce_ver_below_2_1){
	    	try{
	    		$send_product['price_compare_at_price'] = $product->get_regular_price();
	    		$variable = new WC_Product_Variable($post_id);
	    		$send_product['price_min'] = $variable->get_variation_price('min');
	    		$send_product['price_max'] = $variable->get_variation_price('max');
	    		$send_product['price_min_compare_at_price'] = $variable->get_variation_regular_price('min');
	    		$send_product['price_max_compare_at_price'] = $variable->get_variation_regular_price('max');
	    	}catch (Exception $e){
	    		$send_product['price_compare_at_price'] = null;
	    		$send_product['price_min'] = null;
	    		$send_product['price_max'] = null;
	    		$send_product['price_min_compare_at_price'] = null;
	    		$send_product['price_max_compare_at_price'] = null;
	    	}
    	} else {
    		$send_product['price_compare_at_price'] = null;
    		$send_product['price_min'] = null;
    		$send_product['price_max'] = null;
    		$send_product['price_min_compare_at_price'] = null;
    		$send_product['price_max_compare_at_price'] = null;
    	}
    	
    	$send_product['description'] = self::content_filter_shortcode_with_content($send_product['description']);
    	$send_product['short_description'] = self::content_filter_shortcode_with_content($send_product['short_description']);
    	
    	$send_product['description'] = self::content_filter_shortcode($send_product['description']);
    	$send_product['short_description'] = self::content_filter_shortcode($send_product['short_description']);
    	
        return $send_product;
    }

    public function get_category_by_id($category_id){
        $product_category = get_term_by( 'id', $category_id, 'product_cat');
//         $category = get_term_by( 'id', $category_id, 'product_cat', 'ARRAY_A' );
        if ($product_category == false){
            $err_msg = "in get_category_by_id() - product_category == false";
            self::send_error_report($err_msg);
            return null;
        }
        
        $thumbnail_id = get_woocommerce_term_meta( $product_category->term_id, 'thumbnail_id', true );
        $image = wp_get_attachment_url( $thumbnail_id );
        $children = get_term_children($product_category->term_id, 'product_cat');
        
        $category = array(  
                'category_id'   => (string)$product_category->term_id,
                'name'          => $product_category->name,
                'is_active'     => $product_category->count > 0,
                'description'   => $product_category->description,
                'thumbnail'     => $image,
                'children'      => $children,
                'parent_id'     => (string)$product_category->parent,
                'url_path'      => get_term_link((int)$category_id, 'product_cat'),                    
        );
        return $category;
    }
    

    public function on_product_save($post_id){
    	$post = get_post( $post_id );
    	if ( 'product' !=  $post->post_type || get_post_status($post_id) == 'trash'){
    		return;
    	}
    	$action = 'insert';
        self::on_product_update($post_id, $action);
    }
    
    public function on_product_delete($post_id ){
        $post = get_post( $post_id );
        if ( 'product' !=  $post->post_type){
            return;
        }
        $action = 'delete';
        self::on_product_update($post_id, $action);
    }
    
    public function quantity_change_handler($order_id){
        if (!version_compare( WOOCOMMERCE_VERSION, '2.1', '<' )){
            $order = new WC_Order( $order_id );
            foreach($order->get_items() as $item){
                $product_id = $item['product_id'];
                $product = new WC_Product($product_id);
                if (!$product->managing_stock()){
                    continue;
                }
                $quantity = $product->get_stock_quantity();
                if ($item['qty'] == $quantity){
                    // update out of stock
                    self::on_product_update($product_id, 'update');
                }    
            }
        }
    } 
    
    public static function on_product_update($post_id, $action){
        $products_list = get_option('cron_product_list');
        $timestamp = wp_next_scheduled( 'instantsearchplus_cron_request_event' );
        
        if(get_option('wcis_timeframe')){
            $timeframe = get_option('wcis_timeframe');
        } else {
            $timeframe = self::CRON_EXECUTION_TIME;
        }
         
        if ($timestamp != false){	// event already scheduled
            if ($products_list){	// if there is at least one product in the list
                // checking time-stamp diff (current time - first product's time-stamp)
                $delta = time() - $products_list[0]['time_stamp'];
                 
                if (($delta > ($timeframe + self::CRON_THRESHOLD_TIME)) && !get_option('cron_in_progress')){
                    wp_clear_scheduled_hook( 'instantsearchplus_cron_request_event' ); 	// removing task from cron
                    self::execute_update_request();										// executing request for all waiting products
                    // reschedule current product to be executed by cron
                    wp_schedule_single_event(time() + $timeframe, 'instantsearchplus_cron_request_event');
                }
                self::insert_element_to_cron_list($post_id, $action, self::ELEMENT_TYPE_PRODUCT);
    
            } else {
                wp_clear_scheduled_hook('instantsearchplus_cron_request_event');
                $err_msg = "event scheduled to Cron but product's list is empty";
                self::send_error_report($err_msg);
            }
        } else {
            // $timestamp == false - event not scheduled
            if ($products_list){
                // no event in cron, but the cron's list has products -> update all products that are in the list
                if (!get_option('cron_in_progress')){
                    // if cron is currently not in progress -> update all products that are in the list
                    self::execute_update_request();
                    wp_schedule_single_event(time() + $timeframe, 'instantsearchplus_cron_request_event');
                }
                // add current product to the list and schedule cron event
                self::insert_element_to_cron_list($post_id, $action, self::ELEMENT_TYPE_PRODUCT);
                 
            } else {
                // updating product's list and activating cron event
                self::insert_element_to_cron_list($post_id, $action, self::ELEMENT_TYPE_PRODUCT);
                wp_schedule_single_event(time() + $timeframe, 'instantsearchplus_cron_request_event');
            }
        }
        
        /*
         *     $cat->count is the number of products that are under this ($cat) category.
         *     if there is only one product under $cat (meaning only this product) then update category -
         *         catches category change from active -> not active
         */
        foreach (wp_get_post_terms($post_id, 'product_cat') as $cat){
            if ($cat->count == 1){
                self::insert_element_to_cron_list($cat->term_id, 'edit', self::ELEMENT_TYPE_CATEGORY);
            }
        }
    }
    
    // element can be either product or category
    function insert_element_to_cron_list($element_id, $action, $type_of_element = self::ELEMENT_TYPE_PRODUCT){
    	if ($type_of_element == self::ELEMENT_TYPE_CATEGORY){
    		$elements_list = get_option('cron_category_list');
    	} else { 
    		$elements_list = get_option('cron_product_list');
    	} 
    	if ($elements_list){	// the list already has products
    		$is_unique = true;
    		foreach ($elements_list as $p){
    			if ($element_id == $p['element_id'] && $action == $p['action']){
    				$is_unique = false;
    				break;
    			}
    		}
    		if ($is_unique){
    			$element_node = array(
    					'element_id' 	=> $element_id,
    					'action' 		=> $action,
    					'time_stamp' 	=> time()
    			);
    			array_push($elements_list, $element_node);
    			if ($type_of_element == self::ELEMENT_TYPE_CATEGORY){
    				update_option('cron_category_list', $elements_list);
    			} else {
    				update_option('cron_product_list', $elements_list);
    			}

    		}
    	} else {	// first product in the list
    		$elements_list = array(0 =>
    				array(
    						'element_id' 	=> $element_id,
    						'action' 		=> $action,
    						'time_stamp' 	=> time()
    				)
    		);
    	    if ($type_of_element == self::ELEMENT_TYPE_CATEGORY){
    			update_option('cron_category_list', $elements_list);
    		} else {
    			update_option('cron_product_list', $elements_list);
    		};
    	}
    }

    private function schedule_sale_price_update($post_id, $sale_price_dates = null){
        if ($sale_price_dates != null && $sale_price_dates < time())
            return;
        $product_list_by_date = get_option('cron_update_product_list_by_date');
        if ($sale_price_dates != null){
            if ($product_list_by_date){
                $element_node = array(
                        'id' 	     => $post_id,
                        'time_stamp' => $sale_price_dates
                );
                array_push($product_list_by_date, $element_node);
        
            } else{
                $product_list_by_date = array(0 =>
                    				array(
                    				        'id' 	       => $post_id,
                    				        'time_stamp'   => $sale_price_dates
                    				)
                );
            } 
        } else { // removing all post's scheduled updates
            foreach ($product_list_by_date as $key => $value){
                if ($value['id'] == $post_id){
                    unset($product_list_by_date[$key]);
                }
            }
        }
        update_option('cron_update_product_list_by_date', $product_list_by_date);
    }
    
    private function send_products_batch($products, $is_retry = false){
    	$total_products 				= $products['total_products'];
    	$total_pages 					= $products['total_pages'];
    	$product_chunks 				= $products['products'];   	
    	$is_additional_fetch_required 	= $products['is_additional_fetch_required'];
    	
    	if ($total_products == 0){
    		$batch_number = 0;
    	} else {
    		$batch_number = $products['current_page'];
    	}
    	
    	$json_products = json_encode($product_chunks);
    	
    	$url = self::SERVER_URL . 'wc_install_products';
    	$args = array(
    			'body' => array( 
    					'site' 							=> get_option('siteurl'),
    					'site_id' 						=> get_option( 'wcis_site_id' ),
    					'products' 						=> $json_products,
    					'total_batches' 				=> $total_pages,
    					'wcis_batch_size' 				=> get_option( 'wcis_batch_size' ),
    					'authentication_key' 			=> get_option('authentication_key'),
    					'total_products' 				=> $total_products,
    					'batch_number' 					=> $batch_number, 
    					'is_additional_fetch_required' 	=> $is_additional_fetch_required,
    			)
    	);
    	
    	$resp = wp_remote_post( $url, $args );
    	
    	if (is_wp_error($resp) || $resp['response']['code'] != 200){
    		$err_msg = "send_products_batch request failed batch: " . $batch_number;  
    		self::send_error_report($err_msg);
    		if (!$is_retry){
    			self::send_products_batch($products, true);
    		}
    	}
    }
    
    function send_categories_as_batch($categorys_list = null){
        $category_array = array();
        if ($categorys_list != null){
            foreach($categorys_list as $key => $element){      
                if ($element['action'] == 'delete'){
                    $category = array('category_id' => $element['element_id']);
                } else {
                    $category = self::get_category_by_id($element['element_id']);     
                }   
                unset($categorys_list[$key]);
                if ($category == null){
                    continue;
                } 
                
                $category['action'] = $element['action'];    
                $category_array[] = $category;
            }
            update_option('cron_category_list', $categorys_list);
        } else {    // fetch all categories        
            $args = array(
                    'orderby'    => 'count',
                    'hide_empty' => 1,  // sending empty categories!
                    'fields'     => 'ids',
            );
            $product_category_ids = get_terms( 'product_cat', $args );
            if (!empty($product_category_ids) && !is_wp_error($product_category_ids) && count($product_category_ids) > 0){
                foreach($product_category_ids as $category_id){
                    $category = self::get_category_by_id($category_id);
                    if ($category == null){
                        $err_msg = "ERROR!!! in  send_categories_as_batch() - category == null";
                        self::send_error_report($err_msg);
                        continue;
                    }
                    $category['action'] = 'edit';
                    $category_array[] = $category;
                }
            } 
        }
        
        if ($category_array){       // if there are categories to send
            $url = self::SERVER_URL . 'wc_update_categories';
            $args = array(
                    'body' => array('site' => get_option('siteurl'),
                                    'site_id' => get_option( 'wcis_site_id' ),
                                    'categories' => json_encode($category_array),
                                    'authentication_key' => get_option('authentication_key')),
            );
            $resp = wp_remote_post( $url, $args );
            
            if (is_wp_error($resp) || $resp['response']['code'] != 200){	// != 200
                $err_msg = "ERROR!!! update category request failed (response != 200)";
                self::send_error_report($err_msg);   
            }
        }
    }
    
    function send_batches_if_unreachable($batch_num = 0){  
        if ($batch_num == 0){
            $err_msg = "site: " . get_option('siteurl') . "unreachable, sending batches...";
            self::send_error_report($err_msg);
            $batch_num = get_option('max_num_of_batches');
        }
    
        $loop = self::query_products($batch_num);
        $total_pages = $loop->max_num_pages;	// total number of batches
    
        while ($total_pages >= $batch_num){
            if (get_option('cron_send_batches_disable')){
                return;
            }
            self::push_wc_batch($batch_num);
            $batch_num++;
        }
    }
    
    private function send_product_update($post_id, $action)
    {
        $product = self::get_product_from_post($post_id);
        $product_update = array('topic'=>$action, 'product'=>$product);
        $json_product_update = json_encode($product_update); 
        $url = self::SERVER_URL . 'wc_update_products';
        
        $out_of_sync = $post_id;
        
        $args = array(
             'body' => array( 'site' => get_option('siteurl'), 
             				  'site_id' => get_option( 'wcis_site_id' ), 
             		 		  'product_update' => $json_product_update, 
        			   		  'authentication_key' => get_option('authentication_key')),
        );
        
        $resp = wp_remote_post( $url, $args );     

        if (is_wp_error($resp) || $resp['response']['code'] != 200){	// != 200    
        	$err_msg = "update product request failed (response != 200)"; 
			self::send_error_report($err_msg);    	       	
        	
        } else { 	// $resp['response']['code'] == 200

        }        

    }

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private function single_deactivate() {		
		$url = self::SERVER_URL . 'wc_update_site_state';
		
		$args = array(
				'body' => array('site' => get_option('siteurl'),
								'site_id' => get_option( 'wcis_site_id'),
								'authentication_key' => get_option('authentication_key'),
								'email' => get_option( 'admin_email'),
								'site_status' => 'deactivate' )
		);
		
		$resp = wp_remote_post( $url, $args );
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {
		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( 'WCISPlugin', plugin_dir_path( dirname( __FILE__ ) ) . 'languages/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( 'WCISPlugin', FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );
	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'assets/css/wcis_plugin.css', __FILE__ ), array(), self::VERSION );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
// 		wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery' ), self::VERSION );
        global $product;
        $script_url = 'https://acp-magento.appspot.com/js/acp-magento.js';
        $args = "mode=woocommerce&";
        $args = $args . "UUID=" . get_option('wcis_site_id') ."&";
        $args = $args . "store=" . get_current_blog_id() ."&";
        if (is_admin_bar_showing()){
        	$is_admin_bar_showing = "is_admin_bar_showing=1&";
        } else {
        	$is_admin_bar_showing = "is_admin_bar_showing=0&";
        }
        $args .= $is_admin_bar_showing;
        
        $args .= "products_per_page=" . (string)get_option('posts_per_page') . "&";
        if ($product){
            $args .= 'product_url=' . get_permalink() .'&';
            $args .= $product;
        }
        wp_enqueue_script( $this->plugin_slug . '-inject3', $script_url . '?' . $args, false);

        if (is_search() && get_option('fulltext_disabled') == false){
        	$script_url = 'https://acp-magento.appspot.com/js/wcis-results.js';
	        $args = $is_admin_bar_showing;
	        
	        if (get_option('just_created_site')){
	        	$args .= 'just_created_site=true&';
	        }else if ($this->wcis_did_you_mean_fields != null && !empty($this->wcis_did_you_mean_fields)){
	        	// did you mean injection
	        	$args .= 'did_you_mean_enabled=true&';

				$did_you_mean_fields = $this->wcis_did_you_mean_fields;
				if (array_key_exists('alternative_terms', $did_you_mean_fields)){
		        	$alternative_terms_arr = $did_you_mean_fields['alternative_terms'];
		        	
			        $did_you_mean_patams = '';
			        for ($i = 0; $i < count($alternative_terms_arr); $i++){
			        	$did_you_mean_patams .=  'did_you_mean_term' . (string)$i . '=' . urlencode($alternative_terms_arr[$i]) . '&';
			        }
			        
			        if ($did_you_mean_patams != ''){
			        	$args .= $did_you_mean_patams . '&';
			        }
				}
				if (array_key_exists('original_query', $did_you_mean_fields) && array_key_exists('fixed_query', $did_you_mean_fields)){
					$args .= 'original_query=' . urlencode($did_you_mean_fields['original_query']) . '&';
					$args .= 'fixed_query=' . urlencode($did_you_mean_fields['fixed_query']) . '&';
				}
				// clearing did you mean parameters
				$this->wcis_did_you_mean_fields = null;
	        } else if ($this->wcis_search_query != null){
	        	$args .= 'original_query=' . urlencode($this->wcis_search_query) . '&';
	        	$this->wcis_search_query = null;
	        }

	        wp_enqueue_script( $this->plugin_slug . '-fulltext', $script_url . '?' . $args, array('jquery'), self::VERSION );
        }
	}
	
	
	function filter_instantsearchplus_request($vars){
		$vars[] = 'instantsearchplus';
		$vars[] = 'instantsearchplus_parameter';
		return $vars;
	}
	
	function process_instantsearchplus_request($req){
		if (array_key_exists('instantsearchplus', $req->query_vars)){
			if ($req->query_vars['instantsearchplus'] == 'version'){
				if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ){
					$woocommerce_exists = true;
				} else { 
					$woocommerce_exists = false;
				}
				try {
					if ( ! function_exists( 'get_plugins' ) ){
						require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
					}
					$plugin_folder = get_plugins( '/' . 'woocommerce' );
					$plugin_file = 'woocommerce.php';
					if ( isset( $plugin_folder[$plugin_file]['Version'] ) ){
						$wooVer = $plugin_folder[$plugin_file]['Version'];
					} else {
						$wooVer = 'NULL';
					}
				} catch (Exception $e) {
					$wooVer = 'Error - could not get WooCommerce version';
				}
				
				if ( function_exists( 'is_multisite' ) && is_multisite() ){
					$is_multisite = "multisite";
				} else { 
					$is_multisite = "not multisite";
				}
				
				$response = array(
						'wordpress_version' 	=> get_bloginfo('version'),
						'woocommerce_version' 	=> $wooVer,
						'extension_version'		=> self::VERSION,
						'site_id' 				=> get_option('wcis_site_id'),
						'email'					=> get_option('admin_email'),
						'num_of_products'		=> wp_count_posts('product')->publish,
						'store_id'				=> get_current_blog_id(),
						'req_status'			=> 'OK',
						'woocommerce_exists'	=> $woocommerce_exists,
						'is_multisite'			=> $is_multisite,
				);
				exit(json_encode($response));
				
			} elseif ($req->query_vars['instantsearchplus'] == 'sync'){ 
				self::push_wc_products();
				status_header(200);
				exit();		
			} elseif ($req->query_vars['instantsearchplus'] == 'category_sync'){ 
			    wp_schedule_single_event(time() + self::CRON_SEND_CATEGORIES_TIME_INTERVAL, 'instantsearchplus_send_all_categories');
			    status_header(200);
			    exit();
			} elseif ($req->query_vars['instantsearchplus'] == 'get_batches'){
			    wp_clear_scheduled_hook( 'instantsearchplus_send_batches_if_unreachable' );
			    if (!get_option('cron_send_batches_disable')){
			        update_option('cron_send_batches_disable', true);
			    }
			    
				$batch_num = $req->query_vars['instantsearchplus_parameter'];			
				self::push_wc_batch($batch_num);
				status_header(200);
				exit();
			} elseif ($req->query_vars['instantsearchplus'] == 'change_timeframe'){
				$timeframe = $req->query_vars['instantsearchplus_parameter'];
				self::update_timeframe($timeframe);
				exit();
			} elseif ($req->query_vars['instantsearchplus'] == 'remove_admin_alerts'){
				delete_option('wcic_site_alert');
				delete_option('wcis_just_created_alert');
				exit();
			} elseif ($req->query_vars['instantsearchplus'] == 'check_admin_message'){
				self::check_for_alerts();
				exit();
			} elseif ($req->query_vars['instantsearchplus'] == 'change_logging_status'){
				if (!get_option('wcis_logging')) {
					update_option('wcis_logging', true);
				} else {
					delete_option('wcis_logging');
				}
				
				if (get_option('wcis_logging')) {
					$err_msg = "logging request - logging turned ON!";
				} else {
					$err_msg = "logging request - logging turned OFF!";
				}
				self::send_error_report($err_msg);
				exit();
			} elseif ($req->query_vars['instantsearchplus'] == 'disable_highlight'){
				if (get_option('wcis_enable_highlight') == false){
					update_option('wcis_enable_highlight', true);
				} else {
					delete_option('wcis_enable_highlight');
				}
			} elseif ($req->query_vars['instantsearchplus'] == 'clear_cron'){
			    delete_option('cron_product_list');
			    delete_option('cron_category_list');
			    delete_option('cron_in_progress');
			    wp_clear_scheduled_hook('instantsearchplus_cron_request_event');
			} elseif ($req->query_vars['instantsearchplus'] == 'tmp'){    
			} 
		}
	}
	
	// compatible to an old version | due to CRON request that is already scheduled
	function handle_cron_request(){
	    self::execute_update_request();
	}
	
	function execute_update_request(){	   
	    if (get_option('cron_in_progress')){
	        return;
	    }
		update_option('cron_in_progress', True);
		
		try {
    		$products_list = get_option('cron_product_list');
    		$categorys_list = get_option('cron_category_list');
    		if (!$products_list && !$categorys_list){
    			wp_clear_scheduled_hook('instantsearchplus_cron_request_event');
    			delete_option('cron_in_progress');
    			return;
    		} 
    		if ($products_list){
        		if (count($products_list) <= self::SINGLES_TO_BATCH_THRESHOLD){
        			foreach ($products_list as $key => $product_node){
        				self::send_product_update($product_node['element_id'], $product_node['action']);
        				unset($products_list[$key]);
        			}
        		} else {	// sending the products as a batch
        			self::send_cron_products_as_batch($products_list);
        			$products_list = get_option('cron_product_list');
        		}
        		
        		if (count($products_list) == 0){
        			delete_option('cron_product_list');
        			wp_clear_scheduled_hook('instantsearchplus_cron_request_event');
        		} else {
        			$err_msg = "not managed to send " . count($products_list) . " products"; 
        			self::send_error_report($err_msg);
        		}
    		}
        
    		if ($categorys_list){
        		if (count($categorys_list) >= 0){    		    
        		    self::send_categories_as_batch($categorys_list);
        		    $categorys_list = get_option('cron_category_list');
        		}
        		if (count($categorys_list) == 0){
        		    delete_option('cron_category_list');
        		    wp_clear_scheduled_hook('instantsearchplus_cron_request_event');
        		} else {
        		    $err_msg = "not managed to send " . count($categorys_list) . " categories";
        		    self::send_error_report($err_msg);
        		}
    		}
		} catch (Exception $e) {
        	$err_msg = "execute_update_request exception raised msg: ". $e->getMessage() . ", from url: " . get_option('siteurl');
        	self::send_error_report($err_msg);
		}
		
		delete_option('cron_in_progress');
	}

	
	function send_cron_products_as_batch($products_list){
		$batch_size = get_option( 'wcis_batch_size' );
		$total_num_of_products = count($products_list);
		$total_num_of_batches = ceil($total_num_of_products / $batch_size);
		$iteration = 1;
		$product_array = array();
		
		foreach ($products_list as $key => $product_node){
			$product = self::get_product_from_post($product_node['element_id']);
			$product['topic'] = $product_node['action'];	// insert/update/remove
			$product_array[] = $product;
			
			if ((($iteration % $batch_size) == 0) || ($iteration == $total_num_of_products)){		// sending the batch
				$send_products = array(
						'total_pages' 				   	=> $total_num_of_batches,
						'total_products'				=> $total_num_of_products,
						'current_page' 					=> ceil($iteration / $batch_size),
						'products'						=> $product_array,
						'is_additional_fetch_required' 	=> false,
				);
				self::send_products_batch($send_products);
				
				// clearing array
				unset($product_array);
				$product_array = array();
				unset($send_products);
			}
			unset($products_list[$key]);
			$iteration++;
		}
		update_option('cron_product_list', $products_list);
	}
	
	function send_error_report($str){
		$url = self::SERVER_URL . 'wc_error_log';
		
		$args = array(
				'body' => array( 'site' => get_option('siteurl'),
						'site_id' => get_option( 'wcis_site_id' ),
						'authentication_key' => get_option('authentication_key'),
						'err_desc' => $str),
		);
		
		$resp = wp_remote_post( $url, $args );
	}
	
	
	function send_logging_record($url_params_arr){
		$url = self::SERVER_URL . 'wc_logging_record';
		$not_needed_keys = array('s', 'submit');

		$record_array = array();
		foreach ($url_params_arr as $key => $value){
			if (in_array($key, $not_needed_keys))
				continue;
			$record_array[] = array('key' 	=> $key,
									'value' => $value);
		}

		$args = array(
				'body' => array( 'site' => get_option('siteurl'),
						'site_id' => get_option( 'wcis_site_id' ),
						'logging_record' => json_encode($record_array)),
		);
		$resp = wp_remote_post( $url, $args );
	}
	
	// FullText search Section
	function pre_get_posts_handler( $wp_query, $is_retry = false){
		if( is_search() && $wp_query->is_main_query() && !is_admin()){
			$query = $wp_query->query_vars;			
			$url_args = add_query_arg(null, null);
			
			$url_params = array();
			parse_str(add_query_arg(null, null), $url_params); 
			foreach ($url_params as $key => $value){
				if (strpos($key, '/?') === 0){ 		// first url parameter that starts with '/?'
					$new_key = str_replace('/?', '', $key);
					$url_params[$new_key] = $url_params[$key];
					unset($url_params[$key]);
				}
			}
			wp_schedule_single_event(time(), 'instantsearchplus_send_logging_record', array($url_params));
			
			if (strpos($url_args, 'min_price=') !== false && strpos($url_args, 'max_price=') !== false){
				return self::on_fulltext_disable_query($wp_query);
			}

			if (strpos($url_args, 'orderby=') !== false){
				return self::on_fulltext_disable_query($wp_query);
			}
			
			$q = (isset($query['s'])) ? $query['s'] : get_search_query();
			// cleaning search query from '\'
			$q = str_replace('\\', '', $q);
			
			$page_num = ($query['paged'] == 0) ? 1 : $query['paged'];
			$post_type = (array_key_exists('post_type', $query)) ? $query['post_type'] : 'post';
			
			// TODO: if posts_per_page < 1 set it to 10
			$results_per_page = get_option('posts_per_page');
			
			$url = self::SERVER_URL . 'wc_search';
			$args = array(
					'body' => array('s' 					=> get_option('siteurl'),
									'h' 					=> get_option('siteurl'),	
									'UUID' 					=> get_option( 'wcis_site_id' ),
									'q'						=> $q,
									'v' 					=> self::VERSION,
									'store_id' 				=> get_current_blog_id(),
									'p' 					=> $page_num,				// requested page number
									'products_per_page'		=> $results_per_page,
									'is_admin_view'			=> is_admin_bar_showing(),
					                'post_type'             => $post_type			
					),
					'timeout' => 20,
			);
			$resp = wp_remote_post( $url, $args );
			
			if (is_wp_error($resp) || $resp['response']['code'] != 200){				
				$err_msg = "/wc_search request failed is_retry: " . $is_retry;
				self::send_error_report($err_msg);
				
				if (!$is_retry){
					self::pre_get_posts_handler( $wp_query, true);
				} else {
					return self::on_fulltext_disable_query($wp_query);
				}
								
			} else {				
				$response_json = json_decode($resp['body'], true);
				
				if (array_key_exists('fulltext_disabled', $response_json)){
					return self::on_fulltext_disable_query($wp_query);
				} elseif (array_key_exists('just_created_site', $response_json)){
					update_option('just_created_site', true);
					delete_option('fulltext_disabled');
					return $wp_query;
				}
				
				if (get_option('just_created_site')){
					delete_option('just_created_site');
				}

				$product_ids = array();

				if (array_key_exists('id_list', $response_json)){
					foreach ($response_json['id_list'] as $product_id){
						$product_ids[] = $product_id;
					}
				} else {
					return self::on_fulltext_disable_query($wp_query);
				}
					
                $this->wcis_fulltext_ids = $product_ids;
				if (array_key_exists('total_results', $response_json) && $response_json['total_results'] != 0){
				    $this->wcis_total_results = $response_json['total_results'];
				} else {
				    $this->wcis_total_results = -1;
				}
				
				// did you mean section
				$wp_query->query_vars['s'] = $q;
				self::handle_did_you_mean_result($response_json);
			}
					
		} else {
			if (is_search() && !is_admin()){
				return $wp_query;
			} else {
				return self::on_fulltext_disable_query($wp_query);
			}
		}
		// full text search enable
		delete_option('fulltext_disabled');
		return $wp_query;
	}
	
	public function on_fulltext_disable_query($wp_query){
		delete_option('just_created_site');
		update_option('fulltext_disabled', true);
		return $wp_query;
	}
	
	public function handle_did_you_mean_result($response_json){
		global $wp_query;
		$did_you_mean_fields = array();
		// if original search query is different from the result's query
		if (array_key_exists('results_for', $response_json)   && 
		        $response_json['results_for'] != null         && 
		        $response_json['results_for'] != ''           &&
				!self::did_you_mean_is_same_words($wp_query->query_vars['s'], 
												  $response_json['results_for'])){

			$did_you_mean_fields['original_query'] = $wp_query->query_vars['s'];
			$wp_query->query_vars['s'] = $response_json['results_for'];
			$did_you_mean_fields['fixed_query'] = $response_json['results_for'];
		} 
		if (array_key_exists('alternatives', $response_json) && (count($response_json['alternatives']) > 0)){
			$alternative_terms = array();
			foreach($response_json['alternatives'] as $term){
				$alternative_terms[] = $term;
			}
			$did_you_mean_fields['alternative_terms'] = $alternative_terms;
		}
		if (empty($did_you_mean_fields)){
		    $this->wcis_search_query = $wp_query->query_vars['s'];
		} else { 
// 		    if there are alternatives(did you mean) or search query not equals to "result for query" (typo)
		    $this->wcis_did_you_mean_fields = $did_you_mean_fields;
		}
	}
	
	public function did_you_mean_is_same_words($original, $fixed){
		if (($original == $fixed) ||
			($fixed == null)	  ||
			($fixed == '')		  || 		
			(str_replace('\\', '', $original) == str_replace('\\', '', $fixed)) ||
			(strcasecmp($original, $fixed) == 0)		// case-insensitive comparison
		){
			return true;
		}
		
		return false;
	}
	
	
	public function posts_search_handler($search){
		if( is_search() && !is_admin() && (get_option('fulltext_disabled') == false)){			
		    $search = ''; // disable WordPress search
		}
		return $search;	
	}
	
	function post_limits_handler($limit){
		if( is_search() && (get_option('fulltext_disabled') == false)){			
			$limit = 'LIMIT 0, ' . get_option('posts_per_page');
		}
		return $limit;
	}
	
	function the_posts_handler($posts){
		if (is_search() && (get_option('fulltext_disabled') == false)){
			global $wp_query;
			
			$total_results = $this->wcis_total_results;	
			if ($total_results == -1){
				$total_results = 0;
			}
			
			$wp_query->found_posts = $total_results;			
			$wp_query->max_num_pages = ceil($total_results / get_option('posts_per_page'));
// 			$wp_query->query_vars['post_type'] = 'product';
			$wp_query->query_vars['posts_per_page'] = get_option('posts_per_page');
			
			unset($posts);
			$posts = array();
			if ($total_results > 0){
			    foreach ($this->wcis_fulltext_ids as $product_id){
					$post = get_post($product_id);
					$posts[] = $post;
				}
			}
			unset($this->wcis_fulltext_ids);
			$this->wcis_total_results = 0;
		}

		return $posts;
	}	
	
	
	// highlighting title/content/tags/excerpt according to the search terms
	function highlight_result_handler($current_text){
		if (is_search() && in_the_loop() && get_option('wcis_enable_highlight') && (get_option('fulltext_disabled') == false)){
			global $wp_query;
			$query = $wp_query->query_vars;
			
			$search_terms = preg_replace('!\s+!', ' ', $query['s']);
			$search_terms = explode(' ', $search_terms);
			
			foreach ($search_terms as $term){
				$current_text = preg_replace('/(' . $term . ')/i',
						'<span class="wcis_isp_marked_word">$1</span>',
						$current_text
				);
			}

			$current_text = '<span class="wcis_isp_text_content">' .  $current_text . '</span>';
		}
		
		return $current_text;
	}
	// FullText search Section end

	function admin_init_handler(){
		if ( isset($_GET['instantsearchplus']) && $_GET['instantsearchplus'] == 'remove_over_capacity_alert' ){
			delete_option('wcic_site_alert');
			wp_redirect(remove_query_arg('instantsearchplus'), 301);
		} else if ( isset($_GET['instantsearchplus']) && $_GET['instantsearchplus'] == 'remove_just_created_alert' ){
			delete_option('wcis_just_created_alert');
			wp_redirect(remove_query_arg('instantsearchplus'), 301);
		}
	}
	
	// admin quota exceeded message
	function show_admin_message(){			
		if (is_admin()){
			$dashboard_url = 'http://woo.instantsearchplus.com/wc_dashboard';
			$dashboard_url .= '?site_id=' . get_option( 'wcis_site_id' );
			$dashboard_url .= '&authentication_key=' . get_option('authentication_key');
			$dashboard_url .= '&new_tab=1';
			$dashboard_url .= '&v=' . WCISPlugin::VERSION;
			
			if (get_option('wcis_just_created_alert')){
				$just_created_text = '';
				
				echo '<div class="updated"><p>';
				printf( __( '<b>InstantSearch+ for WooCommerce is installed.</b> Please <b><u><a href="%1$s" target="_blank">Select your service plan</a></u></b> to get started <a href="%2$s" style="float:right"><u>Hide</u></a>', 'WCISPlugin' ), 
							$dashboard_url, 
							add_query_arg('instantsearchplus', 'remove_just_created_alert'));
				echo "</p></div>";
				
			}
			if (get_option('wcic_site_alert')){
				$alert = get_option('wcic_site_alert');
				$msg = $alert['alerts'][0]['message'];
				
				echo '<div class="error" style="background-color:#fef7f1;"><p>';
				printf(__('<b> %1$s </b> | <b><a href="%2$s" target="_blank">Upgrade now</a></b> to enable back <a href="%3$s" style="float:right">Hide</a>', 'WCISPlugin'), 
							$msg, 
							$dashboard_url,
							add_query_arg('instantsearchplus', 'remove_over_capacity_alert'));
				echo "</p></div>";
			}
		}	
	}
	
	function check_for_alerts($is_from_page_load = false){
		$url = self::SERVER_URL . 'ext_info';
		$args = array(
			'body' => array('site_id' 				=> get_option( 'wcis_site_id' ),
							'version' 				=> self::VERSION,
					),
					'timeout' => 10,
		);	
		$resp = wp_remote_post( $url, $args );
		
		if (is_wp_error($resp) || $resp['response']['code'] != 200){	
			$err_msg = "check_for_alerts failed";
			self::send_error_report($err_msg); 
		} else {	
			$response_json = json_decode($resp['body'], true);
			if (!$is_from_page_load){
				if (!empty($response_json['alerts'])){
					update_option('wcic_site_alert', $response_json);
				} else { 
					if (get_option('wcic_site_alert')){
						delete_option('wcic_site_alert');
					}
				}
			}
				
			if (array_key_exists('timeframe', $response_json)){
				self::update_timeframe($response_json['timeframe']);
			}
		}
	}
	
	function update_timeframe($new_timeframe){	
		if (get_option('wcis_timeframe') && ($new_timeframe < get_option('wcis_timeframe'))){	// on subscription upgrade
			wp_clear_scheduled_hook('instantsearchplus_cron_request_event');
			if (get_option('cron_product_list')){
				self::execute_update_request();	
			}		
		}
		update_option('wcis_timeframe', $new_timeframe);
	}
	
	
	function add_woocommerce_integrations_handler($integrations){
		require_once( plugin_dir_path( __FILE__ ) . 'wcis_integration.php' );
		$integrations[] = 'WCISIntegration';
		return $integrations;
	}
	

	function content_filter_shortcode($content){		
		$pattern = '/\[(.+?)[^\]]*\](.*?)\[\/\\1\]/s';
		while(preg_match($pattern, $content)){
			$content = preg_replace($pattern, '$2', $content);
		}

// 		global $shortcode_tags;
// 		if ($content != ''){
// 			foreach ($shortcode_tags as $shortcode_name => $shortcode_function){
// 				$content = preg_replace ('/\['. (string)$shortcode_name .'[^\]]*\](.*?)\[\/'. (string)$shortcode_name .'\]/', '$1', $content);
// 			}				 
// 		}

		// removing all content that looks like shortcode 
		$pattern = '/\[[^\]]+\]/s';
		while(preg_match($pattern, $content)){
			$content = preg_replace($pattern, '', $content);
		}
		
		return $content;
	}
	
	function content_filter_shortcode_with_content($content){
		$const_shortcode = array("php", "insert_php");
		if ($content != ''){
			foreach ($const_shortcode as $filter){
				$content = preg_replace ('/\['. (string)$filter .'[^\]]*\](.*?)\[\/'. (string)$filter .'\]/', '', $content);
			}
		}
		return $content;
	}
	
	
	function on_scheduled_sales(){
	    $product_list_by_date = get_option('cron_update_product_list_by_date');
	    if (!$product_list_by_date){
	        return;
	    }
	    
	    foreach ($product_list_by_date as $key => $value){
	        if ($value['time_stamp'] <= time()){
	            self::on_product_update($value['id'], 'update');
	            unset($product_list_by_date[$key]);
	        }
	    }
	    update_option('cron_update_product_list_by_date', $product_list_by_date);
	}
}
?>