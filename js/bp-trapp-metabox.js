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

jQuery(document).ready(function($) {
    $('#title').prop('disabled', true);
    $('input[name="model"]').prop('disabled', true);
    $('input[name="yoast_wpseo_focuskw_text_input"]').prop('disabled', true);

});
