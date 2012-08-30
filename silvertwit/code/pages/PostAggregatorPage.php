<?php

/**
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class PostAggregatorPage extends Page {
	public static $db = array();
}

class PostAggregatorPage_Controller extends Page_Controller {

	static $dependencies = array(
		'microBlogService'		=> '%$MicroBlogService',
		'securityContext'		=> '%$SecurityContext',
	);
	
	/** 
	 * @var MicroBlogService
	 */
	public $microBlogService;
	
	protected $tags = '';
	
	public function init() {
		parent::init();
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-form/jquery.form.js');
		Requirements::javascript('microblog/javascript/timeline.js');
	}

	public function Timeline() {
		$replies = (bool) $this->request->getVar('replies');
		$since = $this->request->getVar('since');
		$offset = (int) $this->request->getVar('offset');
		
		$tags = $this->request->getVar('tags') ? $this->request->getVar('tags') : $this->tags;

		if (strlen($tags)) {
			$tags = explode(',', $tags);
		} else {
			$tags = array();
		}
		
		$timeline = $this->microBlogService->getStatusUpdates(null, 'WilsonRating', $since, $offset, !$replies, $tags);
		return trim($this->customise(array('Posts' => $timeline, 'SortBy' => 'rating'))->renderWith('Timeline'));
	}
	
	public function tag() {
		$this->tags = $this->getRequest()->param('ID');
		return array();
	}

	public function show() {
		$id = (int) $this->request->param('ID');
		if ($id) {
			$since = $id - 1;
			$posts = $this->microBlogService->getStatusUpdates(Member::create(), array('ID' => 'ASC'), $since, 0, false, array(), 1);

			return array(
				'Timeline'		=> trim($this->customise(array('Posts' => $posts))->renderWith('Timeline')),
				'ShowReplies'	=> 1,
			);
		}
	}
}
