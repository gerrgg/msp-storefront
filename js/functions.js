jQuery(document).ready(function( $ ){
    var msp = {
        $modal: $('#msp_modal'),

        init: function(){
            this.init_owl_carousel();
            this.init_slideout();

            $(document.body).on( 'click', 'i.msp-star-rating', msp.bind_create_review_star_buttons )
            $('#msp_review').on( 'click', '.remove-product-image-from-review', msp.delete_user_product_image )
            $('#msp_submit_question').on( 'blur', 'input[name="question"]', msp.customer_faq_validate_question )
            $('#msp_submit_question').on( 'click', 'button', msp.customer_submit_question )
            $('#msp_customer_faq').on( 'click', '.msp-submit-answer', msp.customer_submit_awnser )
            $('#filter-button').click(function(){
                $('#shop-filters').slideToggle();
            });

            this.$modal.on( 'show.bs.modal', this.route )
            this.$modal.on( 'submit', 'form', this.submit )
        },

        init_slideout: function(){
            var slideout = new Slideout({
                'panel': document.getElementById('page'),
                'menu': document.getElementById('mobile-menu'),
                'padding': 300,
                'tolerance': 70,
                'touch': false,
            });
            
            document.querySelector('.mobile-menu-button').addEventListener('click', function() {
                $('#mobile-menu').show();
                slideout.toggle();
            });

            document.querySelector('a.close').addEventListener('click', close );

            function close(eve) {
                eve.preventDefault();
                slideout.close();
              }
              
            slideout
                .on('beforeopen', function() {
                  this.panel.classList.add('panel-open');
                })
                .on('open', function() {
                  this.panel.addEventListener('click', close);
                })
                .on('beforeclose', function() {
                  this.panel.classList.remove('panel-open');
                  this.panel.removeEventListener('click', close);
                });
        },

    
        init_owl_carousel: function(){
            $('.owl-carousel').owlCarousel({
                responsiveClass: true,
                margin: 10,
                nav: false,
                responsive:{
                    0:{
                        items:2,
                        stagePadding: 15,
                    },
                    450:{
                        items:3,
                    },
                    1000:{
                        items:5,
                    }
                }
            })
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
          },

          route: function( e ){
            let $button = $(e.relatedTarget);
      
            var path = {
              title: $button.attr('data-title'),
              model: $button.attr('data-model'),
              action: $button.attr('data-action'),
              id: $button.attr('data-id'),
            }
      
            msp.$modal.find('.modal-title').text( path.title );
            msp[ path.model ]( path.action, path.id );

          },

          submit: function( e ){ // this obviously wont work for other modal submissions.
            e.preventDefault();
            console.log( e );
            let body = msp.$modal.find('.modal-body');
            let action = $(e.target).find('input[name="action"]').val()
            let model = $(e.target).find('input[name="model"]').val()
            let data = {
              action: action,
              form_data: $(e.target).serialize(),
            }

            $.post( wp_ajax.url, data, function( response ) { 
              msp[ model ]( 'post', '', response ) 
            } );
            
          },

          close: function(){
            msp.$modal.modal( 'toggle' );
          },

          ['size_guide']: function( action, id ){
            $.post(wp_ajax.url, { action: 'msp_get_product_size_guide_src', id: id }, function( response ){
               msp.$modal.find('.modal-body').html( $('<img/>', { src: response, class: 'mx-auto' } ) )
            });
          },

          ['leave_feedback']: function( action, id, response ){
              let body = msp.$modal.find('.modal-body');

              switch( action ){
                case 'get':
                    $.post( wp_ajax.url, { action: 'msp_get_leave_feedback_form', id: id }, function( response ){
                      body.html( response );
                    } );
                break;
                case 'post':
                    console.log( response );
                    if( ! response ){
                      body.find('.feedback').text( 'Feedback requires atleast a star rating; thanks!' );
                    } else {
                      body.html(` <div class="text-center">
                                    <i class="fas fa-check-circle fa-2x text-success"></i>
                                    <h1>Thank you for your feedback!</h1>
                                  </div>`);
                      setTimeout(function(){
                        msp.close();
                      }, 3000);
                    }
                break;
              }
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