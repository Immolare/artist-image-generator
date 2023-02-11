(function( $ ) {
    'use strict';
    $(function() {
        $('.add_as_media').on('click', function (e) {
            e.preventDefault();
            const $that = $(this);
            const data = {
                'action': 'add_to_media',
                'url': $that.parent().find('img').attr('src'),
                'description': $('#prompt').val()
            };
    
            $that.parent().find('h2 > .spinner').addClass('is-active');
            jQuery.post(aig_ajax_object.ajax_url, data, function(response) {
                if (response.success) {
                    $that.hide( "slow", function() {
                        $that.parent().find('h2 > .spinner').removeClass('is-active').remove();
                        $that.parent().find('h2 > .dashicons').addClass('is-active');
                    });
                }
            });
    
            return false;
        });
    });
})( jQuery );
