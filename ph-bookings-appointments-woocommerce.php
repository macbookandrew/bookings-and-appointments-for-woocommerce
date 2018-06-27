<?php
/**
 * Plugin Name: Bookings and Appointments For WooCommerce
 * Description:	Bookings and Appointments solution for all types of businesses.
 * Version: 1.1.4
 * Author: PluginHive
 * Author URI: http://pluginhive.com/about/
 * WC requires at least: 2.6
 * WC tested up to: 3.4
 * Text Domain: bookings-and-appointments-for-woocommerce
*/

/**
 * Plugin activation check
 */
function phive_booking_pre_activation_check(){
	
	//check if basic version is there
	if ( is_plugin_active('ph-bookings-appointments-woocommerce-premium/ph-bookings-appointments-woocommerce-premium.php') ){
		deactivate_plugins( basename( __FILE__ ) );
		wp_die(__("Is everything fine? You already have the Premium version installed in your website. For any issues, kindly raise a ticket via <a target='_blank' href='//pluginhive.com/support/'>pluginhive.com/support</a>",'bookings-and-appointments-for-woocommerce'), "", array('back_link' => 1 ));
	}

}
register_activation_hook( __FILE__, 'phive_booking_pre_activation_check' );


if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )
	&& !in_array('ph-bookings-appointments-woocommerce-premium/ph-bookings-appointments-woocommerce-premium.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {	

	class phive_booking_initialze {

		public function __construct() {

			add_action( 'wp_enqueue_scripts', array( $this, 'phive_booking_scripts' ) );
			add_filter( 'admin_enqueue_scripts', array( $this, 'phive_admin_scripts' ) );		

			include_once('includes/class-ph-booking-cron-manager.php');
			include_once('includes/class-ph-bookings-appointments-woocommerce.php');
			include_once('includes/class-ph-booking-product-manager.php');
			include_once('includes/class-ph-booking-ajax-manager.php');
			
			add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), array( $this, 'plugin_action_links' ) );
			
			if( is_admin() ){
				include_once ( 'includes/admin/class-ph-booking-admin-pages.php' );
			}

			load_plugin_textdomain( 'bookings-and-appointments-for-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n/' );
		}
		
		public function phive_admin_scripts() {

			wp_enqueue_style( 'wc-common-style', plugins_url( '/resources/css/admin_style.css', __FILE__ ));
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_style( 'jquery-ui-css', plugins_url( '/resources/css/jquery-ui.min.css', __FILE__ ) );  
			wp_enqueue_script( 'ph_booking_admin_script', plugins_url( '/resources/js/ph-booking-admin.js', __FILE__ ), array( 'jquery' ) );
		
		}

		function phive_booking_scripts(){
		
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_script( 'ph_booking_product', plugins_url( '/resources/js/ph-booking-ajax.js', __FILE__ ), array('jquery'), '1.0.0', true );
			wp_enqueue_script( 'ph_booking_general_script', plugins_url( '/resources/js/ph-booking-genaral.js', __FILE__ ), array( 'jquery' ) );
			
			$localization_arr = array(
				'ajaxurl' 	=> admin_url( 'admin-ajax.php' ),
				'security' 	=> wp_create_nonce( 'phive_change_product_price' )
			);
			
			wp_localize_script( 'ph_booking_general_script', 'phive_booking_locale', $this->phive_get_string_translation_arr() );
			wp_localize_script( 'ph_booking_product', 'phive_booking_ajax', array_merge( $localization_arr, $this->phive_get_string_translation_arr() ) );
			wp_enqueue_style( 'jquery-ui-css', plugins_url( '/resources/css/jquery-ui.min.css', __FILE__ ) );  
			wp_enqueue_style( 'ph_booking_style', plugins_url( '/resources/css/ph_booking.css', __FILE__ ));
			wp_enqueue_style( 'ph_booking_calendar_style', plugins_url( '/resources/css/ph_calendar.css', __FILE__ ));

		}

		private function phive_get_string_translation_arr(){
			return array(
				'months'			=> array(
					__('January', 'bookings-and-appointments-for-woocommerce'),	
					__('February', 'bookings-and-appointments-for-woocommerce'),	
					__('March', 'bookings-and-appointments-for-woocommerce'),	
					__('April', 'bookings-and-appointments-for-woocommerce'),	
					__('May', 'bookings-and-appointments-for-woocommerce'),	
					__('June', 'bookings-and-appointments-for-woocommerce'),	
					__('July', 'bookings-and-appointments-for-woocommerce'),	
					__('August', 'bookings-and-appointments-for-woocommerce'),	
					__('September', 'bookings-and-appointments-for-woocommerce'),	
					__('October', 'bookings-and-appointments-for-woocommerce'),	
					__('November', 'bookings-and-appointments-for-woocommerce'),	
					__('December', 'bookings-and-appointments-for-woocommerce'),	
				),
				'months_short'			=> array(
					__('Jan', 'bookings-and-appointments-for-woocommerce'),	
					__('Feb', 'bookings-and-appointments-for-woocommerce'),	
					__('Mar', 'bookings-and-appointments-for-woocommerce'),	
					__('Apr', 'bookings-and-appointments-for-woocommerce'),	
					__('May', 'bookings-and-appointments-for-woocommerce'),	
					__('Jun', 'bookings-and-appointments-for-woocommerce'),	
					__('Jul', 'bookings-and-appointments-for-woocommerce'),	
					__('Aug', 'bookings-and-appointments-for-woocommerce'),	
					__('Sep', 'bookings-and-appointments-for-woocommerce'),	
					__('Oct', 'bookings-and-appointments-for-woocommerce'),	
					__('Nov', 'bookings-and-appointments-for-woocommerce'),	
					__('Dec', 'bookings-and-appointments-for-woocommerce'),	
				),
				'booking_cost' 		=> __('Booking cost', 'bookings-and-appointments-for-woocommerce'),
				'booking_date' 		=> __('Booking', 'bookings-and-appointments-for-woocommerce'),
				'is_not_avail' 		=> __('is not available.', 'bookings-and-appointments-for-woocommerce'),
				'are_not_avail' 	=> __('are not available.', 'bookings-and-appointments-for-woocommerce'),
				'pick_later_date'	=> __('Pick a later end date', 'bookings-and-appointments-for-woocommerce'),
				'max_limit_text'	=> __('Max no of blocks available to book is', 'bookings-and-appointments-for-woocommerce'),
				'pick_booking'		=> __('Please pick a booking period', 'bookings-and-appointments-for-woocommerce'),
			);
		}
		
		function plugin_action_links( $links ) {
		
			$plugin_links = array(
				'<a href="http://pluginhive.com/support/" target="_blank">' . __('Support', 'bookings-and-appointments-for-woocommerce') . '</a>',
			);
			return array_merge( $plugin_links, $links );
		
		}			
		

	}
	new phive_booking_initialze;

}
