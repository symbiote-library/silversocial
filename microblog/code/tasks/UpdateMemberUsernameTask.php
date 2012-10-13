<?php

/**
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class UpdateMemberUsernameTask extends BuildTask {
	
	public static $dependencies = array('transactionManager' => '%$TransactionManager');
	/**
	 * @var TransactionManager
	 */
	public $transactionManager;
	
	public function run($request) {
		if (!$this->transactionManager) {
			Injector::inst()->inject($this);
		}
		
		$this->transactionManager->runAsAdmin(function () {
			$members = DataList::create('Member');
			foreach ($members as $member) {
				if (!$member->Username) {
					list($username) = explode('@', $member->Email);
					$member->Username = $username;
					$member->write();
					echo "Updated $username <br/>\n";
				 } else {
					 echo "$member->Username already set <br/>\n";
				 }
				
			}
		});
	}
}
