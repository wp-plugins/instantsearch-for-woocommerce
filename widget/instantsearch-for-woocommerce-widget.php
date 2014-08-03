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

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );
		
		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		$form = '
            	<form class="isp_search_box_form" name="isp_search_box" action="' . esc_url(home_url('/')) . '">
              		<input type="text" name="s" class="isp_search_box_input" placeholder="Search" autocomplete="off" autocorrect="off" autocapitalize="off" style="outline: none;">
					<input type="hidden" name="post_type" value="product" />
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
	public function form( $instance ) {}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

		return $instance;
	}
}


















?>