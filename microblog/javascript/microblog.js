
window.Microblog = window.Microblog || {};

;(function($) {
	$(function () {
		if ($('input[name=Search]').length) {
			var curVal = $('input[name=Search]').val();
			$('input[name=Search]').focus(function () {
				if ($(this).val() == curVal) {
					$(this).val('');
				}
			})
		}
	})
})(jQuery);