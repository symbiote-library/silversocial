<?php

class Page extends SiteTree {
	
	public static $db = array(
	);
	
	public static $has_one = array(
	);
	
	public function requireDefaultRecords() {
		if (Director::isDev()) {
			$loader = new FixtureLoader();
			$loader->loadFixtures();
		}
	}
}

class Page_Controller extends ContentController {
	/**
	 * An array of actions that can be accessed via a request. Each array element should be an action name, and the
	 * permissions or conditions required to allow the user to access it.
	 *
	 * <code>
	 * array (
	 *     'action', // anyone can access this action
	 *     'action' => true, // same as above
	 *     'action' => 'ADMIN', // you must have ADMIN permissions to access this action
	 *     'action' => '->checkAction' // you can only access this action if $this->checkAction() returns true
	 * );
	 * </code>
	 *
	 * @var array
	 */
	public static $allowed_actions = array (
	);
	
	public function init() {
		
		parent::init();
		Requirements::block(THIRDPARTY_DIR . '/prototype/prototype.js');
		Requirements::combine_files('silvertwit.js', array(
			THIRDPARTY_DIR . '/jquery/jquery.js',
			THIRDPARTY_DIR . '/jquery-livequery/jquery.livequery.js',
			'microblog/javascript/date.js',
			'microblog/javascript/jquery.tmpl.min.js',
			'microblog/javascript/microblog.js'
		));
	}
	
	public function SecurityID() {
		return SecurityToken::inst()->getValue();
	}
	
	public function MemberDetails() {
		$m = Member::currentUser();
		if ($m) {
			return Convert::raw2json(array(
				'Title'			=> $m->getTitle(),
				'FirstName'		=> $m->FirstName,
				'Surname'		=> $m->Surname,
				'ID'			=> $m->ID
			));
		}
		
	}
}
