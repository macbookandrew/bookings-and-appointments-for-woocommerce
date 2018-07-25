<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
class phive_booking_product_options {
	public function __construct() {
		add_filter( 'product_type_selector', array( $this, 'add_booking_product_product' ) );
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'custom_product_tabs' ) );
		add_action( 'woocommerce_product_data_panels', array( $this, 'booking_options_product_tab_content' ) );
		add_action( 'woocommerce_process_product_meta_phive_booking', array( $this,'save_booking_option_field' ) ); //The filter name should match with product class name
		add_filter( 'woocommerce_is_sold_individually', array($this, 'hide_quantity_field'), 10, 2 );
	}

	/**
	* Hide quantity field in cart page for bookable products 
	*/
	function hide_quantity_field( $return, $product ) {
		if ( is_cart() && $product->get_type() == "phive_booking" ){
			return true;
		}
		return $return;
	}

	/**
	 * Add a custom product tab.
	 */
	function custom_product_tabs( $tabs) {
		$tabs['phive_booking_availablity'] = array(
			'label'		=> __( 'Booking availability', 'bookings-and-appointments-for-woocommerce' ),
			'target'	=> 'booking_availability',
			'class'		=> array( 'show_if_booking_product','hide_if_simple', 'hide_if_variable', 'hide_if_grouped', 'hide_if_external'  ),
		);
		$tabs['phive_booking'] = array(
			'label'		=> __( 'Booking', 'bookings-and-appointments-for-woocommerce' ),
			'target'	=> 'booking_options',
			'class'		=> array( 'show_if_booking_product','hide_if_simple', 'hide_if_variable', 'hide_if_grouped', 'hide_if_external'  ),
		);
		return $tabs;
	}

	
	/**
	 * Add to product type drop down.
	 */
	function add_booking_product_product( $types ){
		// Key should be exactly the same as in the class
		$types[ 'phive_booking' ] = __( 'Bookable product','bookings-and-appointments-for-woocommerce' );
		return $types;
	}

	/**
	 * Contents of the booking options product tab.
	 */
	function booking_options_product_tab_content() {
		echo '<div id="message" class="notice notice-info is-dismissible">
			<p>Do you like the PluginHive Bookings plugin? It would be a huge encouragement for us if you review it 
			<a target="_blank" href="//wordpress.org/support/plugin/bookings-and-appointments-for-woocommerce/reviews/">here</a>
			<button type="button" class="notice-dismiss">
			<span class="screen-reader-text">Dismiss this notice.</span></button>
		</div>';
		global $post;
		include("html-ph-booking-product-admin-options.php");
		include("html-ph-booking-product-admin-availability.php");
	}


	/**
	 * Save the custom fields.
	 */
	function save_booking_option_field( $post_id ) {
		/*$booking_option = isset( $_POST['_enable_renta_option'] ) ? 'yes' : 'no';
		update_post_meta( $post_id, '_enable_renta_option', $booking_option );*/

		if ( isset( $_POST['_phive_book_price'] ) ){
			//Set default value of interval 1
			$interval = !empty($_POST['_phive_book_interval']) ? sanitize_text_field( $_POST['_phive_book_interval'] ) : 1;

			update_post_meta( $post_id, '_phive_book_price', sanitize_text_field( $_POST['_phive_book_price'] ) );
			update_post_meta( $post_id, '_phive_book_interval', $interval );
			update_post_meta( $post_id, '_phive_book_interval_type', sanitize_text_field( $_POST['_phive_book_interval_type'] ) );
			update_post_meta( $post_id, '_phive_book_interval_period', sanitize_text_field( $_POST['_phive_book_interval_period'] ) );
			update_post_meta( $post_id, '_phive_book_working_hour_start', sanitize_text_field( $_POST['_phive_book_working_hour_start'] ) );
			update_post_meta( $post_id, '_phive_book_working_hour_end', sanitize_text_field( $_POST['_phive_book_working_hour_end'] ) );
		}
			
		update_post_meta( $post_id, 'booking_availability_rules', $this->validate_availability_rules() );
	}
	private function validate_availability_rules(){
		$rules = array();
		foreach ($_POST['ph_booking_availability_type'] as $key => $value) {
			$rules[] = array(
				'availability_type' => $_POST['ph_booking_availability_type'][$key],
				'from_time' 		=> $_POST['ph_booking_from_time'][$key],
				'to_time' 			=> $_POST['ph_booking_to_time'][$key],
				'is_bokable'		=> $_POST['ph_booking_is_bookable'][$key],
				'holiday_saturday'	=> $_POST['holiday_saturday'],
				'holiday_sunday'	=> $_POST['holiday_sunday'],
			);
		}
		return $rules;
	}

}
new phive_booking_product_options();