<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

include('market.php');

if ( !class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class phive_booking_all_list extends WP_List_Table {
	protected $max_items;
	
	public function __construct() {
		parent::__construct( array() );
	}

	
	/**
	* Set the colomn titles
	*/
	public function get_columns() {

		$columns = array(
			'order_id'			=> esc_html( __( 'Order', 'bookings-and-appointments-for-woocommerce' ) ),
			'product'			=> esc_html( __( 'Product', 'bookings-and-appointments-for-woocommerce' ) ),
			'start_date'		=> esc_html( __( 'From', 'bookings-and-appointments-for-woocommerce' ) ),
			'end_date'			=> esc_html( __( 'To', 'bookings-and-appointments-for-woocommerce' ) ),
			'bookedby'			=> esc_html( __( 'Booked by', 'bookings-and-appointments-for-woocommerce' ) ),
		);

		return $columns;
	}

	/**
	* Set sortable columns
	*/
	protected function get_sortable_columns() {

		$sortable_columns = array(
			'order_id'		=> array( 'order_id', true ),
			'product'		=> array( 'product', true ),
			'start_date'	=> array( 'start_date', false ),
			'end_date'		=> array( 'end_date', false ),
			'bookedby'		=> array( 'bookedby', false )
		);

		return $sortable_columns;
	}

	/**
	* Prapare the table content
	*/
	public function prepare_items() {
		
		$this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );
		$current_page		  = absint( $this->get_pagenum() );
		$per_page			  = 20;

		$this->get_items( $current_page, $per_page );

		/**
		 * Pagination
		 */
		$this->set_pagination_args( array(
			'total_items' => $this->max_items,
			'per_page'	=> $per_page,
			'total_pages' => ceil( $this->max_items / $per_page )
		) );
		
	}

	
	private function is_confrimed_all_bookings_of_order($order){

		$is_confrimed = true;

		$items 		= $order->get_items();

		foreach ($items as $order_item_id => $line_item) {

			$_product = wc_get_product( $line_item->get_product_id() );
			
			if( empty($_product) ){
				continue;
			}

			$required_confirmation 	= get_post_meta( $_product->get_id(), "_phive_book_required_confirmation", 1 );
			
			if( $required_confirmation == 'yes' && $line_item->get_meta('confirmed') != 'yes' ){
				$is_confrimed = false;
				break;
			}
		}
		return $is_confrimed;
	}

	/**
	* Disply the table content
	*/
	public function get_items( $current_page, $per_page ) {
		// Get all booked items from orders
		$all_bookings 		= $this->ph_get_all_bookings();
		$filtered_booking 	= $this->ph_filter_bookings_by_search_params( $all_bookings );
		
		//Sort all booking
		usort( $filtered_booking, array( $this, 'order_bookings' ) );
		$this->items 		= $this->ph_filter_bookings_for_current_page( $filtered_booking, $current_page, $per_page );
		
		$this->max_items 	= count( $filtered_booking );
		return;
	}

	private function ph_filter_bookings_by_search_params( $all_bookings ){
		$ph_booking_status 		= isset( $_GET['ph_booking_status'] ) ? $_GET['ph_booking_status'] : array();
		$ph_filter_product_ids 	= isset( $_GET['ph_filter_product_ids']) ? $_GET['ph_filter_product_ids'] : array();
		$ph_filter_from 		= isset( $_GET['ph_filter_from']) ? strtotime($_GET['ph_filter_from']) : '';
		$ph_filter_to 			= isset( $_GET['ph_filter_to']) ? strtotime($_GET['ph_filter_to']) : '';

		foreach ($all_bookings as $key => &$booking) {

			if( !empty($ph_filter_product_ids ) && $booking['product_id'] != $ph_filter_product_ids ){
				unset($all_bookings[$key]);
				continue;
			}
			if( !empty($ph_filter_from ) && !$this->is_time_in_between( strtotime($booking['start']), $ph_filter_from, $ph_filter_to ) ){
				if( !empty($ph_filter_to ) ) {
				 	if( !$this->is_time_in_between( strtotime($booking['end']), $ph_filter_from, $ph_filter_to ) ){
						unset($all_bookings[$key]);
						continue;
					}
				}else{
					unset($all_bookings[$key]);
					continue;
				}
			}
		}

		return $all_bookings;
	}

	/**
	* Check if the given time in between the given time interval.
	* @param $checkme: the time to check
	* @param $lower_range: The min range 
	* @param $heigher_range: The max range 
	* @return Bool
	*/
	private function is_time_in_between( $checkme, $lower_range, $heigher_range ){
		$return = true;
		
		if( !empty($lower_range) && $checkme < $lower_range ){
			$return = false;
		}

		if( !empty($heigher_range) && $checkme >= $heigher_range ){
			$return = false;
		}
		
		return $return;
	}

	/**
	* Give the entries for current page (Pagination)
	*/
	private function ph_filter_bookings_for_current_page( $all_bookings, $current_page, $per_page ){

		// Re-index the array.
		$all_bookings = array_values($all_bookings);
		
		$items_count = count( $all_bookings );
		$min		 = ( $current_page - 1 ) * $per_page;
		$max		 = $min + $per_page;

		if( $items_count < $max ){
			$max = $items_count;
		}

		$items = array();
		for ( $i = $min; $i < $max; $i++ ) {
			$items[] = $all_bookings[$i];
		}
		return $items;
	}

	private function ph_get_all_bookings( $past=true ){

		$query_args = array(
			'post_type'	  => wc_get_order_types(),
			'post_status'	=> array_keys( wc_get_order_statuses() ),
			'posts_per_page' => -1,
		);
		$all_orders	  = get_posts( $query_args );
		
		$bookings = array();
		foreach ( $all_orders as $key => $order ) {
			
			$order = wc_get_order( $order->ID );
			
			// Sanity check
			if ( ! is_object( $order ) ) {
				continue;
			}

			$items = $order->get_items();
		   
			foreach ($items as $order_item_id => $line_item) {

				$_product = wc_get_product( $line_item->get_product_id() );
				
				if( empty($_product) ){
					continue;
				}
		
				$persons_as_booking 	= get_post_meta( $_product->get_id(), "_phive_booking_persons_as_booking", 1 );
				$required_confirmation 	= get_post_meta( $_product->get_id(), "_phive_book_required_confirmation", 1 );
				
				$numberof_persons		= $line_item->get_meta('Number of persons');
				
				$booking_products = array();
				if( $_product->is_type( 'phive_booking' ) ) {
					$i=0;
					do{
						$i++;
						$bookings[] = array(
							'ID'				=> $order_item_id,
							'order_id'			=> $order->get_id(),
							'product_id'		=> $_product->get_id(),
							'start'				=> $line_item->get_meta('From'),
							'end'				=> $line_item->get_meta('To'),
							'bookedby'			=> $order->get_billing_first_name()." ".$order->get_billing_last_name(),
						);
					}while ( $persons_as_booking == 'yes' &&  $i < $numberof_persons );
				}
			}
		}
		return $bookings;
	}
	
	/**
	* Display filter html and pagination html
	*
	*/
	protected function display_tablenav( $which ) {

		$ph_filter_product_ids 	= isset( $_GET['ph_filter_product_ids']) ? $_GET['ph_filter_product_ids'] : '';
		$ph_filter_from 		= isset( $_GET['ph_filter_from']) ? $_GET['ph_filter_from'] : '';
		$ph_filter_to 			= isset( $_GET['ph_filter_to']) ? $_GET['ph_filter_to'] : '';

		if ( ! empty( $filter_id ) ) {
			$_product = wc_get_product( $filter_id );
		}

		include( 'views/html-ph-booking-admin-reports-list-filters.php' );
	}

	/**
	* Content of each columns.
	*/
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {

			case 'order_id' :

				if ( ! empty( $item['order_id'] ) ) {
					$order = wc_get_order( $item['order_id'] );
				} else {
					$order = false;
				}
				
				if ( $order ) {
					echo '<a href="' . esc_url( admin_url( 'post.php?post=' . absint( $order->get_id() ) ) . '&action=edit' ) . '" class="order-view"><strong>#' . esc_attr( $order->get_order_number() ) . '</strong></a>';
				}
			break;

			case 'start_date' :
				//If booking time is there.
				if( strlen($item['start']) > 10 ){
					echo date_i18n( get_option( 'date_format' )." ".get_option( 'time_format' ), strtotime( $item['start'] ) );
				}else{
					echo date_i18n( get_option( 'date_format' ), strtotime( $item['start'] ) );
				}
			break;

			case 'end_date' :
				if ( !empty( $item['end'] ) ) {
					//If booking time is there.
					if( strlen($item['end']) > 10 ){
						echo date_i18n( get_option( 'date_format' )." ".get_option( 'time_format' ), strtotime( $item['end'] ) );
					}else{
						echo date_i18n( get_option( 'date_format' ), strtotime( $item['end'] ) );
					}
				}
			break;

			case 'product' :
				$product = wc_get_product( $item['product_id'] );
				if ( ! $product ) {
					return;
				}

				$product_name = $product->get_formatted_name();
				echo wp_kses_post( $product_name );
			break;

			default :
				echo isset( $item[$column_name] ) ? esc_html( $item[$column_name] ) : '';

			break;
		}
	}

	/**
	* Reorder the entries based on choosed colomn.
	*
	*/
	function order_bookings( $a, $b ) {

		// Default sort by order id
		$orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'order_id';

		// Default sorting order
		$sorting_order = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'asc';

		switch ( $orderby ) {

			case 'order_id' :

				if ( isset( $a['order_id'] ) && isset( $b['order_id'] ) ) {
					$result = ( $a['order_id'] > $b['order_id'] ) ? -1 : 1;
				} else {
					$result = -1;
				}

			break;

			case 'order_status' :
				$result = ( $a['order_status'] > $b['order_status'] ) ? -1 : 1;
			break;

			case 'product' :
				$result = ( $a['product_id'] > $b['product_id'] ) ? -1 : 1;
			break;

			case 'start_date' :
				$a_start_date = strtotime( $a['start'] );
				$b_start_date = strtotime( $b['start'] );

				$result = ( $a_start_date > $b_start_date ) ? -1 : 1;
			break;

			case 'end_date' :
				$a_end_date = strtotime( $a['end'] );
				$b_end_date = strtotime( $b['end'] );

				$result = ( $a_end_date > $b_end_date ) ? -1 : 1;
			break;

			case 'bookedby' :
				$result = ( $a['bookedby'] > $b['bookedby'] ) ? -1 : 1;
			break;

			default:
				// do nothing
			break;

		}
		
		// Send final sort direction to usort
		return ( $sorting_order === 'asc' ) ? $result : -$result;
	}

}