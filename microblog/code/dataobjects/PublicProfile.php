<?php

/**
 * Information about a user that's visible to everyone
 * 
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class PublicProfile extends DataObject {
	public static $db = array(
		'FirstName'		=> 'Varchar',
		'Surname'		=> 'Varchar',
		'Email'			=> 'Varchar(256)', 
		'Votes'			=> 'Int',
	);

	public static $has_one = array(
		'Member'		=> 'Member',
	);

	public function Link() {
		$microblog = DataObject::get_one('SiteDashboardPage', '"ParentID" = 0');
		return $microblog->Link('board/main/' . $this->MemberID);
	}

	public function canView($member=null) {
		return true;
	}
	
	public function member() {
		return Member::get()->filter(array('ID' => $this->MemberID))->first();
	}
}
