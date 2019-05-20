jQuery( function ( $ ){
    var msp = {
        $modal: $('#msp_modal'),

        init: function(){
            this.$modal.on( 'show.bs.modal', this.route )
            this.$modal.on( 'submit', 'form', this.submit )
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
                        msp.$modal.modal( 'toggle' );
                      }, 3000);
                    }
                break;
              }
          }


    }
    msp.init();
});