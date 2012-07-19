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
	
	
	public function init() {
		parent::init();
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-form/jquery.form.js');
		Requirements::javascript('microblog/javascript/timeline.js');
	}
	
	public function Timeline() {
		$replies = (bool) $this->request->getVar('replies');
		$since = $this->request->getVar('since');
		$before = $this->request->getVar('before');
		$timeline = $this->microBlogService->getStatusUpdates(null, 'WilsonRating', $since, $before, !$replies);
		return trim($this->customise(array('Posts' => $timeline))->renderWith('Timeline'));
	}
}
