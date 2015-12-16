jQuery(document).ready(function($) {
    var metabox = $('#ml_box');
    $('label[for=trapp-start]', metabox).hover(
        function() {
            $('.description', metabox).css('display', 'block');
        }, function() {
            $('.description', metabox).css('display', 'none');
        }
    );
});
