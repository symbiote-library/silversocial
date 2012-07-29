<?php

class FacebookIdentifier extends DataExtension {
	public static $db = array(
		'FacebookID' => 'Varchar',
		'FacebookName' => 'Varchar(255)',
	);
	
	public function updateMemberFormFields(FieldSet $fields) {
		$fields->removeByName('FacebookID');
		$fields->removeByName('FacebookName');
		
		if(Member::currentUser() && Member::currentUser()->exists()) {
			$fields->push($f = new ReadonlyField('FacebookButton', 'Facebook'));
			$f->dontEscape = true;
		} else {
			$fields->push(new HiddenField('FacebookButton', false));
		}
	}
	
	public function getFacebookButton() {
		if($this->owner->exists()) {
			Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
			Requirements::javascript(THIRDPARTY_DIR . '/jquery-livequery/jquery.livequery.js');
			Requirements::javascript('facebook/javascript/facebook.js');
			if($this->owner->FacebookID) {
				$token = SecurityToken::inst();
				$removeURL = Controller::join_links('FacebookCallback', 'RemoveFacebook');
				$removeURL = $token->addToUrl($removeURL);
				return 'Connected to Facebook user ' . $this->owner->FacebookName . '. <a href="' . $removeURL . '" id="RemoveFacebookButton">Disconnect</a>';
			} else {
				return '<img src="facebook/Images/connect.png" id="ConnectFacebookButton" alt="Connect to Facebook" />';
			}
		}
	}
}
