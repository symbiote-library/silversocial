	<% loop Me %>
	<div class="microPost" data-id="$ID">
		<div class="microPostContent">
			<% if $Attachment %> 
				<% if $Attachment.ClassName == 'Image' %>
					<a href="$Attachment.Link" target="_blank" title="Download attached file">$Attachment.MaxWidth(450)</a>
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
			<abbr class="timeago postTime" title="$Created" data-created="$Created">$Created.Nice</abbr>
			</p>
		</div>
	</div>
	<% end_loop %>