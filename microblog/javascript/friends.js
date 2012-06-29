
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
				var params = {
					'memberType': 'Member',
					'memberID': $(this).parents('div.FriendsDashlet').find('input[name=MemberID]').val(),
					'followerType': 'Member',
					'followerID': $(this).attr('data-id')
				};

				SSWebServices.post('microBlog', 'addFriendship', params, function (data) {
					console.log(data);
				})
			}
		})
	})
	
})(jQuery);