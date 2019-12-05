jQuery( function( $ ){
    var msp_admin = {

        init: function(){
        //    $('.color-field').wpColorPicker();
           $('#resource_tab').on( 'click', 'button.add_input_line', msp_admin.add_line_item );
           $('#msp-product-video').on( 'click', 'button.add', msp_admin.add_video_line );

           // Page builder
           $('#msp-front-page-builder').on( 'click', 'button.add', msp_admin.submit_promo_option )
           $('#msp-front-page-builder').on( 'click', 'button.remove', msp_admin.delete_promo_option )
        },


        
        delete_promo_option(e){
            var tr = $(e.target.parentElement.parentElement);
            var pos = tr.parent().children().index(tr);

            console.log( pos )

            // setup for ajax
            var data = {
                action: 'msp_delete_promo_line',
                target: $('input[name="msp_promo[' + pos + '][image_id]"]').val()
            }

            $.post(ajaxurl, data, function( response ){
                tr.remove();
            });

        },

        submit_promo_option(e){
            // get inputs
            inputs = $(e.delegateTarget).find('input')
            var max = ( inputs.length / 2 ) - 1;

            // setup for ajax
            var data = {
                action: 'msp_create_promo_line',
                options: {}
            }

            for( var i = 0; i <= max; i++){
                var id = $('input[name="msp_promo[' + i + '][image_id]"]').val();
                var link = $('input[name="msp_promo[' + i + '][permalink]"]').val();
                if( id.length > 0 && link.length > 0 ) data.options[id] = link;
            }

            $.post( ajaxurl, data, function( response ){
                msp_admin.add_promo_line(e, max + 1)
            } );


        },

        add_promo_line(e, i){
            let button = $(e.target);
            let $table = $(e.delegateTarget).find('table');
            
            $table.append(
                $('<tr/>').append(
                    '<td>' + '<input type="text" name="msp_promo[' + i + '][image_id]">' + '</td>',
                    '<td>' + '<input type="text" name="msp_promo[' + i + '][permalink]">' + '</td>',
                    '<td><button class="remove" type="button" role="button">&times;</button></td>',
                )
            );
        
        },
        
        add_video_line: function( e ){
            let button = $(e.target);
            let $table = $('#msp_product_video_input_table');
            count = ( ! isNaN( button.attr('data-count') ) ) ? +button.attr('data-count') + 1 : 0;

            $table.append(
                $('<input />', { name: 'product_video['+ count +']' }), 
            );

            button.attr( 'data-count', count++ );
        },

        add_line_item: function( e ){
            let button = $(e.target);
            count = +button.attr('data-count') + 1;

            let $parent = $('#resource_input_wrapper');
            
            let fields = {
                label: $('#resource_label').clone(),
                url: $('#resource_url').clone()
            }
            
            Object.keys( fields ).forEach( function ( field ){
                fields[field].attr({
                    id: '',
                    name: 'resource_' + field + '[' + count + ']'
                });
                $parent.append( fields[field].val('') );
            });

            $parent.append( '<br>' );
            
            $(e.target).attr( 'data-count', count++ );
        }

        

    }
    msp_admin.init();

    if( ! $('#title-length').length ) return;
    $('#title-length').html( $('#gsf_title').val().length );

    $('#gsf_title').keyup(function(){
        $('#title-length').html( $(this).val().length );
    });
    
} );
