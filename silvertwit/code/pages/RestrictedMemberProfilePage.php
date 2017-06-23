<?php

/**
 * @author marcus@symbiote.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class RestrictedMemberProfilePage extends MemberProfilePage {
	
	public function providePermissions() {
		return array();
	}
}

class RestrictedMemberProfilePage_Controller extends MemberProfilePage_Controller {
	
	public static $dependencies = array(
		'transactionManager' => '%$TransactionManager',
	);
	
	/**
	 * @var TransactionManager
	 */
	public $transactionManager;
	
	/**
	 * Attempts to save either a registration or add member form submission
	 * into a new member object, returning NULL on validation failure.
	 *
	 * @return Member|null
	 */
	protected function addMember($form) {
		$member   = new Member();
		$groupIds = $this->getSettableGroupIdsFrom($form);

		$form->saveInto($member);

		$member->ProfilePageID   = $this->ID;
		$member->NeedsValidation = ($this->EmailType == 'Validation');
		$member->NeedsApproval   = $this->RequireApproval;

		try {
			$admin = Security::findAnAdministrator();
			$this->transactionManager->run(function () use ($member) {
				$member->write();
				$member->OwnerID = $member->ID;
				$member->write();
			}, $admin);
			
		} catch(ValidationException $e) {
			$form->sessionMessage($e->getResult()->message(), 'bad');
			return;
		}

		// set after member is created otherwise the member object does not exist
		$member->Groups()->setByIDList($groupIds);

		// If we require admin approval, send an email to the admin and delay
		// sending an email to the member.
		if ($this->RequireApproval) {
			$groups = $this->ApprovalGroups();
			$emails = array();

			if ($groups) foreach ($groups as $group) {
				foreach ($group->Members() as $_member) {
					if ($member->Email) $emails[] = $_member->Email;
				}
			}

			if ($emails) {
				$email   = new Email();
				$config  = SiteConfig::current_site_config();
				$approve = Controller::join_links(
					Director::baseURL(), 'member-approval', $member->ID, '?token=' . $member->ValidationKey
				);

				$email->setSubject("Registration Approval Requested for $config->Title");
				$email->setBcc(implode(',', array_unique($emails)));
				$email->setTemplate('MemberRequiresApprovalEmail');
				$email->populateTemplate(array(
					'SiteConfig'  => $config,
					'Member'      => $member,
					'ApproveLink' => Director::absoluteURL($approve)
				));

				$email->send();
			}
		} elseif($this->EmailType != 'None') {
			$email = new MemberConfirmationEmail($this, $member);
			$email->send();
		}

		$this->extend('onAddMember', $member);
		return $member;
	}
}