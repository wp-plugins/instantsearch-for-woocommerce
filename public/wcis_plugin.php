<?php
/**
 * Plugin Name.
 *
 * @package   WCISPlugin
 * @author    InstantSearchPlus
 * @license   GPL-2.0+
 * @link      http://www.instantsearchplus.com
 * @copyright 2014 InstantSearchPlus
 */

/**
 * @package WCISPlugin
 * @author  InstantSearchPlus <support@instantsearchplus.com>
 */
class WCISPlugin {      
//     const SERVER_URL = 'http://woo.instantsearchplus.com/';
	const SERVER_URL = 'http://0-1vk.acp-magento.appspot.com/';

	const VERSION = '1.0.10';
	
	const RETRIES_LIMIT = 3;
	
	const RETRY_PRODUCT_ARRAY_MAX_COUNT = 20;
	
	// cron const variables
	const CRON_THRESHOLD_TIME 				= 600; 	// 60 * 10 -> 10 minutes
	const CRON_EXECUTION_TIME 				= 300; 	// 30 * 10 -> 5 minutes
	const SINGLES_TO_BATCH_THRESHOLD		= 10;	// if more then 10 products send as batch

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
	
	protected $is_out_of_sync = false;   

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
        add_action( 'save_post', array( $this, 'on_product_save' ));
        add_action('trashed_post', array( $this, 'on_product_delete' ));
        
            // status change!!!
//         add_action( 'transition_post_status', array($this, 'on_new_post'), 10, 3 ); 
        
                 
            // profile changes (url/email update handler)
//             add_action( 'profile_update', array( $this, 'on_profile_update') );
//             add_action('admin_init', array( $this, 'on_profile_update'));

        // Load public-facing style sheet and JavaScript.
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
            
        add_action('parse_request', array($this, 'process_instantsearchplus_request'));
        add_filter('query_vars', array($this, 'filter_instantsearchplus_request'));   

        // cron
        add_action( 'instantsearchplus_cron_request_event', array( $this, 'handle_cron_request' ) );    
        
