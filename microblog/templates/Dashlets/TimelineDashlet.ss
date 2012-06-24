<% if CurrentMember %>
	<% if $CurrentMember.ID != $OwnerID %>
		
	<% else %>
	<div class="row">
		<div class="postForm span5">
		<% control PostForm %>
		<form $FormAttributes >
			<% with FieldMap %>
			$Content
			<input type="hidden" name="SecurityID" value="$SecurityID" />
			<% end_with %>
			<br/>
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
	<% end_if %>
	
	<div id="UserFeed">
		<% if OwnerFeed %>
			<% control OwnerFeed %>
			<div class="microPost">
				<h3>Posted by $Owner.Title at $Created.Nice</h3>
				<div class="microPostContent">
					<% if $Attachment && $Attachment.ClassName == 'Image' %>
					<img src="$Attachment.Link" />
					<% end_if %>
					$Content
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
