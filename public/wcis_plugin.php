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
    const SERVER_URL = 'http://woo.instantsearchplus.com/';

	const VERSION = '1.0.0';
	
	const RETRIES_LIMIT = 3;
	
	const RETRY_PRODUCT_ARRAY_MAX_COUNT = 20;

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
        
        //add_action( 'wp_insert_post', array( $this, 'on_product_insert' ) );
        add_action( 'publish_post', array( $this, 'on_product_update' ) );
        add_action( 'save_post', array( $this, 'on_product_update' ) );
        add_action('trashed_post', array( $this, 'on_product_delete' ) );
            
            // profile changes (url/email update handler)
//             add_action( 'profile_update', array( $this, 'on_profile_update') );
//             add_action('admin_init', array( $this, 'on_profile_update'));

        // Load public-facing style sheet and JavaScript.
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
            
        add_action('parse_request', array($this, 'process_instantsearchplus_request'));
        add_filter('query_vars', array($this, 'filter_instantsearchplus_request'));
            
            // status change!!!
//             add_action( 'transition_post_status', array($this, 'on_post_unpublished'), 10, 3 );          
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

            if ( function_exists( 'is_multisite' ) && is_multisite() ) {

                    if ( $network_wide  ) {

                            // Get all blog ids
                            $blog_ids = self::get_blog_ids();

                            foreach ( $blog_ids as $blog_id ) {

                                    switch_to_blog( $blog_id );
                                    self::single_activate();
                            }

                            restore_current_blog();

                    } else {
                            self::single_activate();
                    }

            } else {
                    self::single_activate();
            }
            
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
		
		// TODO: verifie