        // FullText search
        add_filter( 'posts_search', array( $this, 'posts_search_handler' ) );
        add_action( 'pre_get_posts', array( $this, 'pre_get_posts_handler' ) );
        add_filter( 'post_limits', array( $this, 'post_limits_handler' ) );
        add_filter( 'the_posts', array( $this, 'the_posts_handler' ) );
        
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
	public static function activate( $network_wide )
	{			
		$err_msg = "activate function triggered";
		self::send_error_report($err_msg);
		
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
	public static function deactivate( $network_wide ) {		
		wp_clear_scheduled_hook( 'instantsearchplus_cron_request_event' );
		delete_option('cron_product_list');
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
		if ( ! current_user_can( 'activate_plugins' ) )
			return;
	
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
		
		delete_option('cron_product_list');
		
		delete_option('is_out_of_sync');
		delete_option('is_out_of_sync_install');
		delete_option('is_out_of_sync_all_products');
		delete_option('is_out_of_sync_product');
		delete_option('retries_limit_counter');
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
	private static function single_activate($is_retry = false) {
		if (get_option('is_activation_triggered'))
			return;
		update_option('is_activation_triggered', true);
			
		$url = self::SERVER_URL . 'wc_install';  
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
	        if (function_exists( 'is_multisite' ) && is_multisite())
	        	$is_multisite_on = true;
	        else
	        	$is_multisite_on = false;
	
	        $json_multisite = json_encode($multisite_array);
		} catch (Exception $e){
        	$is_multisite_on = false;
		}
            // end multisite
            
        $args = array(
        	'body' => array('site' => get_option('siteurl'), 
        				'email' => get_option( 'admin_email' ), 
        				'product_count' => wp_count_posts('product')->publish,
        				'store_id' => get_current_blog_id(),
        				'is_multisite'	=> $is_multisite_on,
        				'multisite_info'	=> $json_multisite
        	)		
        );
            
        $resp = wp_remote_post( $url, $args );
			
        if (is_wp_error($resp) || $resp['response']['code'] != 200)
        {             	
        	$err_msg = "install req returned with an error code, sending retry install request: " . $is_retry;
        	try{
            	if (is_wp_error($resp))
            		$err_msg = $err_msg . " - error msg: " . $resp->get_error_message();
        	} catch (Exception $e) {}
            	
        	self::send_error_report($err_msg);
       		if (!$is_retry)
            	self::single_activate(true);

        } else {	// $resp['response']['code'] == 200
            	// the server returns site id in the body of the response, save it in the options
        	try{
	        	$response_json = json_decode($resp['body']);
	        	if ($response_json == Null){
	            	$err_msg = "After install json_decode returned null";
	            	self::send_error_report($err_msg);
	            	if (!$is_retry)
	            		self::single_activate(true);
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
            	
            	// TODO: remove it
            	update_option('do_not_send_retries', true);
	            	
			} catch (Exception $e){
            	$err_msg = "After install internal exception raised msg: ". $e->getMessage();
            	self::send_error_report($err_msg);
            }
            	//self::build_categories();
            self::push_wc_products();
		}
	}
    
	
    private static $category2id = array();
    
    private static function build_categories()
    {
        $args = array(
                 'number'     => $number,
                 'orderby'    => $orderby,
                 'order'      => $order,
                 'hide_empty' => $hide_empty,
                 'include'    => $ids
             );
        
        $product_categories = get_terms( 'product_cat', $args );
        $count = count($product_categories);
        $categories_to_send = array();
        if ( $count > 0 ){
            foreach ( $product_categories as $product_category ) {
                // retrieve the thumbnail
                $thumbnail_id = get_woocommerce_term_meta( $product_category->term_id, 'thumbnail_id', true );
                $image = wp_get_attachment_url( $thumbnail_id );
                $children =get_term_children($product_category->term_id, 'product_cat');
                
                $categories_to_send[] = array('category_id'=>$product_category->term_id, 
                                               'parent_id'=>$product_category->parent,
                                               'name'=>$product_category->name,
                                               'url_path'=> get_term_link($product_category, $product_categories),
                                               'is_active'=> $product_category->count > 0,
                                               'description'=>$product_category->description,
                                               'thumbnail'=>$image,
                                               'children'=>$children
                                              );
                
                self::$category2id[$product_category->name] = $product_category->term_id;
            }

        }
    }
    
    private static function query_products($page = 1) {
		// set base query arguments
		$query_args = array(
			'fields'      => 'ids',
			'post_type'   => 'product',
			'post_status' => 'publish',
			//'post_parent' => 0,
            'posts_per_page' => get_option( 'wcis_batch_size' ), 
			'meta_query' => array(),
            'paged' => $page,
		);

		return new WP_Query( $query_args );
	}
    
    private static function push_wc_products()
    {
        $err_msg = "about to get batches...";
        self::send_error_report($err_msg);
        /**
         * Check if WooCommerce is active
         **/
        try{
	        if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	        	try {
	        		$err_msg = "woocommerce checked";
	        		self::send_error_report($err_msg);
	        		
		            $product_array = array();
		            $loop = self::query_products();
		            $page        = $loop->get( 'paged' );	// batch number
					$total       = $loop->found_posts;		// total number of products
					$total_pages = $loop->max_num_pages;	// total number of batches
		            global $blog_id;
		            $max_num_of_batches = get_option('max_num_of_batches');
		            $is_additional_fetch_required = false;
	
		            $err_msg = "before page loop";
		            self::send_error_report($err_msg);
		            
		            while ($page <= $total_pages)
		            {
		                while ( $loop->have_posts() ) 
		                {
		                    $loop->the_post(); 
		                    $product = self::get_product_from_post(get_the_ID());
		                    $product_array[] = $product;
		                }
		                
		                if($max_num_of_batches == $page && $total_pages > $max_num_of_batches)
		                	// need to schedule request from server side to send the rest of the batches after activation ends
		                	$is_additional_fetch_required = true;
		                
		                $send_products = array(
		                		'total_pages' 				   	=> $total_pages, 
		                		'total_products'				=> $total, 
		                		'current_page' 					=> $page, 
		                		'products'						=> $product_array,
		                		'is_additional_fetch_required' 	=> $is_additional_fetch_required,
		                );
		                
		                $err_msg = "about to send batche...";
		                self::send_error_report($err_msg);
		                
		                self::send_products_batch($send_products);
		                
		                // clearing array
		                unset($product_array);	
		                $product_array = array();
		                unset($send_products);
		                
		                $page = $page + 1;
		                
		                // too many products on activation, will get the rest of the products, by server request, after the activation is done
		                if ($is_additional_fetch_required)
		                	break;
		                
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
		        	if (is_plugin_active( 'woocommerce/woocommerce.php'))
		        		$is_woo = 'true';
		        	else 
		        		$is_woo = 'false';
	        	} catch (Exception $e){
	        		$is_woo = 'false (Exception)';
	        	}
	        	
	        	$err_msg = "can't find active plugin of woocommerce, alternative check: " . $is_woo;
	        	self::send_error_report($err_msg);
	        }
        } catch (Exception $e){
        	$err_msg = "can't find active plugin of woocommerce, alternative check: " . $is_woo;
        	self::send_error_report($err_msg);
        }
    }
    
    private static function puch_wc_batch($batch_num){
    	$loop = self::query_products($batch_num);
    	$product_array = array();
    	$total       = $loop->found_posts;		// total number of products
    	$total_pages = $loop->max_num_pages;	// total number of batches
    	while ( $loop->have_posts() )
    	{
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
    	 
    	self::send_products_batch($send_products);	
    }
    
    
    private static function get_product_from_post($post_id)
    {
        $product = new WC_Product( $post_id );
        //$post_categories = wp_get_post_categories( $post_id );
        //$categories = get_the_category();
        
        $thumbnail = $product->get_image();
        if ($thumbnail)
        {
            preg_match('/<img(.*)src(.*)=(.*)"(.*)"/U', $thumbnail, $result);
            $thumbnail = array_pop($result);
        }
        
        $send_product = array('product_id' => $product->id,
                              'currency' => get_woocommerce_currency(),   
                              'price' =>$product->get_price(),
                              'sale_price'=>$product->get_sale_price(),
                              'url' =>$product->get_permalink(),
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
        
        try{
        	$variable = new WC_Product_Variable($post_id);
        	$send_product['price_min'] = $variable->get_variation_price('min');
        	$send_product['price_max'] = $variable->get_variation_price('max');
        }catch (Exception $e){
        	$send_product['price_min'] = null;
        	$send_product['price_max'] = null;
        }		 
        
        return $send_product;
    }
    

    public static function on_product_save($post_id){
    	$post = get_post( $post_id );
    	if ( 'product' !=  $post->post_type || get_post_status($post_id) == 'trash')
    		return;
    	$action = 'insert';
 
    	$products_list = get_option('cron_product_list');
    	$timestamp = wp_next_scheduled( 'instantsearchplus_cron_request_event' );
    	
		if ($timestamp != false){	// event already scheduled
			if ($products_list){	// if there is at least one product in the list
				// checking time-stamp diff (current time - first product's time-stamp)
				$delta = time() - $products_list[0]['time_stamp'];
					
				if (($delta > self::CRON_THRESHOLD_TIME) && !get_option('cron_in_progress')){
					wp_clear_scheduled_hook( 'instantsearchplus_cron_request_event' ); 	// removing task from cron
					self::execute_update_request();										// executing request for all waiting products
					// reschedule current product to be executed by cron
					wp_schedule_single_event(time() + self::CRON_EXECUTION_TIME, 'instantsearchplus_cron_request_event');
				}
				self::insert_product_to_cron_list($post_id, $action);
				
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
    				wp_schedule_single_event(time() + self::CRON_EXECUTION_TIME, 'instantsearchplus_cron_request_event');
    			}
    			// add current product to the list and schedule cron event
    			self::insert_product_to_cron_list($post_id, $action);
    			
    		} else {
    			// updating product's list and activating cron event
    			self::insert_product_to_cron_list($post_id, $action);
    			wp_schedule_single_event(time() + self::CRON_EXECUTION_TIME, 'instantsearchplus_cron_request_event');			
    		}
    	}  	
//     	self::send_product_update($post_id, $action);
    }
    
    function insert_product_to_cron_list($post_id, $action){
    	$products_list = get_option('cron_product_list');
    	if ($products_list){	// the list already has products
    		$is_unique = true;
    		foreach ($products_list as $p){
    			if ($post_id == $p['post_id'] && $action == $p['action']){
    				$is_unique = false;
    				break;
    			}
    		}
    		if ($is_unique){
    			$product_node = array(
    					'post_id' 		=> $post_id,
    					'action' 		=> $action,
    					'time_stamp' 	=> time()
    			);
    			array_push($products_list, $product_node);
    			update_option('cron_product_list', $products_list);
    		}
    	} else {	// first product in the list
    		$products_list = array(0 =>
    				array(
    						'post_id' 		=> $post_id,
    						'action' 		=> $action,
    						'time_stamp' 	=> time()
    				)
    		);
    		update_option('cron_product_list', $products_list);
    	}
    }
    
    /*
     * update existing product
     */
    public static function on_product_update($post_id )
    {    	
    	$post = get_post( $post_id );
        if ( 'product' !=  $post->post_type || get_post_status($post_id) == 'trash'){// or  "publish" != $post->post_status ) {
            return;
        }

        $form_data = $_POST;
        $original_post_status = $form_data['original_post_status'];
        if ($original_post_status == 'publish')
        {
            $action = 'update';
        }
        else
        {
            $action = 'insert';
        }
        
        self::send_product_update($post_id, $action);
    }
    
    public static function on_product_delete($post_id )
    {
        $post = get_post( $post_id );  
        if ( 'product' !=  $post->post_type){
            return;
        }
        
        $action = 'delete';
        self::send_product_update($post_id, $action);
    }
    
    private static function send_products_batch($products, $is_retry = false){
    	$total_products 				= $products['total_products'];
    	$total_pages 					= $products['total_pages'];
    	$product_chunks 				= $products['products'];   	
    	$is_additional_fetch_required 	= $products['is_additional_fetch_required'];
    	
    	if ($total_products == 0)
    		$batch_number = 0;
    	else
    		$batch_number = $products['current_page'];
    	
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
//     		update_option('is_out_of_sync', true);
//     		update_option('is_out_of_sync_all_products', true);
    		$err_msg = "send_products_batch request failed batch: " . $batch_number;  
    		self::send_error_report($err_msg);
    		if (!$is_retry)
    			self::send_products_batch($products, true);
    	} else {
    		update_option('is_out_of_sync', false);
    		update_option('is_out_of_sync_all_products', false);
    		delete_option('is_out_of_sync_product');
    		delete_option('retries_limit_counter');
    	}
    }
    
//     private static function send_products($products)
//     {
//         $batch_size =  get_option( 'wcis_batch_size' );
//         $total_products = $products['total_products'];
//         $total_pages = $products['total_pages'];
//         $all_products = $products['products'];
//         $product_chunks = array_chunk($all_products, $batch_size);
//         $total_batches = count($product_chunks);
//         if ($total_batches == 0)
//         	$batch_number = 0;
//         else
//         	$batch_number = 1;
        
//         $is_request_failed = false; 

//         foreach ($product_chunks as $chunk) 
//         {
//         	try {
//             	$json_products = json_encode($chunk);
//         	} catch (Exception $e) {
//             	$err_msg = "send_products exception raised by json_encode, msg: " . $e->getMessage();
//             	self::send_error_report($err_msg);
//             }
//             $url = self::SERVER_URL . 'wc_install_products';
        
//             $args = array(
//                  		'body' => array( 'site' => get_option('siteurl'), 
//                  		'site_id' => get_option( 'wcis_site_id' ), 
//                  		'products' => $json_products, 
//                  		'total_batches' => $total_batches, 
//             			'wcis_batch_size' => get_option( 'wcis_batch_size' ), 
//                  		'authentication_key' => get_option('authentication_key'), 
//                  		'total_products' => $total_products,
//             			'batch_number' => $batch_number,
//                  		'total_pages' => $total_pages)
//             );
//             $resp = wp_remote_post( $url, $args );
//             $batch_number++;
            
//             if (is_wp_error($resp) || $resp['response']['code'] != 200)
//             	$is_request_failed = true;
//         }
//         if ($is_request_failed){	// on failure
//         	update_option('is_out_of_sync', true);
//         	update_option('is_out_of_sync_all_products', true);
//         } else {					// on success
//         	update_option('is_out_of_sync', false);
//         	update_option('is_out_of_sync_all_products', false);
//         	delete_option('is_out_of_sync_product');
//         	delete_option('retries_limit_counter');
//         }
//         //$resp = wp_remote_get( $url, $args );
        
//         if ($total_batches == 0){
//         	$err_msg = "no products to send, count(product_chunks) is 0";
//         	self::send_error_report($err_msg);
//         }
//     }
    
    private static function send_categories($categories)
    {
    }
    
    private static function send_product_update($post_id, $action)
    {
        $product = self::get_product_from_post($post_id);
        $product_update = array('topic'=>$action, 'product'=>$product);
        $json_product_update = json_encode($product_update); 
        $url = self::SERVER_URL . 'wc_update_products';
        
//         $is_out_of_sync = get_option('is_out_of_sync');
        
        $out_of_sync = $post_id;
        
        $args = array(
             'body' => array( 'site' => get_option('siteurl'), 
             				  'site_id' => get_option( 'wcis_site_id' ), 
             		 		  product_update => $json_product_update, 
        			   		  'authentication_key' => get_option('authentication_key')),
        );
        
        $resp = wp_remote_post( $url, $args );     

        if (is_wp_error($resp) || $resp['response'][code] != 200){	// != 200    
        	$err_msg = "update product request failed (response != 200)"; 
			self::send_error_report($err_msg);    	       	
        	
        } else { 	// $resp['response'][code] == 200

        }        

    }

	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {		
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

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

	}

	/**
	 * Register and enqueue public-facing style sheet.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style( $this->plugin_slug . '-plugin-styles', plugins_url( 'assets/css/public.css', __FILE__ ), array(), self::VERSION );
	}

	/**
	 * Register and enqueues public-facing JavaScript files.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_slug . '-plugin-script', plugins_url( 'assets/js/public.js', __FILE__ ), array( 'jquery' ), self::VERSION );
        global $product;
        $script_url = 'https://acp-magento.appspot.com/js/acp-magento.js';
        $args = "?mode=woocommerce&";
        $args = $args . "UUID=" . get_option('wcis_site_id') ."&";
        $args = $args . "store=" . get_current_blog_id() ."&";
        if ($product)
        {
            $args .= 'product_url=' . get_permalink() .'&';
            $args .= $product;
        }
        //wp_enqueue_script( $this->plugin_slug . '-inject1' . $args, $script_url, false, $args);
        wp_enqueue_script( $this->plugin_slug . '-inject3' . $args, $script_url . '?' . $args, false);
	}
	
	
	function filter_instantsearchplus_request($vars){
		$vars[] = 'instantsearchplus';
		$vars[] = 'instantsearchplus_parameter';
		return $vars;
	}
	
	function process_instantsearchplus_request($req){
		if (array_key_exists('instantsearchplus', $req->query_vars)){
			if ($req->query_vars['instantsearchplus'] == 'version'){
				if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) )
					$woocommerce_exists = true;
				else 
					$woocommerce_exists = false;
				try {
					if ( ! function_exists( 'get_plugins' ) )
						require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
					$plugin_folder = get_plugins( '/' . 'woocommerce' );
					$plugin_file = 'woocommerce.php';
					if ( isset( $plugin_folder[$plugin_file]['Version'] ) )
						$wooVer = $plugin_folder[$plugin_file]['Version'];
					else
						$wooVer = 'NULL';
				} catch (Exception $e) {
					$wooVer = 'Error - could not get WooCommerce version';
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
						'woocommerce_exists'	=> $woocommerce_exists
				);
				exit(json_encode($response));
				
			} elseif ($req->query_vars['instantsearchplus'] == 'sync'){
				self::push_wc_products();
				status_header(200);
				exit();
			} elseif ($req->query_vars['instantsearchplus'] == 'reset_retries'){
				// reseting all retries attempts
				delete_option('is_out_of_sync_product');
				delete_option('is_out_of_sync_install');
				delete_option('is_out_of_sync_all_products');				
				update_option('is_out_of_sync', false);	
				delete_option('do_not_send_retries');			
			} elseif ($req->query_vars['instantsearchplus'] == 'stop_retries'){
				// do not send retries anymore
				update_option('do_not_send_retries', true);
				delete_option('is_out_of_sync_product');
				delete_option('is_out_of_sync_install');
				delete_option('is_out_of_sync_all_products');				
				update_option('is_out_of_sync', false);				
			} elseif ($req->query_vars['instantsearchplus'] == 'get_batches'){
				$batch_num = $req->query_vars['instantsearchplus_parameter'];			
				self::puch_wc_batch($batch_num);
				status_header(200);
				exit();
			}
		}
	}
	
	function process_retry_request(){
		if (!get_option('is_out_of_sync') && get_option('do_not_send_retries'))
			return;
		
// 		check for retries limitation
		$limit = get_option('retries_limit_counter');
		if ($limit){
			if ($limit < self::RETRIES_LIMIT){
				$limit++;
				update_option('retries_limit_counter', $limit);
			} else {	// retried too many times
				delete_option('retries_limit_counter');
				update_option('is_out_of_sync', false);
				delete_option('is_out_of_sync_install');
				delete_option('is_out_of_sync_all_products');
				delete_option('is_out_of_sync_product');
				return;
			}
		} else 
			update_option('retries_limit_counter', 1);
		
		if (get_option('is_out_of_sync_install'))
			self::single_activate();
		else if (get_option('is_out_of_sync_all_products'))
			self::push_wc_products();
		else if (get_option('is_out_of_sync_product')){
			$products_array = get_option('is_out_of_sync_product');
			foreach ($products_array as $key => $product_desc){
				$product = self::get_product_from_post($product_desc['post_id']);
				$product_update = array('topic'=>$product_desc['action'], 'product'=>$product);
				$json_product_update = json_encode($product_update);
				$url = self::SERVER_URL . 'wc_update_products';
				$args = array(
						'body' => array( 'site' => get_option('siteurl'), 
										 'site_id' => get_option( 'wcis_site_id' ), 
										 product_update => $json_product_update, 
										 'authentication_key' => get_option('authentication_key')),
				);

				$resp = wp_remote_post( $url, $args );
		        if (is_wp_error($resp) || $resp['response'][code] != 200)
		        	break;
		        else  // retry succeeded - removing from retries array;
		        	unset($products_array[$key]);
			}
			
			if (count($products_array) == 0){
				delete_option('is_out_of_sync_product');
				update_option('is_out_of_sync', false);
				delete_option('retries_limit_counter');
			} else {
				update_option('is_out_of_sync_product', $products_array);
			}	
		}				
	}
	
	function handle_cron_request(){
		update_option('cron_in_progress', True);
		$products_list = get_option('cron_product_list');
		if (!$products_list){
			wp_clear_scheduled_hook('instantsearchplus_cron_request_event');
			delete_option('cron_in_progress');
			return;
		} 
		
		if (count($products_list) <= self::SINGLES_TO_BATCH_THRESHOLD){
			foreach ($products_list as $key => $product_node){
				self::send_product_update($product_node['post_id'], $product_node['action']);
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
		delete_option('cron_in_progress');
	}
	
	// TODO: merge with handle_cron_request()
	function execute_update_request(){
		$products_list = get_option('cron_product_list');
		
		if (count($products_list) <= self::SINGLES_TO_BATCH_THRESHOLD){
			foreach ($products_list as $key => $product_node){
				self::send_product_update($product_node['post_id'], $product_node['action']);
				unset($products_list[$key]);
			}
		} else {	// sending the products as a batch
			self::send_cron_products_as_batch($products_list);
			$products_list = get_option('cron_product_list');
		}
		
		if (count($products_list) == 0){
			delete_option('cron_product_list');
			$err_msg = "sent products succesfully outside the cron handler";
			self::send_error_report($err_msg);
		} else {
			$err_msg = "not managed to send " . count($products_list) . " products outside the cron handler";
			self::send_error_report($err_msg);
		}	
	}
	
	function send_cron_products_as_batch($products_list){
		$batch_size = get_option( 'wcis_batch_size' );
		$total_num_of_products = count($products_list);
		$total_num_of_batches = ceil($total_num_of_products / $batch_size);
		$iteration = 1;
		
// 		$err_msg = "in send_cron_products_as_batch num of batches: " . $total_num_of_batches . ", total_products: " . $total_num_of_products;
// 		self::send_error_report($err_msg);
		
		foreach ($products_list as $key => $product_node)
		{
			$product = self::get_product_from_post($product_node['post_id']);
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
	
	
	// FullText search
	function pre_get_posts_handler( $wp_query, $is_retry = false){
		if( is_search() && $wp_query->is_main_query() && !is_admin()){
			$query = $wp_query->query_vars;
			$url_args = add_query_arg();
			if (strpos($url_args, 'min_price=') !== false && strpos($url_args, 'max_price=') !== false)
				return $wp_query;
			if (strpos($url_args, 'orderby=') !== false)
				return $wp_query;

			if (isset($query['s']) ) 
				$q = $query['s'];
			else
				$q = get_search_query();
			
			if ($query['paged'] == 0)
				$page_num = 1;
			else 
				$page_num = $query['paged'];
			
			$url = self::SERVER_URL . 'wc_search';
			$args = array(
					'body' => array('s' 					=> get_option('siteurl'),
									'h' 					=> get_option('siteurl'),	
									'UUID' 					=> get_option( 'wcis_site_id' ),
									'q' 					=> get_search_query(),
									'v' 					=> self::VERSION,
									'store_id' 				=> get_current_blog_id(),
									'p' 					=> $page_num,				// requested page number
									'products_per_page'		=> get_option('posts_per_page')				
					),
					'timeout' => 20,
			);
			
			$resp = wp_remote_post( $url, $args );
			if (is_wp_error($resp) || $resp['response']['code'] != 200){				
				$err_msg = "/wc_search request failed is_retry: " . $is_retry;
				self::send_error_report($err_msg);
				
				if (!$is_retry)
					self::pre_get_posts_handler( $wp_query, true);
								
			} else {				
				$response_json = json_decode($resp['body'], true);

				$product_ids = array();
							
				foreach ($response_json['id_list'] as $product_id)
					$product_ids[] = $product_id;

				update_option('wcis_fulltext_ids', $product_ids);
				if ($response_json['total_results'] != 0)
					update_option('wcis_total_results', $response_json['total_results']);
				else
					update_option('wcis_total_results', -1);
			}
					
		}
		return $wp_query;
	}
	
	public function posts_search_handler($search){
		if( is_search() && ! is_admin() && get_option('wcis_fulltext_ids')){
			$search = ''; // disable WordPress search
		}
		return $search;	
	}
	
	function post_limits_handler($limit){
		if( is_search() && get_option('wcis_fulltext_ids'))			
			$limit = 'LIMIT 0, ' . get_option('posts_per_page');
		return $limit;
	}
	
	function the_posts_handler($posts){
		if (is_search() && (get_option('wcis_fulltext_ids') || get_option('wcis_total_results') == -1)){
			
			global $wp_query;			
			$total_results = get_option('wcis_total_results');	
			if ($total_results == -1)
				$total_results = 0;
			$wp_query->found_posts = $total_results;			
			$wp_query->max_num_pages = ceil($total_results / get_option('posts_per_page'));
			$wp_query->query_vars['post_type'] = 'product';
			
			$fulltext_ids = get_option('wcis_fulltext_ids');

// 			if (count($fulltext_ids) <= 0){
// 				delete_option('wcis_fulltext_ids');
// 				delete_option('wcis_total_results');
// 				return $posts;
// 			}
			
			unset($posts);
			$posts = array();
			if ($total_results > 0){
				foreach ($fulltext_ids as $product_id){
					$post = get_post($product_id);
					$posts[] = $post;
				}
			}
		}

		delete_option('wcis_fulltext_ids');
		delete_option('wcis_total_results');
		return $posts;
	}	
	// FullText search end
		
}
?>