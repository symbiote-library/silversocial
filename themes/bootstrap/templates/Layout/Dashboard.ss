
<input id="DashboardID" value="$Dashboard.ID" type="hidden" />
<input id="DashboardUrl" value="$Link" type="hidden"/>

<%-- $DashboardForm --%>

<div class="row">
$Dashboard
</div>

<% if $Dashboard.OwnerID == $CurrentMember.ID %>
<div id="dashlet-editing" class="navbar navbar-fixed-bottom">
	<div class="navbar-inner">
	<a class="btn" data-dialog="true" href="{$Link}edit/$Dashboard.ID">Update Dashboard</a>
	<a class="btn" data-dialog="true" href="{$Link}adddashlet">Add dashlet</a>
	<a class="btn" id="editDashlets" href="#">Edit dashlets</a>
	</div>
</div>
<% end_if %>