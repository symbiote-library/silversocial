	<% loop Me %>
	<div class="microPost" data-id="$ID">
		<div class="microPostContent">
			<% if $Attachment %> 
				<% if $Attachment.ClassName == 'Image' %>
					$Attachment.MaxWidth(450)
				<% else %>
				<a href="$Attachment.Link" title="Download attached file">$Title</a>
				<% end_if %>
			<% end_if %>
			<% if Content %>
			$Content
			<% else %>
				<% if OwnerID == $CurrentMember.ID %>
				<div class="edit-placeholder"><em>Click to update</em></div>
				<% end_if %>
			<% end_if %>
			
			<p>
			<abbr class="timeago postTime" title="$Created">$Created.Nice</abbr>
			</p>
		</div>
	</div>
	<% end_loop %>