<?php
class phive_booking_core{
	public function __construct() {
		add_action( 'woocommerce_before_calculate_totals', array( $this, 'phive_update_custom_cart_price'), 1, 1 );
		add_action( 'woocommerce_loaded', array( $this,'register_booking_product_product_type' ) );
		
		add_action( 'woocommerce_phive_booking_add_to_cart', array( $this, 'phive_add_booking_product_to_cart' ), 30 );
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'phive_add_booking_infos_with_cart_item' ), 10, 3 ); //Add Customer Data to WooCommerce Cart

		add_filter( 'woocommerce_get_item_data', array( $this, 'phive_disply_item_booking_infos' ), 10, 2 ); //Display Details as Meta in Cart
		add_action( 'woocommerce_checkout_create_order_line_item', array( $this, 'phive_add_booking_info_order_line_item_meta' ), 10, 4 ); //Add Custom Details as Order Line Items
		
		add_filter( 'wc_add_to_cart_message_html', array($this, 'phive_added_to_cart_message'), 10,2 );
		
	}

	public function phive_added_to_cart_message( $message, $products ){
		
		$is_booking_product = false;
		foreach ($products as $product_id => $quantity) {
		
			$interval_type = get_post_meta( $product_id, "_phive_book_interval_type", 1 );
			if( !empty( $interval_type ) ){
				$is_booking_product = true;
				break;
			}
		}
		
		if( $is_booking_product ){
			$message = '<a href="'.wc_get_page_permalink( 'cart' ).'" class="button wc-forward">'.__('View cart','bookings-and-appointments-for-woocommerce').'</a> '.__('Booking Done. Please check cart for payment.','bookings-and-appointments-for-woocommerce');
			$message = apply_filters( 'ph_booking_add_to_cart_message_html', $message, $products );
		}

		return $message;
	}

	public function phive_update_custom_cart_price( $cart_object ) {
		foreach ( $cart_object->cart_contents as $cart_item_key => $value ) { 
			if( isset($value['phive_booked_price']) ){
				// Version 2.x
				//$value['data']->price = $value['phive_booked_price']

				// Version 3.x / 4.x
				$value['data']->phive_set_booked_price( $value['data']->get_id(), $value['phive_booked_price'] );
			}
		}
	}


	/**
	* Forcefully convert WP date format into 'Y-m-d' format
	*/
	private function phive_formate_date($date){
		$new_date = DateTime::createFromFormat( get_option( 'date_format' ), $date );
		if( is_object($new_date) ){
			return $new_date->format('Y-m-d');
		}else{
			//The format 'F j, Y' is not working with 'createFromFormat'
			return date('Y-m-d', strtotime($date));
		}

	}

	public function phive_add_booking_infos_with_cart_item($cart_item_data, $product_id, $variation_id){
		if(isset($_REQUEST['phive_book_from_date'])){
			if( !class_exists('phive_booking_cron_manager') ){
				include_once('class-ph-booking-cron-manager.php');
			}
			$cron_manager = new phive_booking_cron_manager();
			$cart_item_data['phive_book_from_date'] 		= sanitize_text_field( $_REQUEST['phive_book_from_date'] );
			$cart_item_data['phive_book_to_date'] 			= sanitize_text_field( $_REQUEST['phive_book_to_date'] );
			$cart_item_data['phive_booked_price'] 			= sanitize_text_field( $_REQUEST['phive_booked_price'] );
			$cart_item_data['phive_booking_freezer_id'] 	= $cron_manager->freeze_booking_slot( $product_id, $cart_item_data['phive_book_from_date'], $cart_item_data['phive_book_to_date'] );
		}
		return $cart_item_data;
	}

	
	function phive_disply_item_booking_infos($item_data, $cart_item){
		//If case of fixed block of time, show only from date
		if( array_key_exists('phive_book_from_date', $cart_item) && $cart_item['phive_book_from_date'] == $cart_item['phive_book_to_date'] ){
			$item_data[] = array(
				'key'   => 'Booked',
				'value' => $cart_item['phive_book_from_date'],
			);
		}else{
			if(array_key_exists('phive_book_from_date', $cart_item)){
				$item_data[] = array(
					'key'   => 'Booked from',
					'value' => $cart_item['phive_book_from_date'],
				);
			}
			if(array_key_exists('phive_book_to_date', $cart_item)){
				$item_data[] = array(
					'key'   => 'Booked to',
					'value' => $cart_item['phive_book_to_date'],
				);
			}
		}
		
		return $item_data;
	}

	function phive_add_booking_info_order_line_item_meta($item, $cart_item_key, $values, $order){
		//If case of fixed block of time, show only from date
		if(array_key_exists('phive_book_from_date', $values) && $values['phive_book_from_date']==$values['phive_book_to_date']){
			$item->add_meta_data('From',$values['phive_book_from_date']);
		}else{
			if(array_key_exists('phive_book_from_date', $values)){
				$item->add_meta_data('From',$values['phive_book_from_date']);
			}

			if(array_key_exists('phive_book_to_date', $values)){
				$item->add_meta_data('To',$values['phive_book_to_date']);
			}
		}

		if(array_key_exists('phive_book_to_date', $values)){
			$item->add_meta_data('Cost',$values['phive_booked_price']);
		}

		/*if(array_key_exists('phive_from_book_time', $values)){
			$item->add_meta_data('_phive_from_book_time',$values['phive_from_book_time']);
		}
		if(array_key_exists('phive_to_book_time', $values)){
			$item->add_meta_data('_phive_to_book_time',$values['phive_to_book_time']);
		}*/
	}

	public function phive_add_booking_product_to_cart() {
		ob_start();
		include( 'html-ph-booking-add-to-cart.php' );
		echo ob_get_clean();
	}

	/**
	 * Register the custom product type
	 */
	public static function register_booking_product_product_type() {
		include_once( 'class-ph-booking-wc-product.php' );
	}
	
}
new phive_booking_core;
