<div class="month-picker-wraper">
	<input type="hidden" id="book_interval_type" value="<?php echo $product->get_interval_type()?>">
	<input type="hidden" id="book_interval" value="<?php echo $product->get_interval()?>">

	<div class="callender-error-msg">&nbsp;</div>
	<div class="ph-calendar-month">	
		<ul>
			<li><?php _e('Pick Month(s)','bookings-and-appointments-for-woocommerce')?></li>
		</ul>
	</div>

	<ul class="ph-calendar-days">	<?php
		$start_date = date('Y-m');
		// End date
		echo $this->phive_generate_month_for_period($start_date, '');
		?>
	</ul>
</div>

<div class="booking-info-wraper">
	<p id="booking_info_text"> </p>
	<p id="booking_price_text"> </p>
</div>
