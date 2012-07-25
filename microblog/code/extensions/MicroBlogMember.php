<?php

/**
 *
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class MicroBlogMember extends DataExtension {
	
	public static $db = array(
		'VotesToGive'		=> 'Int',
	);
	
	public static $has_one = array(
		'UploadFolder'		=> 'Folder',
		'Profile'			=> 'PublicProfile',
	);
	
	public static $many_many = array(
		'Following'			=> 'Member',
	);
	
	public static $belongs_many_many = array(
		'Followers'			=> 'Member',
	);
	
	public static $dependencies = array(
		'microBlogService'		=> '%$MicroBlogService',
	);
	
	/**
	 * @var MicroBlogService
	 */
	public $microBlogService;
	
	public function onBeforeWrite() {
		parent::onBeforeWrite();
		if ($this->owner->OwnerID != $this->owner->ID) {
			$this->owner->OwnerID = $this->owner->ID;
		}
		
		if (!$this->owner->ID) {
			$this->owner->InheritPerms = true;
		}
		
		$changed = $this->owner->isChanged('FirstName') || $this->owner->isChanged('Surname') || $this->owner->isChanged('Email');
		
		if ($this->owner->ID && !$this->owner->ProfileID) {
			$profile = PublicProfile::create();
			$this->syncProfile($profile);
			$this->owner->ProfileID = $profile->ID;
		} else if ($this->owner->ProfileID && $changed) {
			$this->syncProfile($this->owner->Profile());
		}
		
		$this->memberFolder();
	}
	
	protected function syncProfile($profile) {
		$profile->FirstName = $this->owner->FirstName;
		$profile->Surname = $this->owner->Surname;
		$profile->Email = $this->owner->Email;
		$profile->MemberID = $this->owner->ID;
		$profile->Votes = $this->owner->VotesToGive;
		$profile->write();
	}
	
	public function permissionSources() {
		return $this->owner->Groups();
	}
	
	public function follow($otherMember) {
		$this->owner->Following()->add($otherMember);
	}
	
	public function unfollow($otherMember) {
		$this->owner->Following()->remove($otherMember);
	}
	
	public function canView() {
		return true;
	}
	
	public function canVote() {
		return $this->VotesToGive > 0;
	}
	
	public function memberFolder() {
		if (!$this->owner->UploadFolderID || !$this->owner->UploadFolder()->exists()) {
			// get the folder for this user
			$name = md5($this->owner->Email);
			$path = 'user-files/' . $name;
			$this->owner->UploadFolderID = Folder::find_or_make($path)->ID;
		}
		
		return $this->owner->UploadFolder();
	}
	
	public function toFilteredMap() {
		$allowed = array(
			'FirstName',
			'Surname',
			'Email',
		);

		$map = array();
		foreach ($allowed as $prop) {
			$map[$prop] = $this->owner->$prop;
		}
		
		return $map;
	}
	
	public function Friends() {
		return $this->microBlogService->friendsList($this->owner);
	}
	
	public function Link() {
		$microblog = DataObject::get_one('SiteDashboardPage', '"ParentID" = 0');
		return $microblog->Link('board/main/' . $this->owner->ID);
	}
}
