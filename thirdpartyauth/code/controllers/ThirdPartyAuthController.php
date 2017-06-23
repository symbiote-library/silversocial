<?php

/**
 * @author marcus@symbiote.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class ThirdPartyAuthController extends Controller {
	static $create_new_user = true;
	
	static $default_group = null;
	
	protected function newUser($firstName, $surname, $email) {
		$member = new Member();
		$member->FirstName = $firstName;
		$member->Surname = $surname;
		$member->Email = $email;
		$member->write();
		
		// let extensions handle what happens to new users
		$this->extend('onNewThirdpartyUser', $member);

		if (self::$default_group) {
			if ($member->hasExtension('Restrictable')) {
				$groupName = self::$default_group;
				singleton('TransactionManager')->runAsAdmin(function () use ($member, $groupName) {
					$member->addToGroupByCode(str_replace(' ', '-', strtolower($groupName)), $groupName);
				});
			} else {
				$member->addToGroupByCode(str_replace(' ', '-', strtolower(self::$default_group)), self::$default_group);
			}
		}
		return $member;
	}
}