// 		check_admin_referer();
	
		// Important: Check if the file is the one
		// that was registered during the uninstall hook.
		if ( __FILE__ != WP_UNINSTALL_PLUGIN )
			return;
	
		// deleting the database
		delete_option('wcis_site_id');
		delete_option('wcis_batch_size');
		delete_option('authentication_key');
		
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

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();
	}
    
	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {
            $url = self::SERVER_URL . 'wc_install';
            $args = array(
                 'body' => array( 'site' => get_option('siteurl'), 'email' => get_option( 'admin_email' )),
            );
            
            $resp = wp_remote_post( $url, $args );
			
            if (is_wp_error($resp) || $resp['response']['code'] != 200)
            { // request failed retry latter
            	update_option('is_out_of_sync', true);
            	update_option('is_out_of_sync_install', true);
            	 
            	// removing other retries requests
            	if (get_option('is_out_of_sync_all_products'))
            		update_option('is_out_of_sync_all_products', false);
            	if (get_option('is_out_of_sync_product'))
            		delete_option('is_out_of_sync_product');

            } else {	// $resp['response']['code'] == 200
            	// the server returns site id in the body of the response, save it in the options
            	$response_json = json_decode($resp['body']);
            	$site_id = $response_json->{'site_id'};
            	
            	$batch_size = $response_json->{'batch_size'};
            	update_option('wcis_site_id', $site_id);
            	update_option('wcis_batch_size', $batch_size);
            	$authentication_key = $response_json->{'authentication_key'};
            	update_option('authentication_key', $authentication_key);
            	
            	if (get_option('is_out_of_sync_install')){
            		update_option('is_out_of_sync_install', false);
            		if (!get_option('is_out_of_sync_all_products'))
            			// if all products are synced
            			update_option('is_out_of_sync', false);
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
            'posts_per_page' => 250, 
			'meta_query' => array(),
            'paged' => $page,
		);

		return new WP_Query( $query_args );
	}
    
    private static function push_wc_products()
    {
        error_log("push products");
        /**
         * Check if WooCommerce is active
         **/
        if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
            $product_array = array();
            $loop = self::query_products();
            $page        = $loop->get( 'paged' );
			$total       = $loop->found_posts;
			$total_pages = $loop->max_num_pages;
            global $blog_id;
            
            while ($page <= $total_pages)
            {
                while ( $loop->have_posts() ) 
                {
                    $loop->the_post(); 
                    $product = self::get_product_from_post(get_the_ID());
                    $product_array[] = $product;
                }
                
                $page = $page + 1;
                $loop = self::query_products($page);
            }
           
            $send_products = array('total_pages'=>$total_pages, "total_products"=>$total, 'products'=>$product_array);
            self::send_products($send_products);
            
        }
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
        
        
//         print_r("post_password_required: " . post_password_required( $post_id ));
        
                
//         $sellable = $product->is_purchasable();
//         if ($product->managing_stock() && !($product->backorders_allowed())){
//         	if (!($product->is_in_stock()) || $product->get_stock_quantity() <= 0)
//         		$sellable = false;   		
//         }
//         $send_product['sellable'] = $sellable;
        
        	
// 	        $variation = $product->get_available_variations();
// 	        $variation_id = $variation[0]['variation_id'];
// 	        $variable_product1= new WC_Product_Variation( $variation_id );
// 	        $send_product['price_min'] = $variable_product1->get_variation_regular_price('min');
// 	        $send_product['price_max'] = count($variation);

	        
	        // not same product as WC_Product!!!
// 	        $variation = new WC_Product_Variation( $product->id/*, $product->get_parent()*/);
// 	        $send_product['price_min'] = $product->get_price();
//         	$send_product['price_max'] = $variation->get_price();
//         	$send_product['tmp'] = serialize($variation->get_title());
//     		echo "file: " . __FILE__ . ", function: " . __FUNCTION__ . ", line: " . __LINE__ . "action: " . $action . "<br>";
			 
        
        return $send_product;
    }
    
    public static function on_product_update($post_id )
    {    	
        $post = get_post( $post_id );  
        if ( 'product' !=  $post->post_type){// or  "publish" != $post->post_status ) {
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
    
    private static function send_products($products)
    {
        $batch_size =  get_option( 'wcis_batch_size' );
        $total_products = $products['total_products'];
        $total_pages = $products['total_pages'];
        $all_products = $products['products'];
        $product_chunks = array_chunk($all_products, $batch_size);
        $total_batches = count($product_chunks);
        $batch_number = 1;
        
        $is_request_failed = false; 

        foreach ($product_chunks as $chunk) 
        {
            $json_products = json_encode($chunk);   
            $url = self::SERVER_URL . 'wc_install_products';
        
            $args = array(
                 		'body' => array( 'site' => get_option('siteurl'), 
                 		'site_id' => get_option( 'wcis_site_id' ), 
                 		'products' => $json_products, 
                 		'total_batches' => $total_batches, 
            			'wcis_batch_size' => get_option( 'wcis_batch_size' ), 
                 		'authentication_key' => get_option('authentication_key'), 
                 		'total_products' => $total_products,
            			'batch_number' => $batch_number,
                 		'total_pages' => $total_pages)
            );
            $resp = wp_remote_post( $url, $args );
            $batch_number++;
            
            if (is_wp_error($resp) || $resp['response']['code'] != 200)
            	$is_request_failed = true;
        }
        if ($is_request_failed){	// on failure
        	update_option('is_out_of_sync', true);
        	update_option('is_out_of_sync_all_products', true);
        } else {					// on success
        	update_option('is_out_of_sync', false);
        	update_option('is_out_of_sync_all_products', false);
        	delete_option('is_out_of_sync_product');
        	delete_option('retries_limit_counter');
        }
        //$resp = wp_remote_get( $url, $args );
    }
    
    private static function send_categories($categories)
    {
    }
    
    private static function send_product_update($post_id, $action)
    {
        $product = self::get_product_from_post($post_id);
        $product_update = array('topic'=>$action, 'product'=>$product);
        $json_product_update = json_encode($product_update);   
        $url = self::SERVER_URL . 'wc_update_products';
        
        $is_out_of_sync = get_option('is_out_of_sync');
        
        $out_of_sync = $post_id;
        
        $args = array(
             'body' => array( 'site' => get_option('siteurl'), 
             				  'site_id' => get_option( 'wcis_site_id' ), 
             		 		  product_update => $json_product_update, 
        			   		  'authentication_key' => get_option('authentication_key')),
        );
        
        $resp = wp_remote_post( $url, $args );     

        if (is_wp_error($resp) || $resp['response'][code] != 200){	// != 200        	       	
        	$is_out_of_sync = true;
        	update_option('is_out_of_sync', $is_out_of_sync);

        	// if wc_install or wc_install_products out of sync do nothing
        	if (!get_option('is_out_of_sync_install') && !get_option('is_out_of_sync_all_products')){
        		// first product that got an error on update request 
	        	if (get_option('is_out_of_sync_product') == false){
	        		$retry_list = 
	        			array(0 => array(
		        					'post_id' => $post_id,
		        					'action' => $action        			
	        						)		
	        		);

	        		update_option('is_out_of_sync_product', $retry_list);
	        	} else {
	        		$retry_list = get_option('is_out_of_sync_product');
	        		if (count($retry_list) > self::RETRY_PRODUCT_ARRAY_MAX_COUNT)
	        			return;
	        			
	        		$is_unique = true;
	        		foreach ($retry_list as $p){
	        			if ($post_id == $p['post_id'] && $action == $p['action']){
	        				$is_unique = false;
	        				break;
	        			}
	        		}
	        		if ($is_unique){
		        		$retry_product = array(
		        				'post_id' => $post_id,
		        				'action' => $action
		        		);
		        		array_push($retry_list, $retry_product);
		        		update_option('is_out_of_sync_product', $retry_list);
	        		}  		
	        	}
        	}
        	
        } else { 	// $resp['response'][code] == 200
        	if ($is_out_of_sync){
        		self::process_retry_request();
        	}
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
		return $vars;
	}
	
	function process_instantsearchplus_request($req){
		if (array_key_exists('instantsearchplus', $req->query_vars)){
			if ($req->query_vars['instantsearchplus'] == 'version'){
				
				try {
					if ( ! function_exists( 'get_plugins' ) )
						require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
					$plugin_folder = get_plugins( '/' . 'woocommerce' );
					$plugin_file = 'woocommerce.php';
					if ( isset( $plugin_folder[$plugin_file]['Version'] ) )
						$wooVer = $plugin_folder[$plugin_file]['Version'];
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
						'req_status'			=> 'OK'
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
	
	/*
	 * called when ever there is a status(publish, draft, future, private, ...) change
	 * will call for send_product_update(...) only in case the product is unpublished 
	 * which is not caught by on_product_update hooks
	 */
// 	function on_post_unpublished($new_status, $old_status, $post){
// 		if ($old_status == 'publish' && $new_status != 'publish'){
// 			print_r("**in on_post_unpublished"); 
// 			echo "<br><br>";			
// 			self::send_product_update($post->ID, 'update');
// 		}
// 	}
	
	
// 	function on_profile_update(){
// 		print_r("on_profile_update()");
// 		$url = self::SERVER_URL . 'ext_update_email';
// 		$args = array(
// 						'body' => array(
// 								'email'	=> get_option('admin_email'),
// 								'uuid'	=> get_option('wcis_site_id'))	
// 		);
// 		$resp = wp_remote_post( $url, $args );
// 	}
		
}
?>