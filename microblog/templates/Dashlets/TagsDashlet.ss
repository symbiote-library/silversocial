
<h2>Tags</h2>

<% if Tags %>
<% loop Tags %>
<p><a href="?tag=$Title.ATT">$Title</a> ($Number)</p>
<% end_loop %>
<% end_if %>