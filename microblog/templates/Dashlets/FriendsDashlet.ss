<% if CurrentMember %>

	<% if $CurrentMember.ID != $OwnerID %>
		<!-- add the user we're looking at as a friend -->
		
	<% else %>
		$FriendSearchForm
		
		<div class="friendsSearchList">
			
		</div>
	<% end_if %>
	
	<!-- list of this user's friends -->
	<% with Owner.Friends %>
	
	<% end_with %>
<% else %>
	Please login
<% end_if %>
