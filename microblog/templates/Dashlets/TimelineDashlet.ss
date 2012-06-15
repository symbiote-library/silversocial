<% if CurrentMember %>
	<% if $CurrentMember.ID != $OwnerID %>
		Viewing another user
		
		$CurrentMember.ID  $OwnerID

		<% if ViewingUserID %>
		<input id="ViewingUser" value="$ViewingUserID" type="hidden" />
		<% else %>
		
		<% end_if %>
	<% else %>
		$PostForm
	<% end_if %>
	
	<div id="UserFeed">
		<% if OwnerFeed %>
			<% control OwnerFeed %>
			<div class="microPost">
				<h3>Posted by $Owner.Title at $Created.Nice</h3>
				<div class="microPostContent">
					$formattedPost
				</div>
			</div>
			<% end_control %>
		<% end_if %>
	</div>
	
	<script id="MicroPostTmpl" type="text/x-jquery-tmpl">
		<div class="microPost newPost">
			<h3>Posted by \${Author} at \${Date}</h3>
			<div class="microPostContent">
				\${Content}
			</div>
		</div>
	</script>
	
<% else %>
	Please login
<% end_if %>