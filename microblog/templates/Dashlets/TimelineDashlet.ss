<% if CurrentMember %>
	<% if $CurrentMember.ID != $OwnerID %>
		
	<% else %>
		<% control PostForm %>
		<form $FormAttributes >
			$Fields	

			
<br/>
			$Actions
		</form>
		<% end_control %>
		
		<form class="fileUploadForm" enctype="application/x-www-form-urlencoded" method="post" action="$Top.Link(uploadFile)">
			<input id="fileupload" class="fancy-upload" type="file" name="FileUpload" multiple>
			<div id="dropZone" style="height: 50px; border: 1px solid black;"></div>
		</form>
	<% end_if %>
	
	<div id="UserFeed">
		<% if OwnerFeed %>
			<% control OwnerFeed %>
			<div class="microPost">
				<h3>Posted by $Owner.Title at $Created.Nice</h3>
				<div class="microPostContent">
					$formattedPost
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
