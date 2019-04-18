jQuery(document).ready(function( $ ){

    var header = {
        $msp_header: $('#masthead'),
        browsing_history: '',

        init: function(){
            this.init_slideout();
            this.set_browsing_history();

            //events
            this.$msp_header.on( 'mouseenter', '.user-history', function(){
                $('#browsing-history-block').show();
            });

            this.$msp_header.on( 'mouseleave', '.user-history', function(){
                $('#browsing-history-block').hide();
            });
            
        },

        init_slideout: function(){
            var slideout = new Slideout({
                'panel': document.getElementById('page'),
                'menu': document.getElementById('mobile-menu'),
                'padding': 356,
                'tolerance': 70
            });
            
            document.querySelector('.mobile-menu-button').addEventListener('click', function() {
                $('#mobile-menu').show();
                slideout.toggle();
            });
          
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

        set_browsing_history: function(){
            $.post( wp_ajax.url, { action: 'msp_get_user_browsing_history' }, function( data ){
                header.browsing_history = data;
            } );
        },

        get_browsing_history: function(){
            return header.browsing_history;
        },

        spinner: function(){
            return `<div class="spinner-border" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>`;
        },

    }
  
    header.init();
  });