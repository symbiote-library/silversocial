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
		Requirements::block(THIRDPARTY_DIR . '/prototype/prototype.js');
		
		Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-ui/jquery-ui.js');
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-entwine/dist/jquery.entwine-dist.js');
		Requirements::javascript(THIRDPARTY_DIR . '/javascript-templates/tmpl.js');
		Requirements::javascript(THIRDPARTY_DIR . '/javascript-loadimage/load-image.js');
		
		Requirements::javascript(FRAMEWORK_DIR . '/javascript/i18n.js');
		Requirements::javascript(FRAMEWORK_ADMIN_DIR . '/javascript/ssui.core.js');
		
		Requirements::javascript('webservices/javascript/webservices.js');
		
		Requirements::javascript('microblog/javascript/date.js');
		Requirements::javascript('microblog/javascript/microblog.js');
		
		
		parent::init();
	}
	
	public function SecurityID() {
		return SecurityToken::inst()->getValue();
	}
	
	public function MemberDetails() {
		$m = Member::currentUser();
		if ($m) {
			$m = $m->publicProfile();
			return Varchar::create_field('Varchar', Convert::raw2json(array(
				'Title'			=> $m->getTitle(),
				'FirstName'		=> $m->FirstName,
				'Surname'		=> $m->Surname,
				'ProfileID'		=> $m->ID,
				'MemberID'		=> $m->MemberID,
			)));
		}
	}

	public function RegisterForm() {
		if (!Member::currentUser()) {
			$profile = RestrictedMemberProfilePage::get()->first();
			if ($profile) {
				$ctl = RestrictedMemberProfilePage_Controller::create($profile);
				return $ctl->RegisterForm();
			}
		}
	}
}
