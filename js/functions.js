jQuery(document).ready(function( $ ){
    var msp = {
        init: function(){
            msp.init_owl_carousel();
            msp.bind_karma_buttons();
            $(document.body).on( 'click', 'i.msp-star-rating', msp.bind_create_review_star_buttons )
            $('#msp_review').on( 'click', '.remove-product-image-from-review', msp.delete_user_product_image )
            $('#msp_submit_question').on( 'blur', 'input[name="question"]', msp.customer_faq_validate_question )
            $('#msp_submit_question').on( 'click', 'button', msp.customer_submit_question )
            $('#msp_customer_faq').on( 'click', '.msp-submit-answer', msp.customer_submit_awnser )
        },

        alert: function( message, type = 'primary' ){
            return $('<div/>', { class: 'alert alert-' + type, role: 'alert' }).text( message );
        },

        customer_faq_validate_question: function( e ){
            let question = $('#msp_submit_question input[name="question"]').val();
            if( question.length > 10 ){
                $('#msp_submit_question_btn').prop( 'disabled', false );
            } else {
                $('#msp_submit_question_btn').prop( 'disabled', true );
            }

            return ( question.length > 10 );
        },

        customer_submit_awnser: function( e ){
            let $parent = $(e.target).parent();
            let answer = $(e.delegateTarget).find( 'input[name="answer"]' ).val();
            
            if( answer.length > 0 ){
                let data = { 
                    action: 'msp_process_customer_submit_awnser',
                    form_data: $parent.serialize()
                }
                $.post( wp_ajax.url, data, function( response ){
                    console.log( response );
                    if( response > 0 ){
                        $parent.html( msp.alert( 'Thanks for your help!', 'success' ) );
                    } else {
                        $parent.append( msp.alert( 'Something went wrong, please try again', 'error' ) );
                    }
                } );
            }
        },

        customer_submit_question: function( e ){
            if( msp.customer_faq_validate_question() ){
                let data = {
                    action: 'msp_process_customer_submit_question',
                    formdata: $('#msp_submit_question *').serialize()
                }

                $.post( wp_ajax.url, data, function( response ){
                    if( response > 0 ){
                        $('#msp_submit_question').html( msp.alert( 'We just sent your question to our best people, we\'ll email you when we get an awnser', 'success' ) );
                    }
                } );
            }
        },

        delete_user_product_image: function( e ){
            $parent = $(e.target).parent();

            let data = {
                action: 'msp_delete_user_product_image',
                id: $(e.target).data('id')
            }

            $.post( wp_ajax.url, data, function( response ){
                console.log( response );
                if( response >= 1 ){
                    $parent.fadeOut();
                }
            });
        },

        init_owl_carousel: function(){
            $('.owl-carousel').owlCarousel({
                margin:10,
                responsiveClass:true,
                stagePadding: 20,
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

        bind_create_review_star_buttons: function( e ){
            let rating = $(e.target).data('rating');
            $('.msp-star-rating').removeClass( 'fas' );

            console.log( rating );

            for( let i = 1; i <= 5; i++ ){
                let star_class = ( i <= rating ) ? 'fas' : 'far';
                $('i.msp-star-rating.rating-' + i).addClass(star_class);
            }
            
            $('#rating').val( rating );
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

        get_json_from_url: function(url) {
            // https://stackoverflow.com/questions/8486099/how-do-i-parse-a-url-query-parameters-in-javascript

            if( ! url ) url = location.href;
            var question = url.indexOf("?");
            var hash = url.indexOf("#");
            if(hash==-1 && question==-1) return {};
            if(hash==-1) hash = url.length;
            var query = question==-1 || hash==question+1 ? url.substring(hash) : 
            url.substring(question+1,hash);
            var result = {};
            query.split("&").forEach(function(part) {
              if(!part) return;
              part = part.split("+").join(" "); // replace every + with space, regexp-free version
              var eq = part.indexOf("=");
              var key = eq>-1 ? part.substr(0,eq) : part;
              var val = eq>-1 ? decodeURIComponent(part.substr(eq+1)) : "";
              var from = key.indexOf("[");
              if(from==-1) result[decodeURIComponent(key)] = val;
              else {
                var to = key.indexOf("]",from);
                var index = decodeURIComponent(key.substring(from+1,to));
                key = decodeURIComponent(key.substring(0,from));
                if(!result[key]) result[key] = [];
                if(!index) result[key].push(val);
                else result[key][index] = val;
              }
            });
            return result;
          }
    }

    msp.init();

    $('#msp_select2_products').select2({
        ajax: {
              url: wp_ajax.url, // AJAX URL is predefined in WordPress admin
              dataType: 'json',
              delay: 250, // delay in ms while typing when to perform a AJAX search
              data: function (params) {
                    return {
                      q: params.term, // search query
                      action: 'msp_get_products' // AJAX action for admin-ajax.php
                    };
              },
              processResults: function( data ) {
              var options = [];
              if ( data ) {

                  // data is the array of arrays, and each of them contains ID and the Label of the option
                  $.each( data, function( index, text ) { // do not forget that "index" is just auto incremented value
                      options.push( { id: text[0], text: text[1]  } );
                  });

              }
              return {
                  results: options
              };
          },
          cache: true
      },
      minimumInputLength: 3 // the minimum of symbols to input before perform a search
  });

});