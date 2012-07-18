<% if Deleted %>
	<% _t('MicroPost.DELETED', '[deleted]') %>
<% else %>

	<p class="posterInfo">
		<abbr class="timeago postTime" title="$Created" data-created="$Created">$Created.Nice</abbr> by <a href="$Owner.Link">$Owner.Title</a>
	</p>
	<% if Attachment %> 
		<% if $Attachment.ClassName == 'Image' %>
			<a href="$Attachment.Link" target="_blank" title="Download attached file">$Attachment.MaxWidth(450)</a>
		<% else %>
		<a href="$Attachment.Link" title="Download attached file">$Attachment.Title</a>
		<% end_if %>
	<% end_if %>
	<% if Content %>
		<% if IsOembed %>
		$Content.Raw
		<% else_if IsImage %>
		<img src="$Content" />
		<% else %>
		$Content.Parse(BBCodeParser)
		<% end_if %>
	<% else %>
		<% if OwnerID == $CurrentMember.ID %>
		<!-- <div class="edit-placeholder"><em>Click to update</em></div> -->
		<% end_if %>
	<% end_if %>

<% end_if %>