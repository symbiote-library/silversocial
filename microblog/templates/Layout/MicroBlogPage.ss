<div id="content">
<% if CurrentMember %>
	<div class="followersList">
		<% if CurrentMember.Following %>
		<h3>People you follow</h3>
		<% control CurrentMember.Following %>
		<p><a href="$Top.Link(user)/$ID">$Title</a></p>
		<% end_control %>
		<% end_if %>

		<% if CurrentMember.Followers %>
		<h3>People following you</h3>
		<% control CurrentMember.Followers %>
		<p><a href="$Top.Link(user)/$ID">$Title</a></p>
		<% end_control %>
		<% end_if %>
	</div>

	<% if ViewingUserID %>
	<input id="ViewingUser" value="$ViewingUserID" type="hidden" />
	<% else %>
		$StatusForm
	<% end_if %>
<% else %>
	$Content
<% end_if %>

<% if CanFollow %>
	$FollowForm
<% end_if %>
<% if CanUnFollow %>
	$UnFollowForm
<% end_if %>
<% if UserFeed %>
<div id="UserFeed">
	<% control UserFeed %>
	<div class="microPost">
		<h3>Posted by $Owner.Title at $Created.Nice</h3>
		<div class="microPostContent">
			$formattedPost
		</div>
	</div>
	<% end_control %>
</div>
<% end_if %>

<script id="MicroPostTmpl" type="text/x-jquery-tmpl">
	<div class="microPost newPost">
		<h3>Posted by \${Author} at \${Date}</h3>
		<div class="microPostContent">
			\${Content}
		</div>
	</div>
</script>
</div>