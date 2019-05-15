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

          submit: function( e ){
            e.preventDefault();
            let body = msp.$modal.find('.modal-body');
            let data = {
              action: 'msp_process_feedback_form',
              form_data: $(e.target).serialize(),
            }

            $.post( wp_ajax.url, data, function( response ){
              console.log( response );
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