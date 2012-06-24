<div id="UserFeed">
	<% loop Me %>
	<div class="microPost">
		<h3>Posted by $Owner.Title at $Created.Nice</h3>
		<div class="microPostContent">
			<% if $Attachment && $Attachment.ClassName == 'Image' %>
			<img src="$Attachment.Link" />
			<% end_if %>
			$Content
		</div>
	</div>
	<% end_loop %>
</div>