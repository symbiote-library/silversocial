<div class="row">
	
	<div class="span3">
	
	$Form	
	
	<h3>Category</h3>
	<% control currentFacets(Categories_ms) %>
	<p><a href="$QuotedSearchLink">$Name</a> ($Count)</p>
	<% end_control %>

	<h3>Price</h3>
	<% control currentFacets(Price_mt) %>
	<p><a href="$SearchLink">$Name</a> ($Count)</p>
	<% end_control %>
	
	
	<h3>Manufacturer</h3>
	<% control currentFacets(Manufacturer_ms) %>
	<p><a href="$QuotedSearchLink">$Name</a> ($Count)</p>
	<% end_control %>
	
	</div>

	<div class="span9">
		$Content
		
	<% if FacetCrumbs %>
	<ul class="facetCrumbs">
		<% control FacetCrumbs %>
		<li><i class="icon-minus-sign"></i><a href="$RemoveLink">$Name</a></li>
		<% end_control %>
	</ul>
	<% end_if %>

	<% if Results %>
		<% if ListingTemplateID %>
		$TemplatedResults
		<% else %>
	    <ul id="SearchResults">
	      <% control Results %>
	        <li>
				<% if Image %>
				<a class="thumbnail" href="$Link">$Image.SetRatioSize(100,100)</a>
				<% end_if %>

				<div class="resultDescription">
					<div>
						<% if MenuTitle %>
							<a class="searchResultHeader" href="$Link">$MenuTitle</a>
						<% else %>
							<a class="searchResultHeader" href="$Link">$Title</a>
						<% end_if %>
					</div>
					<div>
							<% if Description %>
								$Description.FirstParagraph(html)
							<% end_if %>
					</div>
				</div>
	        </li>
	      <% end_control %>
	    </ul>
		<% end_if %>
	  <% else %>
	    <p>Sorry, your search query did not return any results.</p>
	  <% end_if %>

	  <% if Results.MoreThanOnePage %>
	    <div id="PageNumbers">
	      <% if Results.NotLastPage %>
	        <a class="next" href="$Results.NextLink" title="View the next page">Next</a>
	      <% end_if %>
	      <% if Results.NotFirstPage %>
	        <a class="prev" href="$Results.PrevLink" title="View the previous page">Prev</a>
	      <% end_if %>
	      <span>
	        <% control Results.PaginationSummary(5) %>
	          <% if CurrentBool %>
	            $PageNum
	          <% else %>
	            <a href="$Link" title="View page number $PageNum">$PageNum</a>
	          <% end_if %>
	        <% end_control %>
	      </span>
	    </div>
	 <% end_if %>
	 </div>
</div>
