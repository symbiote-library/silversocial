

<div class="row">
	<div class="span3">
		<h3>Category</h3>
		<% control Search.currentFacets(Categories_ms) %>
		<p><a href="$QuotedSearchLink">$Name</a> ($Count)</p>
		<% end_control %>

		<h3>Price</h3>
		<% control Search.currentFacets(Price_mt) %>
		<p><a href="$SearchLink">$Name</a> ($Count)</p>
		<% end_control %>

		<h3>Manufacturer</h3>
		<% control Search.currentFacets(Manufacturer_ms) %>
		<p><a href="$QuotedSearchLink">$Name</a> ($Count)</p>
		<% end_control %>
	</div>

	<div class="span9">
		<h2>$Manufacturer : $Title</h2>
		<% if CurrentMember %><p class="priceInfo">$Price</p><% end_if %>
		<% if Image %>
		<div class="row">
		<span class="thumbnail" style="width:600px">
		$Image.SetRatioSize(600,400)
		</span>
			<p></p>
		</div>
		<div class="row">
			<div class="span3">
				<h3>Features</h3>
				$Features
			</div>
			<div class="span3">
				<h3>Technical Info</h3>
				$TechInfo
				
			</div>
		</div>
		<div class="row">
			<div class="span3">
				<h3>Finish</h3>
				$Finish
			</div>
			<div class="span3">
				<h3>Warranty</h3>
				$Warranty
			</div>
		</div>
		<% end_if %>
	</div>
</div>