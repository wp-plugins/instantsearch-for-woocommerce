<?php

if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

/**
 * InstantSearch+ for WooCommerce.
 *
 * @package   WCISPlugin
 * @author    Fast Simon Inc
 * @license   GPL-2.0+
 * @link      http://www.instantsearchplus.com
 * @copyright 2014 InstantSearchPlus
 */

class WCISIntegration extends WC_Integration{
	public function __construct() {
		global $woocommerce;
	
		$this->id                 = 'WCISPlugin';
		
		//WCISPlugin - WCISPlugin::get_instance()->get_plugin_slug() || instantsearch-for-woocommerce
		$this->method_title       = __( 'InstantSeach+ for WooCommerce', 'WCISPlugin' );
		 
		$this->method_description = __( 'Best search plugin for WooCommerce.', 'WCISPlugin' );
	
		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();
	
		// Actions.
		add_action( 'woocommerce_update_options_integration_' .  $this->id, array( $this, 'process_admin_options' ) );
	}
	
	public function init_form_fields() {
		
		$dashboard_url = 'https://acp-magento.appspot.com/wc_dashboard?site_id='. get_option( 'wcis_site_id' ) . '&authentication_key=' . get_option('authentication_key') . '&new_tab=1' . '&v=' . WCISPlugin::VERSION;
		
		$this->form_fields = array(
	 			'customize_button' => array(
	 				'title'             => __( 'InstantSearch+ Dashboard', 'WCISPlugin' ),
 					'button_name'       => __( 'Load InstantSearch+ Dashboard', 'WCISPlugin' ),
	 				'type'              => 'button',
	 				'custom_attributes' => array(
	 									'onclick' => "window.open('" . $dashboard_url . "', '_blank')",
					),
	 				'description'       => __( 'Customize your settings by going to the integration site directly.', 'WCISPlugin' ),
	 				'desc_tip'          => true,
	  			)
				
		);
	}
	
	/**
	 * Generate Button HTML.
	 */
	public function generate_button_html( $key, $data ) {
		$field    = $this->plugin_id . $this->id . '_' . $key;
		$defaults = array(
				'class'             => 'button-secondary',
				'css'               => '',
				'custom_attributes' => array(),
				'desc_tip'          => false,
				'description'       => '',
				'title'             => '',
		);
	
		$data = wp_parse_args( $data, $defaults );
	
		ob_start();
		?>
			<tr valign="top">
				<th scope="row" class="titledesc">
					<label for="<?php echo esc_attr( $field ); ?>"><?php echo wp_kses_post( $data['title'] ); ?></label>
					<?php echo $this->get_tooltip_html( $data ); ?>
				</th>
				<td class="forminp">
					<fieldset>
						<legend class="screen-reader-text"><span><?php echo wp_kses_post( $data['title'] ); ?></span></legend>
						<button class="<?php echo esc_attr( $data['class'] ); ?>" type="button" name="<?php echo esc_attr( $field ); ?>" id="<?php echo esc_attr( $field ); ?>" style="<?php echo esc_attr( $data['css'] ); ?>" <?php echo $this->get_custom_attribute_html( $data ); ?>><?php echo wp_kses_post( $data['button_name'] ); ?></button>
						<?php echo $this->get_description_html( $data ); ?>
					</fieldset>
				</td>
			</tr>
			<?php
			return ob_get_clean();
		}
}




?>