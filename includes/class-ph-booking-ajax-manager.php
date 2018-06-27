<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
class phive_booking_ajax_manager{
	public function __construct() {
		add_action( 'wp_ajax_phive_get_callender_next_month', array($this,'phive_get_callender_next_month') );
		add_action( 'wp_ajax_nopriv_phive_get_callender_next_month', array($this,'phive_get_callender_next_month') );
		
		add_action( 'wp_ajax_phive_get_callender_prev_month', array($this,'phive_get_callender_prev_month') );
		add_action( 'wp_ajax_nopriv_phive_get_callender_prev_month', array($this,'phive_get_callender_prev_month') );

		add_action( 'wp_ajax_phive_get_booked_datas_of_date', array($this,'phive_callender_time_for_date') );
		add_action( 'wp_ajax_nopriv_phive_get_booked_datas_of_date', array($this,'phive_callender_time_for_date') );

		add_action( 'wp_ajax_phive_get_booked_price', array($this,'phive_get_booked_price') );
		add_action( 'wp_ajax_nopriv_phive_get_booked_price', array($this,'phive_get_booked_price') );
		// $this->phive_callender_time_for_date();
	}

	public function phive_get_booked_price(){
		global $woocommerce;
		$product_id = $_POST['product_id'];

		$date_format = get_option( 'date_format' );

		$from = wp_unslash( $_POST['book_from'] );
		$to = wp_unslash( $_POST['book_to'] );

		$from_date = DateTime::createFromFormat( 'Y-m-d', esc_attr( $from ) );
		$to_date = DateTime::createFromFormat( 'Y-m-d', esc_attr( $to ) );

		$value[ $product_id ] = array( 'book_from' => $from, 'book_to'=>$to );
		WC()->customer->update_meta_data( 'phive_booking_details', $value );
		
		$prod_obj = new WC_Product_phive_booking( $product_id );
		$prod_obj->set_id($product_id);

		echo json_encode(
			array(
				'price_html' 	=> $prod_obj->get_price_html(),
				'price'			=> $prod_obj->get_price(),
				'from_date'		=> $from_date->format( $date_format ),
				'to_date'		=> $to_date->format( $date_format ),
			)
		);
		exit();
	}

	public function phive_callender_time_for_date(){
		$product_id = $_POST['product_id'];
		$date = $_POST['date'];
		if( !class_exists('phive_booking_callender') ){
			include_once('booking-callender/class-ph-booking-callender.php');
		}
		
		$shop_opening_time = get_post_meta( $product_id, "_phive_book_working_hour_start", 1 );
		$shop_opening_time = !empty( $shop_opening_time ) ? date( 'H:i',strtotime($shop_opening_time) ) : '00:00';
		
		$start_date = $date.' '.$shop_opening_time;

		$callender = new phive_booking_callender();
		$callender->phive_generate_time_for_period( $start_date, '', $product_id );
		exit();
	}

	public function phive_get_callender_next_month(){
		$product_id = $_POST['product_id'];
		$month 		= $_POST['month'];
		$year 		= $_POST['year'];
		if( !class_exists('phive_booking_callender') ){
			include_once('booking-callender/class-ph-booking-callender.php');
		}
		$callender = new phive_booking_callender();

	 	$start_date = date ( "Y-m-d", strtotime( "+1 month", strtotime("$year-$month-01") ) ) ;
		
		echo json_encode(
			array(
				'days' 		=> $callender->phive_generate_days_for_period( $start_date, '', $product_id ),
				'month'		=> date( "F",strtotime($start_date) ),
				'year'		=> date( "Y",strtotime($start_date) ),
			)
		);
		exit();
	}
	public function phive_get_callender_prev_month(){
		$product_id = $_POST['product_id'];
		$month 		= $_POST['month'];
		$year 		= $_POST['year'];
		if( !class_exists('phive_booking_callender') ){
			include_once('booking-callender/class-ph-booking-callender.php');
		}
		$callender = new phive_booking_callender();

	 	$start_date = date ( "Y-m-d", strtotime( "-1 month", strtotime("$year-$month-01") ) ) ;
		echo json_encode(
			array(
				'days' 		=> $callender->phive_generate_days_for_period( $start_date, '', $product_id ),
				'month'		=> date( "F",strtotime($start_date) ),
				'year'		=> date( "Y",strtotime($start_date) ),
			)
		);
		exit();
	}
}
new phive_booking_ajax_manager();