<style>
.input-item{
	margin: 0px 5px!important;
}
</style>
<div id='booking_options' class='panel woocommerce_options_panel'><?php
	$interval_period 	= get_post_meta( $post->ID, '_phive_book_interval_period', 1);
	$interval_type 		= get_post_meta( $post->ID, '_phive_book_interval_type', 1);
	$interval 			= get_post_meta( $post->ID, '_phive_book_interval', 1);
	$price 				= get_post_meta( $post->ID, '_phive_book_price', 1);
	$opening_time		= get_post_meta( $post->ID, '_phive_book_working_hour_start', 1);
	$closing_tme		= get_post_meta( $post->ID, '_phive_book_working_hour_end', 1);
	?>
	<p class="form-field">
		<label for="_phive_book_price" style="width:30%"><?php _e('Booking Period','bookings-and-appointments-for-woocommerce')?></label>
		<select id="_phive_book_interval_type" name="_phive_book_interval_type" class="input-item">
			<option value="fixed"<?php if($interval_type=='fixed')echo'selected="selected"'; ?> ><?php _e('Fixed period of','bookings-and-appointments-for-woocommerce')?></option>
			<option value="customer_choosen" <?php if($interval_type=='customer_choosen')echo'selected="selected"'; ?> ><?php _e('Enable Calendar Range','bookings-and-appointments-for-woocommerce')?></option>
		</select>
		
		<input type="number" onKeyPress="if(this.value.length==3) return false;" class="short input-item" style="width:50px;" name="_phive_book_interval" id="_phive_book_interval" value="<?php echo $interval;?>" placeholder="1">
		<select id="_phive_book_interval_period" name="_phive_book_interval_period" class="select short input-item" style="width:85px;margin-left: 10px;" >
			<option value="minute" <?php if($interval_period=='minute')echo'selected="selected"'; ?> ><?php _e('Minutes(s)','bookings-and-appointments-for-woocommerce')?></option>
			<option value="hour" <?php if($interval_period=='hour')echo'selected="selected"'; ?>><?php _e('Hour(s)','bookings-and-appointments-for-woocommerce')?></option>
			<option value="day" <?php if($interval_period=='day')echo'selected="selected"'; ?>><?php _e('Day(s)','bookings-and-appointments-for-woocommerce')?></option>
			<!-- <option value="week" <?php //if($interval_period=='week')echo'selected="selected"'; ?>><?php // _e('Week(s','bookings-and-appointments-for-woocommerce')?>)</option> -->
			<option value="month" <?php if($interval_period=='month')echo'selected="selected"'; ?>><?php _e('Month(s)','bookings-and-appointments-for-woocommerce')?></option>
		</select><?php
		echo wc_help_tip( __( "Allow the user to book a fixed unit/block of minutes/hrs/days/months or allow the user to choose a range of days in the calendar", 'bookings-and-appointments-for-woocommerce' ) );?>
	</p>

	<p class="form-field" >
		<label for="_phive_book_price" style="width:30%"><?php _e('Price','bookings-and-appointments-for-woocommerce')?></label>
		<input type="text" class="short input-item" style="width:70px" name="_phive_book_price" id="_phive_book_price" value="<?php echo $price;?>" placeholder=""><?php
		echo wc_help_tip( __("In case of fixed period the price applies to the fixed period chosen by you above. In case of calendar range the price is for per day.",'bookings-and-appointments-for-woocommerce') )?>
	</p>

	<?php

	woocommerce_wp_text_input( array(
		'id'			=> '_phive_book_working_hour_start',
		'label'			=> __( 'First Booking Time', 'bookings-and-appointments-for-woocommerce' ),
		'desc_tip'		=> 'true',
		'value'			=> !empty($opening_time) ? $opening_time : '10:00',
		'description'	=> __( 'When your booking period is in minutes, this will be the start time for your first booking.', 'bookings-and-appointments-for-woocommerce' ),
		'type' 			=> 'time',
		'style'			=> "width: 120px",
	) );
	woocommerce_wp_text_input( array(
		'id'			=> '_phive_book_working_hour_end',
		'label'			=> __( 'Last Booking Time', 'bookings-and-appointments-for-woocommerce' ),
		'desc_tip'		=> 'true',
		'value'			=> !empty($closing_tme) ? $closing_tme : '20:00',
		'description'	=> __( 'When your booking period is in minutes, this will be the start time for your last booking', 'bookings-and-appointments-for-woocommerce' ),
		'type' 			=> 'time',
		'style'			=> "width: 120px;",

	) );
?></div>

<script>
jQuery(document).ready(function($) {
	function toggleInterval(){
		if( $("#_phive_book_interval_type").val() != 'fixed' && $("#_phive_book_interval_period").val() != 'minute' && $("#_phive_book_interval_period").val() != 'hour'){
			$("#_phive_book_interval").hide();
		}else{
			$("#_phive_book_interval").show();
		}
	}
	function toggleWorkingTime(){
		if( $("#_phive_book_interval_period").val() == 'minute' || $("#_phive_book_interval_period").val() == 'hour' ){
			$("._phive_book_working_hour_start_field").show();
			$("._phive_book_working_hour_end_field").show();
		}else{
			$("._phive_book_working_hour_start_field").hide();
			$("._phive_book_working_hour_end_field").hide();
		}
	}
	function toggleCallender(){
		if( $("#_phive_book_interval_type").val()=='customer_choosen' ){
			$("#_phive_book_interval_period").val("day").change();
			$("#_phive_book_interval").val(1).change();
			$("#_phive_book_interval_period").hide();
		}else{
			$("#_phive_book_interval_period").show();
		}
	}
	toggleInterval();
	toggleWorkingTime();
	toggleCallender();
	
	$("#_phive_book_interval_type").change(function(){
		toggleCallender()
		toggleInterval();
	});

	$("#_phive_book_interval_period").change(function(){
		toggleInterval()
		toggleWorkingTime();
	})
})
</script>