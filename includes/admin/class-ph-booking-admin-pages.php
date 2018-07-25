<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class phive_booking_admin_report {
	public function __construct() {
		add_action(	'admin_menu', array( $this, 'ph_booking_admin_report_menu' ) );
	}

	
	public function ph_booking_admin_report_menu(){	
		add_menu_page('bookings', __('Bookings','bookings-and-appointments-for-woocommerce'), 'manage_options', 'all-bookings', array($this, 'ph_generate_booking_report'), 'dashicons-calendar', 56 );
	}

	function ph_generate_booking_report(){
		include_once('class-ph-booking-admin-reports-list.php');
		$bookings_list = new phive_booking_all_list();

		printf( '<div class="wrap"><h2>%s</h2>', __( 'Bookings', 'bookings-and-appointments-for-woocommerce' ) );
		echo '<form id="booking-list-table-form" method="post">';

		$bookings_list->prepare_items();
		$bookings_list->display();
		
		echo '</form>';
		echo '</div>';
	}

}
new phive_booking_admin_report;