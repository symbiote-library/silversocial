<div class="row">
    <div class="span3">
        <% include Sidebar %>
    </div>

    <div class="span9">
    <h1>$Title</h1>
	
	<input type="hidden" name="timelineUpdateUrl" value="$Link(Timeline)" />
	
	<div id="StatusFeed">
		$Timeline
		<div class="feed-actions">
			<a href="#" class="moreposts">Load more...</a>
		</div>
	</div>
	
    </div>
</div>
