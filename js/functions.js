jQuery(document).ready(function( $ ){
    var msp = {
        init: function(){
            msp.init_owl_carousel();
        },

        init_owl_carousel: function(){
            console.log( 'init carousel' );
            $('.owl-carousel').owlCarousel({
                loop:true,
                margin:10,
                responsiveClass:true,
                responsive:{
                    0:{
                        items:1,
                        nav:true
                    },
                    600:{
                        items:8,
                        nav:false
                    },
                    1000:{
                        items:14,
                        nav:true,
                        loop:false
                    }
                }
            })
        }
    }

    msp.init();

});