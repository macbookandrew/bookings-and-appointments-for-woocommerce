<div class="tablenav <?php echo esc_attr( $which ); ?>" >

	<div class="ph-list-bulkaction-wraper">
		<?php $this->bulk_actions( $which );?>
	</div>

	<div class="ph-list-filter-wraper">

		<div class="ph-filter-item">
			<?php
			$args = array(
			    'type' => 'phive_booking',
			);
			$products = wc_get_products( $args );
			?>
			<select name="ph_filter_product_ids" id="ph_filter_product_ids" class="wc-enhanced-select ph_filter_product_ids"><?php
				echo '<option value="">'.__( 'Choose product', 'bookings-and-appointments-for-woocommerce' ).'</option>';
				foreach ( $products as $key => $product ) {
					if( !empty($product) ){
						echo '<option ' . selected( $ph_filter_product_ids, $product->get_id() ) . ' value="' . $product->get_id() .'">' . $product->get_name() .'</option>';
					}
				}?>
			</select>

		</div>

		<div class="ph-filter-item">
			<input type="text" id="ph_filter_from" class="ph_filter_from" placeholder="<?php _e('From', 'bookings-and-appointments-for-woocommerce')?>" value="<?php echo $ph_filter_from;?>">
		</div>

		<div class="ph-filter-item">
			<input type="text" id="ph_filter_to" class="ph_filter_to" placeholder="<?php _e('To', 'bookings-and-appointments-for-woocommerce')?>" value="<?php echo $ph_filter_to;?>">
		</div>

		<div class="ph-filter-item">
			<input type="button" class="button btn_filter" id="btn_filter" value="Filtrer">
		</div>

		<br class="clear">

	</div>

	<div class="ph-list-pagination-wraper">
		<?php $this->pagination( $which );?>
	</div>

</div>
<script>
jQuery(document).ready(function($) {

	$("#btn_filter").on("click", function(){
		
		admin_url = '<?php echo admin_url("admin.php?page=all-bookings&paged=1")?>';
		filter_product_ids 	= $("#ph_filter_product_ids").val() ? $("#ph_filter_product_ids").val() : '';
		filter_from 		= $("#ph_filter_from").val() 		? $("#ph_filter_from").val() 		: '';
		filter_to 			= $("#ph_filter_to").val() 			? $("#ph_filter_to").val() 			: '';

		window.location = admin_url + "&ph_filter_product_ids="+filter_product_ids+"&ph_filter_from="+filter_from+"&ph_filter_to="+filter_to;
	});

	jQuery( "#ph_filter_from" ).datepicker({
		showOtherMonths: true,
		selectOtherMonths: true,
		dateFormat: 'yy-mm-dd',
	});

	jQuery( "#ph_filter_to" ).datepicker({
		showOtherMonths: true,
		selectOtherMonths: true,
		dateFormat: 'yy-mm-dd',
	});
})
</script>