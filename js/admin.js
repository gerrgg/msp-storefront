jQuery( function( $ ){
    var msp_admin = {

        init: function(){
           this.update_stock_widget();
           $('.color-field').wpColorPicker();
           $('#resource_tab').on( 'click', 'button.add_input_line', msp_admin.add_line_item );
           $('#msp-product-video').on( 'click', 'button.add', msp_admin.add_video_line );
           $('#msp-front-page-builder').on( 'click', 'button.add', msp_admin.submit_promo_option )
        },

        submit_promo_option(e){
            // get inputs
            var inputs = $(e.delegateTarget).find('input');

            // setup for ajax
            var data = {
                action: 'msp_submit_theme_option',
                options: []
            }

            $.each(inputs, function(x, element){
                data.options[x] = { key: element.name, value: element.value }
            });

            // we are counting rows, its two inputs per row so we divide by 2. This is important for saving to the right key.
            var count = ( data.options.length / 2 ) + 1

            // Save options and create another line to 'form'.
            $.post( ajaxurl, data, msp_admin.add_front_page_line(e, count) )
        },

        add_front_page_line(e, count){
            let button = $(e.target);
            let $table = $(e.delegateTarget);
            
            $table.append(
                $('<tr/>').append(
                    '<td>' + '<input type="text" name="msp_promo_link_'+ count +'">' + '</td>',
                    '<td>' + '<input type="text" name="msp_promo_src_'+ count +'">' + '</td>',
                )
            );

            console.log( count );
        
        },

        update_stock_widget: function(){
            var data = { action: 'msp_admin_sync_vendor', vendor: '', url: '' };
            let form = $('#msp_add_update_stock_form');
            let select = form.find( 'select[name="vendor"]' );
            let url = form.find( 'input[name="url"]' );

            
            form.on( 'change', select, function(){
                $('.feedback').html("");
                if( $(select).val() == 'portwest' ){
                    data.url = 'http://www.portwest.us/downloads/sohUS.csv';
                    $(url).val(data.url);
                } else {
                    $('.feedback').html("Helly Hansen requires a url. <br>Please go to <a href='https://app.ivendix.com/'>iVendix</a> and enter the url emailed to you; above. Thanks!");
                    $(url).val('');
                }
            }).on( 'click', '#submit_update_vendor', function( e ){
                $('.feedback').html( 'Request Sent!, Thanks.<br>' );
                data.vendor = $(select).val();
                data.url = $(url).val();
                $.post( ajaxurl, data, function( response ){
                    $('.feedback').html( response );
                });
            })
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
} );
