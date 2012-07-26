
window.Microblog = window.Microblog || {};
Microblog.Member = {};

;(function($) {
	$(function () {
		if ($('#MemberDetails').length) {
			var member = $('#MemberDetails').data('member');
			Microblog.Member = member ? member : {};
		}

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