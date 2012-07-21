<% if CurrentMember %>
	<input type="hidden" value="$Owner.ID" name="MemberID" />
	<% if $CurrentMember.ID != $OwnerID %>
		<!-- add the user we're looking at as a friend -->
		
	<% else %>
		$FriendSearchForm
		
		<div class="friendsSearchList">
			
		</div>
	<% end_if %>
	
	<!-- list of this user's friends -->
	<% loop Owner.Friends %>
	<div class="userFriend">
		<a href="$Link">
			<img src="http://www.gravatar.com/avatar/{$Owner.gravatarHash}.jpg?s=24" />
			$FirstName $Surname
		</a>
	</div>
	<% end_loop %>
<% else %>
	Please login
<% end_if %>
