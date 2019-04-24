jQuery( function( $ ){
    var msp_checkout_form = {
        $checkout_form: $('form.checkout'),

        init: function(){
            this.save_estimated_delivery_date();
            this.$checkout_form.on( 'click', 'input[name="shipping_method[0]"]', this.save_estimated_delivery_date );
        },

        save_estimated_delivery_date: function(){
            

            est_date = $('input[name="shipping_method[0]"]:checked').prev().text();
            
            $.post( wp_ajax.url, { action: 'msp_set_estimated_delivery_date', date: est_date }, function( data ){
                console.log( data );
            } );
        }
    }
    msp_checkout_form.init();
} );
