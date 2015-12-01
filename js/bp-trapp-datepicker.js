jQuery(document).ready(function($) {
	$('#trapp-deadline').datepicker({
        dateFormat: 'yy-mm-dd',
        minDate: $('#trapp-deadline').val()
    });
});
