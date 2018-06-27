<?php
class phive_booking_cron_manager{
	public function __construct() {
		add_action( 'ph-unfreez-booking-slot', array( $this, 'phive_clear_scheduled_unfreez' ) );
		add_action( 'woocommerce_before_cart_item_quantity_zero', array( $this, 'before_cart_item_quantity_zero' ), 10, 1 );
		add_action( 'woocommerce_cart_item_removed', array( $this, 'cart_item_removed' ), 20 );
		add_action( 'woocommerce_cart_item_restored', array( $this, 'cart_item_restored' ), 20 );
	}

	public function cart_item_restored( $cart_item_key ) {
		$cart	  = WC()->cart->get_cart();
		$cart_item = $cart[ $cart_item_key ];
		if ( isset( $cart_item['phive_booking_freezer_id'] ) ) {
			$this->schedule_unfreez( $cart_item['phive_booking_freezer_id'] );
		}
	}

	public function cart_item_removed( $cart_item_key ) {
		$cart_item = WC()->cart->removed_cart_contents[ $cart_item_key ];
		if ( isset( $cart_item['phive_booking_freezer_id'] ) ) {
			$post_id = $cart_item['phive_booking_freezer_id'];
			$this->phive_clear_scheduled_unfreez( $post_id );
		}
	}
	
	public function before_cart_item_quantity_zero( $cart_item_key ) {
		$cart	   = WC()->cart->get_cart();
		$cart_item  = $cart[ $cart_item_key ];

		if ( isset($cart_item['phive_booking_freezer_id']) ) {
			$post_id = $cart_item['phive_booking_freezer_id'];
			$this->phive_clear_scheduled_unfreez( $post_id );
		}
	}

	public function freeze_booking_slot( $product_id, $from, $to){
		global $wpdb;

		$new_post = array(
			'ID' => '',
			'post_type' => 'booking_slot_freezer', // Custom Post Type Slug
			'post_status' => 'open',
			'post_title' => 'Booking slot freezer',
			'ping_status' => 'closed',
		);

		$freezer_id = wp_insert_post($new_post);
		
		$meta_values = array(
			'_product_id' 		=> $product_id,
			'From'			=> $from,
			'To'			=> $to,
			'_booking_customer_id'	=> is_user_logged_in() ? get_current_user_id() : 0,
		);

		foreach ( $meta_values as $meta_key => $value ) {
			update_post_meta( $freezer_id, $meta_key, $value );
		}
		$this->schedule_unfreez( $freezer_id );

		return $freezer_id;
	}

	private function schedule_unfreez( $post_id ){
		wp_schedule_single_event( time() + ( 60 * 15 ) , "ph-unfreez-booking-slot", array( $post_id ) );
	}


	public function phive_clear_scheduled_unfreez( $post_id ){
		wp_delete_post( $post_id );
		wp_clear_scheduled_hook( 'ph-unfreez-booking-slot', array( $post_id ) );
	}
}
new phive_booking_cron_manager();