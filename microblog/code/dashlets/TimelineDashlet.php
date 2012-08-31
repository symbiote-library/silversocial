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
		$controller = $this->timeline();
		$rendered = $controller->handleRequest($this->request, $this->model);
		return $rendered instanceof SS_HTTPResponse ? $rendered->getBody() : '';
	}

}
