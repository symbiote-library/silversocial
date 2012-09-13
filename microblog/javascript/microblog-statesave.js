
;(function ($) {
	
	var KEY = 'saved_state';
	
	var num = 1;
	
	$(function () {
		$.entwine('microblog', function ($) {
			
			$('input[name=action_savepost]').entwine({
				onmatch: function () {
					$(this).click(function () {
						localStorage.removeItem(KEY);
					})
				}
			})

			$('.postContent').entwine({
				onmatch: function () {
					$(this).attr('data-num', num++);
					var _this = this;
					// explicit focus bind because focusin gets called twice...!
					$(this).focus(function () {
						var current = localStorage.getItem(KEY);
						if (!current || $(this).val().length) {
							return;
						}
						if (current == $(this).val()) {
							return;
						}
						$(this).val(current);
						localStorage.removeItem(KEY);
						_this.checkContentSize();
					})
				},
				onkeyup: function () {
					localStorage.setItem(KEY, $(this).val());
				}
			});

		});
	})
})(jQuery);