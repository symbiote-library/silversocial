
<% if Owner %>
	<% if Owner.Avatar %>
	<% else %>
	<div class="gravatarImage">
		<img src="http://www.gravatar.com/avatar/$Owner.gravatarHash" />
	</div>
	<% end_if %>
	
	$Owner.Title

	<p>
		<a href="Security/logout">Logout</a>
	</p>

<% else %>

Please <a href="Security/login">login</a>
<% end_if %>