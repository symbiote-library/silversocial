
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
					'memberType': 'PublicProfile',
					'memberID': $(this).parents('div.FriendsDashlet').find('input[name=MemberID]').val(),
					'followerType': 'PublicProfile',
					'followerID': $(this).attr('data-id')
				};

				SSWebServices.post('microBlog', 'addFriendship', params, function (data) {
					
				})
			}
		})
		
		$('a.deleteFriend').entwine({
			onclick: function () {
				var params = {
					'relationshipType':		'Friendship',
					'relationshipID':		$(this).attr('data-id')
				};
				
				var _this = $(this);
				SSWebServices.post('microBlog', 'removeFriendship', params, function (data) {
					_this.parent().fadeOut();
				})
			}
		})
	})
	
})(jQuery);