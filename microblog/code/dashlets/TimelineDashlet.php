<?php

/**
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class TimelineDashlet extends Dashlet {
	public static $title = 'Timeline';
}

class TimelineDashlet_Controller extends Dashlet_Controller {
	/**
	 * @var MicroBlogService
	 * 
	 */
	public $microBlogService;
	public $securityContext;

	static $dependencies = array(
		'microBlogService'		=> '%$MicroBlogService',
		'securityContext'		=> '%$SecurityContext',
	);
	
	public function __construct($widget = null, $parent = null) {
		parent::__construct($widget, $parent);
		if ($parent && $parent->getRequest()) {
			$this->request = $parent->getRequest();
		}
	}
	
	public function timeline() {
		$controller = TimelineController::create($this, true, $this->getWidget()->Owner());
		return $controller;
	}

	public function ShowDashlet() {
		// oh man this is so hacky, but I don't really quite know the best way to do what I want which is
		// one controller and about ten different ways to access it... all depending on context of course!
		$controller = $this->timeline();
		$controller->init();
		$rendered = $controller->index(); // $controller->handleRequest($this->request, $this->model);
		return $rendered instanceof SS_HTTPResponse ? $rendered->getBody() : $rendered;
	}

}
