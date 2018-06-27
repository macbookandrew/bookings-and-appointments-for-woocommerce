<div class="time-picker-wraper">
	<input type="hidden" id="book_interval_type" value="<?php echo $product->get_interval_type()?>">
	<input type="hidden" id="book_interval" value="<?php echo $product->get_interval()?>">

	<div class="callender-error-msg">&nbsp;</div>
	<div class="ph-calendar-month">			
		<ul>
			<li>
				<div class="fixed-date-wraper" >
					<img class="callender-ico" src="<?php echo plugin_dir_url( dirname( __FILE__ ) ).'/booking-callender/icons/callender.png'?>">
					<span class="date-diaplyer">
	        			<?php echo date_i18n('d-M-Y');?>
					</span>
					<input type="text" class="callender-fixed-date" value="<?php echo date('Y-m-d');?>" style="opacity: 0 !important; filter: alpha(opacity=0)!important;"/>
				</div>
			</li>
		</ul>
	</div>

	<ul class="ph-calendar-days">	<?php
		date_default_timezone_set('UTC');
		$shop_opening_time 	= get_post_meta( $product->get_id(), "_phive_book_working_hour_start", 1 );

		$shop_opening_time = !empty( $shop_opening_time ) ? date( 'H:i',strtotime($shop_opening_time) ) : '00:00';
		
		// Start date
		// $start_date = date('Y').'-'.date('m').'-01';
		$start_date = date('Y-m-d').' '.$shop_opening_time;
		// End date
		$this->phive_generate_time_for_period($start_date, '');
		?>
	</ul>
</div>
<div class="booking-info-wraper">
	<p id="booking_info_text"> </p>
	<p id="booking_price_text"> </p>
</div>
