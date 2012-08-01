<?php

/**
 * Displays the list of tags in the system
 *
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class TagsDashlet extends Dashlet {
	public static $title = 'Tags';
}

class TagsDashlet_Controller extends Dashlet_Controller {
	
	public function Tags() {
		
		$query = new SQLQuery;
		
		$query->setFrom('Tag');
		$query->selectField('count(Tag.ID)', 'Number');
		$query->selectField('Tag.ID');
		$query->selectField('Title');
		
		$query->addInnerjoin('MicroPost_Tags', 'Tag.ID = MicroPost_Tags.TagID');
		
		$date = date('Y-m-d H:i:s', strtotime('-1 week'));
		$query->addWhere("MicroPost_Tags.Tagged > '$date'");
		
		$query->addGroupBy('Tag.ID');
		
		$query->setLimit(20);
		
		$rows = $query->execute();
		
		$tags = ArrayList::create();
		
		$home = PostAggregatorPage::get()->first();
		
		foreach ($rows as $row) {
			$data = new ArrayData($row);
			if ($home) {
				$data->Link = $home->Link('tag/' . $data->Title);
			}
			
			$tags->push($data);
		}
		return $tags;
	}
}