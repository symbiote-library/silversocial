<div class="row">
	
    $Content
	
	<div class="span4">
	<h3>Login now</h3>
    $Form
	
	<% if CurrentMember %>
	<% else %>
	$Facebook
	$Google
	<% end_if %>
	
	</div>
	
	<div class="span4">
	
	<h3>
		Or register below
	</h3>
	
	$RegisterForm
	</div>
	
</div>
