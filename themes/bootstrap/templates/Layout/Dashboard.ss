
<input id="DashboardID" value="$Dashboard.ID" type="hidden" />
<input id="DashboardUrl" value="$Link" type="hidden"/>

<%-- $DashboardForm --%>

<div class="row">
$Dashboard
</div>

<div class="row">
	<a class="btn" data-dialog="true" href="{$Link}edit/$Dashboard.ID">Update Dashboard</a>
	<a class="btn" data-dialog="true" href="{$Link}adddashlet">Add dashlet</a>
</div>