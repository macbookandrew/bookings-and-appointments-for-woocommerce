jQuery(document).ready(function($) {
	function generate_booking_info_text(from, to, cost){
		if( from !== to ){
			date_html = "<b>"+phive_booking_ajax.booking_date+":</b>&nbsp;"+formate_date(from)+"&nbsp;to&nbsp;"+formate_date(to);
		}else{
			date_html = "<b>"+phive_booking_ajax.booking_date+":</b>&nbsp;"+formate_date(from);
		}
		$('.booking-info-wraper').html('<p id="booking_info_text">'+date_html+'</p> <p id="booking_price_text"> '+phive_booking_ajax.booking_cost+':&nbsp;'+cost+'</p>');
	}

	function formate_date( input_date ){
		var date = new Date( input_date.replace(/-/g, "/") ); //Safari bowser will accept only with seprator '/'

		var month = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
		"Jul", "Aug", "Sep", "Oct", "Nov", "Dec"][date.getMonth()];
		var strDate = date.getDate() + '-' + month + '-' + date.getFullYear();
		return strDate;
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

			//in the case of monthpicker, take last date of 'TO' month
			if( (to.match(new RegExp("-", "g")) || []).length < 2 ){
				var date = new Date( to.replace(/-/g, "/") ); //Safari bowser will accept only with seprator '/'
				var LastDay = new Date(date.getFullYear(), date.getMonth() + 1, 0);
				to = to+"-"+LastDay.getDate();
			}
			generate_booking_info_text( from, to, result.price_html );
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
