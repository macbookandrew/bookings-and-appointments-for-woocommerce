jQuery(document).ready(function($) {
	function generate_booking_info_text(from, to, cost){
		if( from !== to ){
			date_html = "<b>"+phive_booking_ajax.booking_date+":</b>&nbsp;"+from+"&nbsp;to&nbsp;"+to;
		}else{
			date_html = "<b>"+phive_booking_ajax.booking_date+":</b>&nbsp;"+from;
		}
		$('.booking-info-wraper').html('<p id="booking_info_text">'+date_html+'</p> <p id="booking_price_text"> '+phive_booking_ajax.booking_cost+':&nbsp;'+cost+'</p>');
	}

	jQuery( ".ph-date-to" ).on('change', function(){
		from = jQuery( ".ph-date-from" ).val();
		to = jQuery( ".ph-date-to" ).val();

		loding_ico_url = $("#plugin_dir_url").val()+ "includes/booking-callender/icons/loading.gif";
		$(".booking-info-wraper").html('<img class="loading-ico" align="middle" src="'+loding_ico_url+'">');

		product_id = jQuery( "#phive_product_id" ).val();

		if( from.length === 0 || to.length === 0){
			return;
		}

		var data = {
			action: 'phive_get_booked_price',
			// security : phive_booking_ajax.security,
			product_id: product_id,
			book_from: from,
			book_to: to,
		};

		$.post( phive_booking_ajax.ajaxurl, data, function(res) {
			result = jQuery.parseJSON(res);
			// $(".price").html( result.price_html ); //to change the main product price

			$("#phive_booked_price").val(result.price);

			generate_booking_info_text( result.from_date, result.to_date, result.price_html );
		});
	});


	$(document).on("change", ".callender-fixed-date", function(){
		product_id = jQuery( "#phive_product_id" ).val();
		var data = {
			action: 'phive_get_booked_datas_of_date',
			// security : phive_booking_ajax.security,
			product_id: product_id,
			date: $(this).val(),
		};

		$.post( phive_booking_ajax.ajaxurl, data, function(res) {
			$(".ph-calendar-days").html(res);
		});
	})

});
