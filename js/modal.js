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
            let body = msp.$modal.find('.modal-body');
            let action = $(e.target).find('input[name="action"]').val()
            let data = {
              action: action,
              form_data: $(e.target).serialize(),
            }

            $.post( wp_ajax.url, data, function( response ){
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
            });
            
          },

          ['size_guide']: function( action, id ){
            $.post(wp_ajax.url, { action: 'msp_get_product_size_guide_src', id: id }, function( response ){
               msp.$modal.find('.modal-body').html( $('<img/>', { src: response, class: 'mx-auto' } ) )
            });
          },

          ['leave_feedback']: function(){
              let body = msp.$modal.find('.modal-body');
              $.post( wp_ajax.url, { action: 'msp_get_leave_feedback_form' }, function( response ){
                  body.html( response );
              } );
          }


    }
    msp.init();
});