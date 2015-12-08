jQuery(document).ready(function($) {
	var metabox = $('#ml_box');
	$('label[for=trapp-start]', metabox).hover(
		function() {
			console.log('yo');
			$('.description', metabox).css('display', 'block');
		}, function() {
			$('.description', metabox).css('display', 'none');
		}
	);
});
