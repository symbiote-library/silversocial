<div class="row">
    <div class="span3">
        <% include Sidebar %>
    </div>

    <div class="span12">
	
		<div id="PostAggregatorIntro">
			$Content
		</div>
		
	<input type="hidden" name="timelineUpdateUrl" value="$Link(Timeline)" />
	
	<div id="StatusFeed">
		$Timeline
		<div class="feed-actions">
			<a href="#" class="moreposts">Load more...</a>
		</div>
	</div>
	
    </div>
</div>
