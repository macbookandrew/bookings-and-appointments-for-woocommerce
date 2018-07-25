<?php
class phive_booking_callender{
	public function __construct() {

	}

	private function get_all_bookings_for_product($product_id=''){
		global $wpdb;
		global $woocommerce;

		if( !$product_id ){
			global $product;
			$product_id = $product->get_id();
		}

		//Query for getting booked dates
		$query = "SELECT t1.meta_key , t1.meta_value , t1.order_item_id, t2.order_id
			FROM {$wpdb->prefix}woocommerce_order_itemmeta AS t1
			LEFT JOIN {$wpdb->prefix}woocommerce_order_items as t2 on t1.order_item_id = t2.order_item_id
			WHERE t1.order_item_id
			IN (
				SELECT order_item_id
				FROM {$wpdb->prefix}woocommerce_order_itemmeta
				WHERE	meta_key = '_product_id'
				AND	meta_value = $product_id
			)AND (
				meta_key = 'From'
				OR meta_key = 'To'
			)
			ORDER BY	t1.order_item_id DESC LIMIT 0,300";
		$booked = $wpdb->get_results( $query, OBJECT );

		//Query for getting freezed dates
		$query_post = "SELECT meta_key , meta_value , post_id as order_item_id, post_id as order_id
			FROM {$wpdb->prefix}postmeta AS t1
			WHERE t1.post_id
			IN (
				SELECT post_id
				FROM {$wpdb->prefix}postmeta
				WHERE meta_key = '_product_id'
				AND meta_value = $product_id
			)AND (
				meta_key = 'From'
				OR meta_key = 'To'
			)
			ORDER BY  t1.post_id DESC LIMIT 0,300";
		$freezed = $wpdb->get_results( $query_post, OBJECT );
		$booked_array = array_merge( $booked, $freezed);

		$processed = array();
		$booked_date_time = array();

		foreach ($booked_array as $key => $value) {
			if( !empty($value->order_id) ){
				$order = wc_get_order($value->order_id);
				if( is_object($order) && $order->get_status() == 'cancelled' ){
					continue;
				}
			}

			if( $value->meta_key == 'From' ){
				$from_date = substr($value->meta_value, 0, 10);
				$processed[ $value->order_item_id ]['from'] = $value->meta_value;
			}

			if( $value->meta_key=='To' ){
				$processed[ $value->order_item_id ]['to'] = $value->meta_value;
			}
		}

		//if TO is missing, concider FROM as TO
		foreach ($processed as $key => &$value) {
			if( empty($value['to']) ){
				$value['to'] = $value['from'];
			}
		}

		return $processed;

	}

	public function output_callender(){
		global $product;
		$this->phive_set_product_properties( $product->get_id() );
		$interval_period = $product->get_interval_period();
		switch ($interval_period) {
		  case 'hour':
		  case 'minute':
			include('html-ph-booking-timepicker.php');
			break;

		  case 'month':
			include('html-ph-booking-monthpicker.php');
			break;

		  default:
			include('html-ph-booking-datepicker.php');
			break;
		}
	}

	/**
	* Check availability from availability table
	* @return Bool
	*/
	private function is_available( $sart_time, $interval='', $product_id){
		if( !empty($interval) )
			$end_time = strtotime( "+$interval", $sart_time );
		else
			$end_time = $sart_time;

		$available = true;

		foreach ($this->availability_rules as $key => $rule) {
			if ( strtotime( date( 'H:i', $sart_time ) ) >= strtotime( $rule['from_time'] ) && strtotime( date( 'H:i', $sart_time ) ) < strtotime( $rule['to_time'] )
				// && strtotime( date( 'H:i', $end_time ) ) >= strtotime( $rule['from_time'] ) && strtotime( date( 'H:i', $end_time ) ) <= strtotime( $rule['to_time'] )
				){

				//In case of Time calender, the next available time sould start on end of current non-avaiable time.
				$this->break_end_time = strtotime( date( 'Y-m-d', $sart_time )." ".$rule['to_time'] );
				$available = false;
			}
			if( isset($rule['holiday_saturday']) && $rule['holiday_saturday']=='yes' && date('N', $sart_time)== 6 ){
				$this->break_end_time = '';
				$available = false;
			}
			if( isset($rule['holiday_sunday']) && $rule['holiday_sunday']=='yes' && date('N', $sart_time)== 7 ){
				$this->break_end_time = '';
				$available = false;
			}
		}
		return apply_filters( 'ph_is_available', $available, $sart_time, $interval, $product_id, $this->availability_rules );
	}

