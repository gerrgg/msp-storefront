jQuery(document).ready(function( $ ){
    var msp = {
        init: function(){
            msp.init_owl_carousel();
            msp.init_drop_zone();
            msp.bind_create_review_star_buttons();
            msp.bind_karma_buttons();
        },

        init_drop_zone: function(){
            Dropzone.autoDiscover = false;
            $('.dropzone').dropzone({
                url: wp_ajax.post
            });
        },

        init_owl_carousel: function(){
            console.log( 'init carousel' );
            $('#browsing-history-block').owlCarousel({
                margin:10,
                responsiveClass:true,
                responsive:{
                    0:{
                        items:4,
                        nav:true
                    },
                    600:{
                        items:8,
                        nav:false
                    },
                    1000:{
                        items:14,
                        loop:false
                    }
                }
            })

            $('.owl-carousel').owlCarousel({
                margin:10,
                responsiveClass:true,
                nav: true,
                responsive:{
                    0:{
                        nav: false,
                        items:2,
                    },
                    450:{
                        items:3,
                        nav: true,
                    },
                    1000:{
                        items:5,
                        nav: true,
                    }
                }
            })
        },

        bind_create_review_star_buttons: function(){
            $('i.msp-star-rating').click(function(){
                let rating = $(this).data('rating');
                $('.msp-star-rating').removeClass( 'fas' );

                for( let i = 1; i <= 5; i++ ){
                    let star_class = ( i <= rating ) ? 'fas' : 'far';
                    $('i.msp-star-rating.rating-' + i).addClass(star_class);
                }

               
                $('#rating').val( rating );
            });
        },

        bind_karma_buttons: function(){
            $('i.karma').click( function(){
                if( $(this).hasClass('voted') ) return 'Sorry, no.';

                $('i.karma').removeClass( 'voted' );
                let $karma =  $(this).parent().find( '.karma-score' );
                let $button_clicked = $(this);
                
                
                let data = {
                    action: 'msp_update_comment_karma',
                    comment_id: $(this).parent().parent().attr('id').replace('comment-', ''),
                    vote: ( $(this).hasClass( 'karma-up-vote' ) ) ? 1 : -1,
                }
                
                $.post( wp_ajax.url, data, function( response ){
                    console.log( response );
                    if( ! response.length ){
                        $karma.text( response )
                        $button_clicked.addClass( 'voted' );
                    }
                });

            });
        },
    }

    msp.init();

});