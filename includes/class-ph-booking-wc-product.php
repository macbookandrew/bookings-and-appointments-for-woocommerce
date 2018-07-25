<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
class WC_Product_phive_booking extends WC_Product {
	
	private $booked_price = false;

	public function __construct( $product ) {
		$this->product_type = 'phive_booking';
		parent::__construct( $product );

		$this->product_custom_fields = get_post_meta( $this->id );

		if ( ! empty( $this->product_custom_fields['_booking_price'][0] ) ) {
			$this->product_price = $this->product_custom_fields['_booking_price'][0];
		}

	}

	public function get_interval_type(){
		return get_post_meta( $this->id, "_phive_book_interval_type", 1 );
	}

	public function get_interval_period(){
		return get_post_meta( $this->id, "_phive_book_interval_period", 1 );
	}

	public function get_interval(){
		return get_post_meta( $this->id, "_phive_book_interval", 1 );
	}

	public function set_id($id){
		$this->id = $id;
	}

	public function phive_set_booked_price( $id, $price ) {
		$this->booked_price = $price;
	}

	public function get_price($context = 'view'){
		global $woocommerce;
		
		if( $this->booked_price > 0 ){
			return	$this->booked_price;
		}

		//If the date range is set
		$customer_value = isset(WC()->customer) ? WC()->customer->get_meta('phive_booking_details') : '';
		if( !empty($customer_value[ $this->id ]) && $customer_value[ $this->id ]['book_to'] ){
			
			$book_from					= strtotime( $customer_value[ $this->id ]['book_from'] );
			$book_to 					= strtotime( $customer_value[ $this->id ]['book_to'] );
			$prod_interval 				= get_post_meta( $this->id, '_phive_book_interval', 1 );
			
			//Default interval 1
			if( empty($prod_interval) )
				$prod_interval 		= 1;
		
			$prod_price_per_interval	= get_post_meta( $this->id, '_phive_book_price', 1 );
			$prod_price_interval_type	= get_post_meta( $this->id, '_phive_book_interval_period', 1 );
			
			switch ($prod_price_interval_type) {
				case 'minute':
					$interval = (60);
					break;
				case 'hour':
					$interval = (60 * 60);
					break;
				case 'day':
					$interval = (60 * 60 * 24);
					break;
				case 'week':
					$interval = (60 * 60 * 24 * 7);
					break;
				case 'month':
					$interval = (60 * 60 * 24 * 30);
					break;
			}			
			$interval = $interval * $prod_interval;
			$datediff = ($book_to - $book_from);
			$interval_count = ( floor( $datediff / $interval ) )+1;

			$price = $interval_count * $prod_price_per_interval;
			
		}else{
			$price = get_post_meta( $this->id, '_phive_book_price', 1 );
		}
		
		$this->set_prop( 'price', wc_format_decimal( $price ) );
		return $price;
	}

	/**
	 * Get booking's price HTML.
	 *
	 * @return string containing the formatted price
	 */
	public function get_price_html( $deprecated = '' ) {
		if ( '' === $this->get_price() ) {
			$price = apply_filters( 'woocommerce_empty_price_html', '', $this );
		} elseif ( $this->is_on_sale() ) {
			$price = wc_format_sale_price( wc_get_price_to_display( $this, array( 'price' => $this->get_regular_price() ) ), wc_get_price_to_display( $this ) ) . $this->get_price_suffix();
		} else {
			$price = wc_price( wc_get_price_to_display( $this ) ) . $this->get_price_suffix();
		}

		return apply_filters( 'woocommerce_get_price_html', $price, $this );
	}



	/**
	 * Get the add to cart button text
	 *
	 * @access public
	 * @return string
	 */
	public function add_to_cart_text() {

		/*if ( $this->is_purchasable() && $this->is_in_stock() ) {
			$text = get_option( WC_bookings_Admin::$option_prefix . '_add_to_cart_button_text', __( 'Sign Up Now', 'bookings-and-appointments-for-woocommerce' ) );
		} else {
			$text = parent::add_to_cart_text(); // translated "Read More"
		}*/

		$text = __('Book Now','bookings-and-appointments-for-woocommerce');
	
		return apply_filters( 'woocommerce_product_add_to_cart_text', $text, $this );
	}

	/**
	 * Get the add to cart button text for the single page
	 *
	 * @access public
	 * @return string
	 */
	public function single_add_to_cart_text() {
		return apply_filters( 'woocommerce_product_single_add_to_cart_text', self::add_to_cart_text(), $this );
	}



	/**
	 * Checks if the store manager has requested the current product be limited to one purchase
	 * per customer, and if so, checks whether the customer already has an active booking to
	 * the product.
	 *
	 * @access public
	 * @return bool
	 */
	function is_purchasable() {

		/*$purchasable = parent::is_purchasable();

		if ( true === $purchasable && false === WC_Product_phive_booking::is_purchasable( $purchasable, $this ) ) {
			$purchasable = false;
		}

		return apply_filters( 'woocommerce_booking_is_purchasable', $purchasable, $this );*/
		return true;
	}

	public function get_virtual( $context = 'view' ) {
		return true;
	}
}