	/**
	* check if Already bocked.
	* @return bool
	*/
	private function is_booked_date( $date, $product_id ){
		if( empty($this->booked_dates ) ){
			$this->booked_dates = $this->get_all_bookings_for_product( $product_id );
		}
		foreach ( $this->booked_dates as $order_item_id => $booked_detail ) {
			//if date in between booked from and to
			if( isset($booked_detail['from']) && isset($booked_detail['to'])
		 	 && $date >= strtotime($booked_detail['from']) && $date <= strtotime($booked_detail['to']) ){
				return true;
			}
		}
		return false;
	}

	/**
	* check if there is slot for book whole period.
	* @return bool
	*/
	private function is_bookable($date, $intravl_period, $product_id){
		if($this->interval_type!== 'fixed')
			return true;

		if( !empty($this->interval) && $this->interval > 1 ){
			for ($i=1; $i < $this->interval; $i++) {
				$date = strtotime( date ( "Y-m-d", strtotime( "+".$intravl_period, $date ) ) );
				//if already taken or not avialable (Set as not available in available table)
				if( $this->is_booked_date($date, $product_id) || !$this->is_available( $date, "", $product_id ) ){
					return false;
				}
			}
		}
		return true;
	}

	public function phive_generate_days_for_period( $start_date, $end_date='', $product_id='' ){
		$end_date 	= ( empty($end_date) ) ? strtotime( "+1 month", strtotime($start_date) ) : strtotime($end_date);

		if( empty($product_id) ){
			global $product;
			$product_id = $product->get_id();
		}

		$this->phive_set_product_properties( $product_id );

		$interval 	= get_post_meta( $product_id, "_phive_book_interval", 1 );


		// if fixed interval, in case of booking last days of month, want to display days of next month to complete a period
		if( !empty($this->interval) && $this->interval > 1 ){
			$end_date = strtotime( date ( "Y-m-d", strtotime( "+".($this->interval-1)." day", $end_date ) ) );
		}

		$day_order = array('monday','tuesday','wednesday','thursday','friday','saturday','sunday');
		$callender_days = '';

		//Align date to print under corresponding week day name
		foreach ($day_order as $day) {
			if( $day == strtolower( date( "l", strtotime($start_date) ) ) ){
				break;
			}
			$callender_days .='<li class="ph-calendar-date"></li>';
		}

		$curr_date = strtotime($start_date);
		$deleteme = 0;
		while ($curr_date < $end_date) {
			$css_classes = array("ph-calendar-date");

			// if today.
			if( $curr_date == strtotime(date("Y-m-d") ) ){
				$css_classes[] = 'today';
			}
			// if Past date.
			if( $curr_date < strtotime(date("Y-m-d") )  ){
				$css_classes[] = 'past-time';
			}
			// if already taken.
			if( $this->is_booked_date( $curr_date, $product_id ) ){
				$css_classes[] = 'de-active';
				$css_classes[] = 'booking-full';
			}
			// if there is no slot for book whole period.
			if( !$this->is_available( $curr_date, "", $product_id ) ){
				$css_classes[] = 'de-active';
				$css_classes[] = 'not-available';
			}
			// if there is no slot for book whole period.
			if( !$this->is_bookable( $curr_date, "1 day", $product_id ) ){
				$css_classes[] = 'non-bookable-slot';
			}

			// if date of next month (Case of Fixed Interval).
			if( date('m', $curr_date ) != date( 'm',strtotime($start_date) ) ){
				$css_classes[] = 'past-time';
			}

			$css_classes = implode( ' ', array_unique($css_classes) );
			$callender_days .= '<li class="'.$css_classes.'"> <input type="hidden" class="callender-full-date" value="'.date( "Y-m-d", $curr_date ).'">'.date( "d", $curr_date ).'</li>';

			$curr_date = strtotime( date ( "Y-m-d", strtotime( "+1 day", $curr_date ) ) );
		}
		return apply_filters( 'ph_generate_days_for_period', $callender_days, $start_date, $end_date, $product_id );
	}

