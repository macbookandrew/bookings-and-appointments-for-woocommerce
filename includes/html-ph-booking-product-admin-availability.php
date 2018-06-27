<style>
table.xa_availability_table {
	margin: 5px;
	width: 95%;
}
tr.rule td {
	padding: 5px;
	/*text-align: center;*/
	vertical-align: middle;
}

a.close {
	text-decoration: none;
    color: #d4d4d4;
}
td.remove:hover{
	cursor: pointer;
	background: #f00;
	color: #fff!important;
}
td.remove{
	text-align: center;;
}
.nonworking-wraper {
    padding: 10px;
}
.nonworking-title{
	display: inline-block;
}
.nonworking-title {
	width: 35%;
	line-height: 1.3;
	font-weight: 600;
}

</style>
<div id='booking_availability' class='panel woocommerce_options_panel'>
	<?php
	$rules = get_post_meta( $post->ID, 'booking_availability_rules', 1 );
	if( empty($rules) ){
		$rule = array();
	}else{
		$rule = $rules[0];
	}?>
	<div class="" id="availability_wraper">
		<input type="hidden" name="ph_booking_is_bookable[]" value="no">	
		<input type="hidden" name="ph_booking_availability_type[]" value="time-all">
		<div class="nonworking-wraper">
			<div class="nonworking-title"><?php _e('Non-working hours (All days)', 'bookings-and-appointments-for-woocommerce') ?></div>
			<span><?php _e('From','bookings-and-appointments-for-woocommerce') ?>&nbsp;&nbsp;<input type="time" class="time-picker" name="ph_booking_from_time[]" value="<?php echo isset($rule['from_time']) ? $rule['from_time'] : '' ?>" placeholder="HH:MM"></span>
			<span>&nbsp;&nbsp;&nbsp;<?php _e('To','bookings-and-appointments-for-woocommerce') ?>&nbsp;<input type="time" class="time-picker" name="ph_booking_to_time[]" value="<?php echo isset($rule['to_time']) ? $rule['to_time'] : '' ?>" placeholder="HH:MM"></span>
		</div>
		<div class="nonworking-wraper">
			<div class="nonworking-title"><?php _e('Non-working days (Weekend)','bookings-and-appointments-for-woocommerce') ?></div>
			<span><input type="checkbox" name="holiday_saturday" value="yes" <?php if( isset($rule['holiday_saturday']) && $rule['holiday_saturday']=='yes' ) echo "checked";?> > <?php _e('Saturday','bookings-and-appointments-for-woocommerce') ?></span>
	  		<span>&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="holiday_sunday" value="yes" <?php if( isset($rule['holiday_sunday']) && $rule['holiday_sunday']=='yes') echo "checked";?>> <?php _e('Sunday','bookings-and-appointments-for-woocommerce') ?></span>
		</div>
	</div>
</div>