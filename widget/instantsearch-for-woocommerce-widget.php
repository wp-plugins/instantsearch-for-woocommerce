<?php

if (!defined('ABSPATH')) 
	exit; // Exit if accessed directly

class WCISPluginWidget extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		
		parent::__construct('isp_search_box_widget',
							__( 'InstantSearch+ Search Box', 'WCISPlugin' ),
							array('description' => __( 'InstantSearch+ search box for your site', 'WCISPlugin' ), 
								  'classname' => 'widget_isp_search_box_widget') 
		);
	}
	
	public $default_search_box_fields = array(			
			'search_box_width'  	=> 10,
			'search_box_height'		=> 2.3,
			'search_box_inner_text'	=> 'Search...',
			'search_box_float'		=> 'none',
	);

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );
		
		echo $args['before_widget'];
		if ( !empty($title) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}
		
		if (array_key_exists('search_box_width', $instance) && is_numeric($instance['search_box_width']) && $instance['search_box_width'] != 0)
			$search_box_width = $instance['search_box_width'];
		else
			$search_box_width = $this->default_search_box_fields['search_box_width'];
		if (array_key_exists('search_box_height', $instance) && is_numeric($instance['search_box_height']) && $instance['search_box_height'] != 0)
			$search_box_height = $instance['search_box_height'];
		else
			$search_box_height = $this->default_search_box_fields['search_box_height'];
		$search_box_inner_text = (array_key_exists('search_box_inner_text', $instance)) ? 
							$instance['search_box_inner_text'] : 
							$this->default_search_box_fields['search_box_inner_text'];
		$search_box_float = (array_key_exists('search_box_float', $instance)) ?
							$instance['search_box_float'] :
							$this->default_search_box_fields['search_box_float'];
				
		$form = '
            	<form class="isp_search_box_form" name="isp_search_box" action="' . esc_url(home_url('/')) . '" style="width:'.$search_box_width.'rem; float:'.$search_box_float.';">
              		<input type="text" name="s" class="isp_search_box_input" placeholder="'.$search_box_inner_text.'" autocomplete="off" autocorrect="off" autocapitalize="off" style="outline: none; width:'.$search_box_width.'rem; height:'.$search_box_height.'rem;">
					<input type="hidden" name="post_type" value="product" />
              		<input type="image" src="'. plugins_url('assets/images/magnifying_glass.png', __FILE__ ) .'" class="isp_widget_btn" value="" />
           		</form>
        ';

		printf($form);
		
		echo $args['after_widget'];
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {		
		$search_box_width = (array_key_exists('search_box_width', $instance)) ? 
							$instance['search_box_width'] : 
							$this->default_search_box_fields['search_box_width'];
		$search_box_height = (array_key_exists('search_box_height', $instance)) ?
							$instance['search_box_height'] :
							$this->default_search_box_fields['search_box_height'];
		$search_box_inner_text = (array_key_exists('search_box_inner_text', $instance)) ? 
							$instance['search_box_inner_text'] : 
							$this->default_search_box_fields['search_box_inner_text'];
		$search_box_float = (array_key_exists('search_box_float', $instance)) ?
							$instance['search_box_float'] :
							$this->default_search_box_fields['search_box_float'];

		$float_selecte = '';
		$options = array('none', 'left', 'right');
		foreach ($options as $value){
			if ($value == $search_box_float)
				$float_selecte .= '<option value="'.$value.'" selected>'.$value.'</option>';
			else 
				$float_selecte .= '<option value="'.$value.'">'.$value.'</option>';
		}
		
		$form = '
			<p>
				<label for="'. $this->get_field_id('search_box_inner_text') .'">'. __('Search box inner text: ', 'WCISPlugin'). '</label>
				<input type="text" class="widefat" id="'. $this->get_field_id('search_box_inner_text') .'" name="'. $this->get_field_name('search_box_inner_text') .'" value="'.  esc_attr($search_box_inner_text) .'"/>
			</p>
			<p>
				<label for="'. $this->get_field_id('search_box_width') .'">'. __('Width - search box size (in rem units): ', 'WCISPlugin'). '</label>
				<input type="text" class="widefat" id="'. $this->get_field_id('search_box_width') .'" name="'. $this->get_field_name('search_box_width') .'" value="'.  esc_attr($search_box_width) .'"/>
			</p>
			<p>
				<label for="'. $this->get_field_id('search_box_height') .'">'. __('Height - search box size (in rem units): ', 'WCISPlugin'). '</label>
				<input type="text" class="widefat" id="'. $this->get_field_id('search_box_height') .'" name="'. $this->get_field_name('search_box_height') .'" value="'.  esc_attr($search_box_height) .'"/>
			</p>
			<p>
				<label for="'. $this->get_field_id('search_box_float') .'">'. __('Float - push/move search box to the left or to the right side: ', 'WCISPlugin').'</label>
				<select id="'. $this->get_field_id('search_box_float') .'" name="'. $this->get_field_name('search_box_float') .'" class="widefat">
						'.$float_selecte.'
				</select>		
			</p>
		
		';
		
		printf($form);
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] 					= ( !empty($new_instance['title']) ) ? sanitize_text_field( $new_instance['title'] ) : '';
		$instance['search_box_inner_text'] 	= sanitize_text_field($new_instance['search_box_inner_text']);
		$instance['search_box_float'] 		= sanitize_text_field($new_instance['search_box_float']);
		
		if (is_numeric(sanitize_text_field($new_instance['search_box_width'])) && sanitize_text_field($new_instance['search_box_width'] != 0))
			$instance['search_box_width'] = sanitize_text_field($new_instance['search_box_width']);
		if (is_numeric(sanitize_text_field($new_instance['search_box_height'])) && sanitize_text_field($new_instance['search_box_height'] != 0))
			$instance['search_box_height'] = sanitize_text_field($new_instance['search_box_height']);
		
		$err_msg = "site: " . get_option('siteurl') . " - widget is active!";
		WCISPlugin::get_instance()->send_error_report($err_msg);
		
		return $instance;
	}
}

?>