	public function phive_generate_month_for_period( $start_date, $end_date='', $product_id='' ){
		if( empty($product_id) ){
			global $product;
			$product_id = $product->get_id();
		}

		$this->phive_set_product_properties( $product_id );

		$end_date = ( empty($end_date) ) ? strtotime( "+3 year", strtotime($start_date) ) : strtotime($end_date);
		$callender_days = '';

		$curr_date = strtotime($start_date);
		while ($curr_date < $end_date) {
			$css_classes = array('ph-calendar-date');

			// if already taken.
			if( $this->is_booked_date( $curr_date, $product_id ) ){
				$css_classes[] = 'de-active';
				$css_classes[] = 'booking-full';
			}

			// if there is no slot for book whole period.
			if( !$this->is_bookable( $curr_date, "1 month", $product_id ) ){
				$css_classes[] = 'non-bookable-slot';
			}

			// if there is no slot for book whole period.
			if( !$this->is_available( $curr_date, "1 month", $product_id ) ){
				$css_classes[] = 'de-active not-available';
			}

			$css_classes = implode( ' ', array_unique($css_classes) );
			$callender_days .= '<li class="'.$css_classes.'"> <input type="hidden" class="callender-full-date" value="'.date( "Y-m", $curr_date ).'">'.date_i18n( "M-y", $curr_date ).'</li>';

			$curr_date = strtotime( date ( "Y-m-d", strtotime( "+1 month", $curr_date ) ) );
		}
		return $callender_days;
	}


	private function is_working_time( $date, $product_id ){
		$time = strtotime( date('H:i',$date) );

		//if time falls in working hours
		if( (empty($this->shop_opening_time) && empty($this->shop_closing_time))
			|| $time >= strtotime($this->shop_opening_time) && $time <= strtotime($this->shop_closing_time) ){
			return true;
		}
		return false;
	}

	public function phive_generate_time_for_period( $sart_time, $end_time='', $product_id='' ){
		if( !$product_id ){
			global $product;
			$product_id = $product->get_id();
		}

		$this->phive_set_product_properties( $product_id );

		$end_time 		= ( empty($end_time) ) ? strtotime( "+1 day", strtotime($sart_time) ) : strtotime($end_time);

		$loop_breaker=200;
		while ( strtotime($sart_time) < $end_time && $loop_breaker>0 ) {

			$is_break_time = false;
			$css_classes = array('ph-calendar-date');
			$sart_time = strtotime($sart_time);
			if( $this->is_working_time( $sart_time, $product_id ) ){
				// if already taken.
				if( $this->is_booked_date( $sart_time, $product_id ) ){
					$css_classes[] = 'de-active';
					$css_classes[] = 'booking-full';
				}
				// if Past date.
				if( $sart_time < strtotime(date("Y-m-d H:i") ) ){
					$css_classes[] = 'past-time';
				}
				// if there is no slot for book whole period.
				if( !$this->is_available( $sart_time, "+$this->interval $this->interval_period", $product_id ) ){
					if( !empty($this->break_end_time) ){
						$sart_time = date( 'Y-m-d H:i', $this->break_end_time );
						continue;
					}else{
						$css_classes[] = 'de-active';
						$css_classes[] = 'not-available';
					}
				}
				$css_classes = implode( ' ', array_unique($css_classes) );
				echo '<li class="'.$css_classes.'"> <input type="hidden" class="callender-full-date" value="'.date( "Y-m-d H:i", $sart_time ).'">'.date( "H:i", $sart_time ).'</li>';

			}

			$sart_time = date( 'Y-m-d H:i', strtotime( "+$this->interval $this->interval_period", $sart_time ) );

			$loop_breaker--;
		}
	}

	private function phive_set_product_properties( $product_id ){
		$this->shop_opening_time 	= get_post_meta( $product_id, "_phive_book_working_hour_start", 1 );
		$this->shop_closing_time 	= get_post_meta( $product_id, "_phive_book_working_hour_end", 1 );

		$this->interval_type 		= get_post_meta( $product_id, "_phive_book_interval_type", 1 );
		$this->interval 			= get_post_meta( $product_id, "_phive_book_interval", 1 );
		$this->interval_period		= get_post_meta( $product_id, "_phive_book_interval_period", 1 );

		$availability_rules 		= get_post_meta( $product_id, 'booking_availability_rules', 1 );
		$this->availability_rules 	= !empty($availability_rules) ? $availability_rules : array();
	}

}
