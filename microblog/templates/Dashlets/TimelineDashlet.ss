<% if CurrentMember %>

	<% if $CurrentMember.ID != $OwnerID %>
		<input type="hidden" name="timelineUpdateUrl" value="$Link(OwnerFeed)" />
		<div id="StatusFeed">
		$OwnerFeed
			<div class="feed-actions">
				<a href="#" class="moreposts">Load more...</a>
			</div>
		</div>
	<% else %>
	<div class="row">
		<div class="postForm span5">
		<% control PostForm %>
		<form $FormAttributes >
			<% with FieldMap %>
			$Content
			<input type="hidden" name="SecurityID" value="$SecurityID" />
			<% end_with %>
			$Actions
		</form>
		<% end_control %>
		</div>
		
		<div class="uploadForm span3">
			<% with $UploadForm %>
			<form $FormAttributes>
				<% with FieldMap %>
				<input type="hidden" name="SecurityID" value="$SecurityID" />
				$Attachment
				<% end_with %>
				<div id="dropZone"></div>
				<ul id="uploadedFiles"></ul>
			</form>
			<% end_with %>
		
		</div>
	</div>
	
	<input type="hidden" name="timelineUpdateUrl" value="$Link(Timeline)" />
	
	<div id="StatusFeed">
	$Timeline
		<div class="feed-actions">
			<a href="#" class="moreposts">Load more...</a>
		</div>
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
	
<% else %>
	Please login
<% end_if %>
