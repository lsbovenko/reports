(function (G) {
    $(".js-disable-after-submit").on('submit', function () {
        $(this).find('[type="submit"]').attr('disabled','disabled');
    });

    //filter
    var $form = $("#fiter");
    $form.find('select').on('change', function (e) {
        $form.submit();
    });

    G._helpers = {
       overlay: {
           show: function() {
               $('#overlay').show();
           },
           hide: function() {
               $('#overlay').hide();
           }
       }
    };

    $( document ).ajaxStart(function() {
        G._helpers.overlay.show();
    });

    $( document ).on( 'ajaxSend', function(event, jqxhr) {
        jqxhr.setRequestHeader( 'X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content') );
    } );

    $( document ).ajaxComplete(function() {
        G._helpers.overlay.hide();
    });

})(window);

