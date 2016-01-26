jQuery(document).ready(function($) {
    var fields = $('#bp-trapp-locked-fields').data('fields').split(' ');

    $.each(fields, function(key, value) {
        $('[name="' + value + '"]').prop('disabled', true);
    });

});
