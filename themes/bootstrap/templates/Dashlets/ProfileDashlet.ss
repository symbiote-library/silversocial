
<% if Owner %>
	<div class="userProfile">
	<% if Owner.Avatar %>
	<% else %>
	
	<div class="gravatarImage">
		<img src="http://www.gravatar.com/avatar/{$Owner.gravatarHash}.jpg" />
	</div>
		
	<% end_if %>
	
	<p>$Owner.Title (<span class="ownerVotes" data-id="$Owner.ID">$Owner.VotesToGive</span>)</p>
	
	
	
	</div>
	
<% if $Owner.ID == $CurrentMember.ID %>
<div class="settingsForm">
$SettingsForm	
</div>
<% end_if %>

<% else %>

Please <a href="Security/login">login</a>
<% end_if %>