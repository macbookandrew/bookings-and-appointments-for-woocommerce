jQuery(document).ready(function($) {
	const full_month = ["January", "February", "March", "April", "May", "June",
		  "July", "August", "September", "October", "November", "December"
		];
	/********* html-ph-booking-add-to-cart.php *********/
	
	$(".single_add_to_cart_button").click(function(e){
		if( $(".ph-date-from").val() == '' && $(".ph-date-to").val() == ''){
			$(".callender-error-msg").html( phive_booking_locale.pick_booking ).focus();
			e.preventDefault();
		}
	})


	/*********** Booking Calendar ************/

	$(document).on("click", ".non-bookable-slot", function(){
		if( $(this).hasClass('de-active') ){
			loop_elm = $(this)
		}else{
			loop_elm 	= $(this).nextAll(".de-active:first");
		}
		show_not_bookable_message( loop_elm );
	})

	//If change here change in same function coded in includes->booking-callender->html-ph-booking-datepicker.php
	function show_not_bookable_message(loop_elm){ 
		if( loop_elm.length == 0 ){ //case of last item or no de-active after curent elm.
			return;
		}
		
		from 			= loop_elm.find(".callender-full-date").val();
		from_date_obj 	= new Date(from);
		from_date 		= from_date_obj.getUTCDate();
		month 			= phive_booking_locale.months[from_date_obj.getUTCMonth()]
		
		msg_html = "";
		while( loop_elm.length > 0 ){
			to 			= $(loop_elm).find(".callender-full-date").val();
			to_date_obj 		= new Date(to);
			to_date 	= to_date_obj.getUTCDate();
			month 		=  phive_booking_locale.months[to_date_obj.getUTCMonth()]

			date_text = ( from.length == 7 ) ? '' : to_date; //in case of month callender, no need to show date
			
			if( msg_html.length > 0 ){
				msg_html = msg_html.replace(" and", ", ");
				msg_html += " and <b>"+month+" "+date_text+"</b>";
			}else{
				msg_html = "<b>"+month+" "+date_text+"</b>";
			}

			loop_elm = loop_elm.next(".de-active");
		}
		
		if( from_date == to_date ){
			msg_html = "<b>"+month+" "+from_date+"</b> "+phive_booking_locale.is_not_avail;
		}else{
			msg_html += " "+phive_booking_locale.are_not_avail;
		}

		$('.booking-info-wraper').html('<p id="booking_info_text"><span class="not-available-msg">'+msg_html+'</span></p>');
	}


	date_from = '';
	date_to = '';
	click = 0;

	$.fn.isAfter = function(sel){
		return this.prevAll(sel).length !== 0;
	}
	$.fn.isBefore= function(sel){
		return this.nextAll(sel).length !== 0;
	}

	function resetSelection(){
		date_from = '';
		date_to = '';
		click = 0;

		$(".selected-date").each(function(){
			$(this).removeClass("selected-date");
			$(".ph-date-from").val("");
			$(".ph-date-to").val("");
		});
		$("#booking-info-wraper").html("");

		$(".single_add_to_cart_button").addClass("not-allowed");
	}
	
	/******** Date Picker *********/

	//Callender date picker
	$(".date-picker-wraper").on("click", ".ph-calendar-date", function(){
		$(".callender-error-msg").html("&nbsp;");
		//if click on already booked or past date
		if( $(this).hasClass("de-active") || $(this).hasClass("past-time") ){
			return
		}
		$(".single_add_to_cart_button").removeClass("not-allowed");
		
		//if Fixed range, don't allow to choose second date
		if( $("#book_interval_type").val() == 'fixed' ){
			resetSelection();
			$(".single_add_to_cart_button").removeClass("not-allowed");
			$(this).addClass("selected-date");
			book_to = $(this);
			for (var i = $("#book_interval").val(); i > 1; i--) {
				book_to = book_to.next().addClass("selected-date");
				if( book_to.hasClass('de-active') ){
					resetSelection();
					return;
				}
			};

			date_from 	= $(this).find(".callender-full-date").val();
			date_to 	= book_to.find(".callender-full-date").val();
			$(".ph-date-from").val(date_from);
			$(".ph-date-to").val(date_to).change();
			return;
		}

		//if date_from and date_to set, reset all for third click
		if( date_from!='' && date_to!="" ){
			resetSelection();
			return;
		}

		click++;

		
		//if selected a date_to, which is past to date_from
		if( date_from !='' && date_to=='' && $(this).isBefore( ".selected-date" ) ){
			$('.booking-info-wraper').html('<p id="booking_info_text"><span class="not-available-msg">'+phive_booking_locale.pick_later_date+'</span></p>');
			resetSelection()
			return;
		}

		//if click for FROM date
		if( date_from == "" || click%2 != 0 || $("#book_interval_type").val() != 'customer_choosen' ){
			date_from = $(this).find(".callender-full-date").val();
			$(".ph-date-from").val(date_from);
			$(".ph-date-to").val(date_from).change();
			$(this).addClass("selected-date");
		}
		//click for TO date
		else{
			el = $("input[value='"+date_from+"']").closest("li");
			if( !el.length ){
				el = $( "li.ph-calendar-date" ).first();
			}

			//loop untill next clicked date or deactived date found. 
			while(el){
				if( !el.hasClass("de-active") ) { 
					el.addClass("selected-date")
				}

				if( el.hasClass("de-active") ){
					resetSelection();
					show_not_bookable_message(el);
					return;
				}
				else if( this === el.get(0) ){
					date_to = el.find(".callender-full-date").val();
					el = false;
				}else{
					el = el.next();
				}
			}
			$(".ph-date-to").val( date_to ).change(); //trigger .ph-date-to for update the price
		}
	});

	$(".date-picker-wraper").on("click", ".ph-next", function(){
		product_id = jQuery( "#phive_product_id" ).val();
		month = jQuery( ".callender-month" ).val();
		year = jQuery( ".callender-year" ).val();
		var data = {
			action: 'phive_get_callender_next_month',
			// security : phive_booking_ajax.security,
			product_id: product_id,
			month: month,
			year: year,
		};
		$.post( phive_booking_ajax.ajaxurl, data, function(res) {
			result = jQuery.parseJSON(res);
			$(".ph-calendar-days").html(result.days);
			jQuery( ".callender-month" ).val(result.month)
			jQuery( ".callender-year" ).val(result.year)

			jQuery( ".span-month" ).html( phive_booking_locale.months[full_month.indexOf(result.month)] )
			jQuery( ".span-year" ).html(result.year)
		});
	})
	
	$(".date-picker-wraper").on("click", ".ph-prev", function(){
		product_id = jQuery( "#phive_product_id" ).val();
		month = jQuery( ".callender-month" ).val();
		year = jQuery( ".callender-year" ).val();
		var data = {
			action: 'phive_get_callender_prev_month',
			// security : phive_booking_ajax.security,
			product_id: product_id,
			month: month,
			year: year,
		};
		$.post( phive_booking_ajax.ajaxurl, data, function(res) {
			result = jQuery.parseJSON(res);
			
			$(".ph-calendar-days").html(result.days);
			
			jQuery( ".callender-month" ).val(result.month)
			jQuery( ".callender-year" ).val(result.year)

			jQuery( ".span-month" ).html( phive_booking_locale.months[full_month.indexOf(result.month)] )
			jQuery( ".span-year" ).html(result.year)
		});
	})



	/******* Month Picker *********/

	$(".month-picker-wraper").on("click", ".ph-calendar-date", function(){
		//if click on already booked of past date
		if( $(this).hasClass("de-active") || $(this).hasClass("past-time") ){
			return
		}

		resetSelection()
		$(this).addClass("selected-date");
		date_from 	= $(this).find(".callender-full-date").val();
		
		//if Fixed range, don't allow to choose second date
		if( $("#book_interval_type").val() == 'fixed' ){
			book_to = $(this);
			for (var i = $("#book_interval").val(); i > 1; i--) {
				book_to = book_to.next().addClass("selected-date");
				if( book_to.hasClass('de-active') ){
					resetSelection();
					return;
				}
			};
		}
		date_to = $(".selected-date").last().find(".callender-full-date").val();

		$(".ph-date-from").val(date_from);
		$(".ph-date-to").val(date_to).change();

	});


	/******** Time Picker *********/

	//Callender date picker
	$(".time-picker-wraper").on("click", ".ph-calendar-date", function(){
		//if click on already booked of past date
		if( $(this).hasClass("de-active") || $(this).hasClass("past-time") ){
			return
		}
		resetSelection();
		
		date_from 	= $(this).find(".callender-full-date").val();
		date_to 	= $(this).find(".callender-full-date").val();
		$(".ph-date-from").val(date_from);
		$(this).addClass("selected-date");
		$(".ph-date-to").val(date_to).change();

	});

	//Callender time picker
	jQuery( ".callender-fixed-date" ).datepicker({
		showOtherMonths: true,
		selectOtherMonths: true,
		dateFormat: 'yy-mm-dd',
	});

	$(".time-picker-wraper").on("change", ".callender-fixed-date", function(){
		var date = new Date( $(this).val() );
		// getDate, getMonth, getFullYear will give local info so it will give previous day detail for the zone who is behind UTC
		var month = phive_booking_locale.months_short[date.getUTCMonth()];
		var strDate = date.getUTCDate() + '-' + month + '-' + date.getUTCFullYear();
		$(".date-diaplyer").html( strDate );

		resetSelection();
	});
});