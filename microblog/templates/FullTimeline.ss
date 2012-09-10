
<input type="hidden" value="$PostForm.FormAction" id="PostFormUrl" />

<% if CurrentMember %>

	<div class="uploadForm">
		<% with $UploadForm %>
		<form $FormAttributes>
			<% with FieldMap %>
			<input type="hidden" name="SecurityID" value="$SecurityID" />
			$Attachment
			<% end_with %>
			<ul id="uploadedFiles"></ul>
		</form>
		<% end_with %>
	</div>

	<% if Post %>
		<!--<input type="hidden" name="timelineUpdateUrl" value="timeline/show/$Post" />-->
		<div id="StatusFeed" class="autorefresh">
		$OwnerFeed
		</div>
	<% else_if $CurrentMember.ID != $ContextUser.ID %>
		<input type="hidden" name="timelineUpdateUrl" value="$Link(OwnerFeed)" />
		<div id="StatusFeed" class="autorefresh">
		$OwnerFeed
			<div class="feed-actions">
				<a href="#" class="moreposts">Load more...</a>
			</div>
		</div>
	<% else %>
	<div class="row">
		<div class="postForm span8">
		<% control PostForm %>
		<form $FormAttributes >
			<% with FieldMap %>
			$Content
			<input type="hidden" name="SecurityID" value="$SecurityID" />
			<% end_with %>
			$Actions
			<input type="button" name="uploadTrigger" value="Upload" />
		</form>
		<% end_control %>
		</div>
		
	</div>
	
	<input type="hidden" name="timelineUpdateUrl" value="$Link(Timeline)" />
	
	<div id="StatusFeed" class="autorefresh">
		$Timeline
		<div class="feed-actions">
			<a href="#" class="moreposts">Load more...</a>
		</div>
	</div>
	
	<% end_if %>
	
<% else %>
	<input type="hidden" name="timelineUpdateUrl" value="$Link(OwnerFeed)" />
	
	<div id="StatusFeed" class="autorefresh">
		$OwnerFeed
		<div class="feed-actions">
			<a href="#" class="moreposts">Load more...</a>
		</div>
	</div>
<% end_if %>
