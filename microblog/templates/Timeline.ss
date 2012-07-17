	
<% if Posts %>
<% loop Posts %>
	<div class="microPost" data-id="$ID" data-parent="$ParentID" id="post$ID">
		<div class="microPostContent">
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
					<% else %>
					$Content.Parse(BBCodeParser)
					<% end_if %>
				<% else %>
					<% if OwnerID == $CurrentMember.ID %>
					<div class="edit-placeholder"><em>Click to update</em></div>
					<% end_if %>
				<% end_if %>

			<% end_if %>
			
			<p class="postOptions">
				<span class="upCount">$Up</span> - <span class="downCount">$Down</span>
				<a href="#" class="vote" data-dir="1" data-id="$ID">Up</a>
				<a href="#" class="vote" data-dir="-1" data-id="$ID">Down</a>
				<a href="#" class="replyToPost">reply</a>
				<% if Deleted %>
				<% else %>
					<% if checkPerm('Delete') %>
					<a href="#" class="deletePost">delete</a>
					<% end_if %>
				<% end_if %>
			</p>
			<!-- note that the action is left blank and filled in with JS because otherwise the
				recursive template loses context of what to fill in, so we use our top level form -->
			<form method="POST" action="" class="replyForm">
				<input type="hidden" value="$SecurityID" name="SecurityID" />
				<input type="hidden" name="ParentID" value="$ID" />
				<textarea placeholder="Add reply..." name="Content" class="expandable postContent"></textarea>
				<input type="submit" value="Reply" name="action_savepost" />
			</form>
			
			<div class="postReplies">
			<% if Replies %>
			<% include Timeline %>
			<% end_if %>
			</div>
		</div>
	</div>
	<% end_loop %>
<% end_if %>