	<% loop Posts %>
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
			$Content.Parse(BBCodeParser)
			<% else %>
				<% if OwnerID == $CurrentMember.ID %>
				<div class="edit-placeholder"><em>Click to update</em></div>
				<% end_if %>
			<% end_if %>

			<p>
			<abbr class="timeago postTime" title="$Created" data-created="$Created">$Created.Nice</abbr> by $Owner.Title
			</p>
			
			<!-- note that the action is left blank and filled in with JS because otherwise the
				recursive template loses context of what to fill in, so we use our top level form -->
			<form method="POST" action="" class="replyForm">
				<input type="hidden" value="$SecurityID" name="SecurityID" />
				<input type="hidden" name="ParentID" value="$ID" />
				<textarea placeholder="Add reply..." name="Content" class="expandable"></textarea>
				<input type="submit" value="Reply" name="action_savepost" />
			</form>
			
			<% if Replies %>
			<div class="postReplies">
			<% include Timeline %>
			</div>
			<% end_if %>
		</div>
	</div>
	<% end_loop %>