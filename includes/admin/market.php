<style>
    .box14{
        width: 25%;
        margin-top:15px;
        min-height: 310px;
        margin-right: 10px;
        padding:10px;
        position:absolute;
        z-index:1;
        right:0px;
        float:right;
        background: -webkit-gradient(linear, 0% 20%, 0% 92%, from(#fff), to(#f3f3f3), color-stop(.1,#fff));
        border: 1px solid #ccc;
        -webkit-border-radius: 60px 5px;
        -webkit-box-shadow: 0px 0px 35px rgba(0, 0, 0, 0.1) inset;
    }
    .box14_ribbon{
        position:absolute;
        top:0; right: 0;
        width: 130px;
        height: 40px;
        background: -webkit-gradient(linear, 555% 20%, 0% 92%, from(rgba(0, 0, 0, 0.1)), to(rgba(0, 0, 0, 0.0)), color-stop(.1,rgba(0, 0, 0, 0.2)));
        border-left: 1px dashed rgba(0, 0, 0, 0.1);
        border-right: 1px dashed rgba(0, 0, 0, 0.1);
        -webkit-box-shadow: 0px 0px 12px rgba(0, 0, 0, 0.2);
        -webkit-transform: rotate(6deg) skew(0,0) translate(-60%,-5px);
    }
    .box14 h3
    {
        text-align:center;
        margin:2px;
    }
    .box14 p
    {
        text-align:center;
        margin:2px;
        border-width:1px;
        border-style:solid;
        padding:5px;
        border-color: rgb(204, 204, 204);
    }
    .box14 span
    {
        background:#fff;
        padding:5px;
        display:block;
        box-shadow:green 0px 3px inset;
        margin-top:10px;
    }
    .box14 img {
        width: 40%;
        padding-left:30%;
        margin-top: 5px;
    }
    .table-box-main {
        box-shadow: 0 1px 3px rgba(0,0,0,0.12), 0 1px 2px rgba(0,0,0,0.24);
        transition: all 0.3s cubic-bezier(.25,.8,.25,1);
    }

    .table-box-main:hover {
        box-shadow: 0 14px 28px rgba(0,0,0,0.25), 0 10px 10px rgba(0,0,0,0.22);
    }
</style>
<div class="box14 table-box-main">
    <img src="<?php echo plugins_url( '../../resources/images/plus-version.png', __FILE__ )?>">
    <h3><?php echo __('Woocommerce Bookings And Appointments' , 'bookings-and-appointments-for-woocommerce');?></h3><br/>
  
    <span>
        <ul>
            <li> - <?php _e('Allow multiple bookings for a time slot.', 'bookings-and-appointments-for-woocommerce'); ?></li> 
            <li> - <?php _e('Add Participants/Materials to the booking.', 'bookings-and-appointments-for-woocommerce'); ?></li> 
            <li> - <?php _e('Add Resources', 'bookings-and-appointments-for-woocommerce'); ?></li>
            <li> - <?php _e('Set special prices or discounts', 'bookings-and-appointments-for-woocommerce'); ?></li>
            <li> - <?php _e('Create booking availability rules as you wish.', 'bookings-and-appointments-for-woocommerce'); ?></li>
            <li> - <?php _e('Allow booking cancellation', 'bookings-and-appointments-for-woocommerce'); ?></li>
            <li> - <?php _e('Configure the bookings to require confirmation from the Admin', 'bookings-and-appointments-for-woocommerce'); ?></li>
        </ul>
    </span>
    <br />
    <center>
        <a href="//pluginhive.com/product/woocommerce-booking-and-appointments/" target="_blank" class="button button-primary"><?php echo __('Upgrade to Premium','bookings-and-appointments-for-woocommerce');?></a> 
        <a href="//woocommercebookings.pluginhive.com/?hash=ec10e38356980502e191007aa81127f7" target="_blank" class="button button-primary"><?php echo __('Live Demo' , 'bookings-and-appointments-for-woocommerce');?></a>
        <!-- <a href="//pluginhive.com/documentation/" target="_blank" class="button button-primary"><?php //echo __('Documentation' , 'bookings-and-appointments-for-woocommerce');?></a> -->
    </center>
</div>