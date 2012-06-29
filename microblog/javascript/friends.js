
;(function ($) {
	$.entwine('microblog', function ($) {
		$('#Form_FriendSearchForm').entwine({
			onmatch: function () {
				this.ajaxForm(function (data) {
					$('.friendsSearchList').empty();
					$('.friendsSearchList').append(data);
					return false;
				})
			}
		})
		
		$('input.addFriendButton').entwine({
			onclick: function () {
				
			}
		})
	})
	
})(jQuery);