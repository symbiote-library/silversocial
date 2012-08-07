<?php

/**
 * Describes a relationship between users 
 *
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class Friendship extends DataObject {
	
	public static $db = array(
		'Status'			=> "Enum('Approved,Pending','Pending')",
	);
	
	public static $has_one = array(
		'Initiator'			=> 'PublicProfile',
		'Other'				=> 'PublicProfile',
	);
	
	public static $defaults = array(
		'Status'			=> 'Pending',
	);
	
	/**
	 * get the 'other' view of this friendship 
	 */
	public function reciprocal() {
		return DataList::create('Friendship')->filter(array(
			'InitiatorID'		=> $this->OtherID,
			'OtherID'			=> $this->InitiatorID
		))->first();
	}
	
	public function canView($member = null) {
		return true;
	}
	
	public function canEdit($member = null) {
		if (!$member) {
			$member = Member::currentUser();
		}
		return $member->ProfileID == $this->InitiatorID;
	}
	
	public function canDelete($member = null) {
		return $this->canEdit($member);
	}
}
