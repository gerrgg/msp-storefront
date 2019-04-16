jQuery(document).ready(function( $ ){

    //init slideout.js
    var slideout = new Slideout({
        'panel': document.getElementById('page'),
        'menu': document.getElementById('mobile-menu'),
        'padding': 356,
        'tolerance': 70
    });
    
    // Toggle button
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

});