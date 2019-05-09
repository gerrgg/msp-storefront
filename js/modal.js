jQuery( function ( $ ){
    var msp = {
        $modal: $('#msp_modal'),

        init: function(){
            this.$modal.on( 'show.bs.modal', this.route )
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

          ['size_guide']: function( action, id ){
            $.post(wp_ajax.url, { action: 'msp_get_product_size_guide_src', id: id }, function( response ){
               msp.$modal.find('.modal-body').html( $('<img/>', { src: response, class: 'mx-auto' } ) )
            });
          }


    }
    msp.init();
});