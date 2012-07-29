<?php

class GoogleIdentifier extends DataExtension {
	public static $db = array(
		'GoogleIdentity' => 'Varchar(255)',
		'GoogleName' => 'Varchar(255)',
	);
	
	public function updateMemberFormFields(FieldList $fields) {
		$fields->removeByName('GoogleIdentity');
		
		if(Member::currentUser() && Member::currentUser()->exists()) {
			$fields->push($f = new ReadonlyField('GoogleButton', 'Google'));
			$f->dontEscape = true;
		} else {
			$fields->push(new HiddenField('GoogleButton', false));
		}
	}
	
	public function getGoogleButton() {
		if($this->owner->exists()) {
			Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
			Requirements::javascript(THIRDPARTY_DIR . '/jquery-livequery/jquery.livequery.js');
			Requirements::javascript('google/javascript/google.js');
			if($this->owner->GoogleIdentity) {
				$token = SecurityToken::inst();
				$removeURL = Controller::join_links('GoogleCallback', 'RemoveGoogle');
				$removeURL = $token->addToUrl($removeURL);
				return 'Connected to Google account ' . $this->owner->GoogleName . '. <a href="' . $removeURL . '" id="RemoveGoogleButton">Disconnect</a>';
			} else {
				return '<img src="google/Images/connect.png" id="ConnectGoogleButton" alt="Connect to Google" />';
			}
		}
	}
}
