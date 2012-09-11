<?php

/**
 *
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class MicroBlogMember extends DataExtension {
	const FRIENDS = 'Friends';
	const FOLLOWERS = 'Followers';
	
	public static $db = array(
		'PostPermission'		=> 'Varchar',
		'VotesToGive'				=> 'Int',
		'Balance'					=> 'Int',
		'Up'						=> 'Int',
		'Down'						=> 'Int',
	);

	public static $has_one = array(
		'UploadFolder'		=> 'Folder',
		'Profile'			=> 'PublicProfile',

		// where all our friends get added 
		'FriendsGroup'			=> 'Group',
		'FollowersGroup'		=> 'Group',
		
		'MyPermSource'			=> 'PermissionParent',
	);
	
	public static $defaults = array(
		'PostPermission'		=> 'Public'
	);
	
	public static $dependencies = array(
		'microBlogService'		=> '%$MicroBlogService',
		'permissionService'		=> '%$PermissionService',
		'transactionManager'	=> '%$TransactionManager',
	);
	
	static $permission_options = array(
		'Hidden',
		'Friends only',
		'Friends and followers',
		'Public'
	);
	
	static $summary_fields = array(
		'Up',
		'Down',
		'Balance',
	);
	
	/**
	 * @var MicroBlogService
	 */
	public $microBlogService;
	
	/**
	 * @var PermissionService
	 */
	public $permissionService;
	
	/**
	 * @var TransactionManager 
	 */
	public $transactionManager;
	
	public function onBeforeWrite() {

		if (!$this->owner->ID && !$this->owner->Email) {
			throw new Exception("Cannot create user without Email");
			throw new Exception(print_r(debug_backtrace(), true));
		}

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
		
		$this->getGroupFor(self::FRIENDS);
		$this->getGroupFor(self::FOLLOWERS);
		
		$this->owner->Balance = $this->owner->Up - $this->owner->Down;
		
		$this->memberFolder();
	}
	
	public function onAfterWrite() {
		parent::onAfterWrite();
	}

	protected function syncProfile($profile) {
		$profile->FirstName = $this->owner->FirstName;
		$profile->Surname = $this->owner->Surname;
		$profile->Email = $this->owner->Email;
		$profile->MemberID = $this->owner->ID;
		$profile->Votes = $this->owner->VotesToGive;
		$profile->write();
	}
	
	public function publicProfile() {
		if ($this->owner->ID && !$this->owner->ProfileID) {
			$profile = PublicProfile::create();
			$this->syncProfile($profile);
			$this->owner->ProfileID = $profile->ID;
		}
		return $this->owner->Profile();
	}
	
	/**
	 * @TODO Validate that this is still needed? I think it should be able to be turfed by now...
	 * @return type 
	 */
	public function permissionSources() {
		// return $this->owner->Groups();
	}

	public function canView() {
		return true;
	}
	
	public function canVote() {
		return $this->VotesToGive > 0;
	}
	
	/**
	 * Retrieve the container permission source for all this user's posts 
	 */
	public function postPermissionSource() {
		if ($this->owner->MyPermSourceID) {
			return $this->owner->MyPermSource();
		}

		$source = new PermissionParent();
		$source->PublicAccess = true;
		$source->Title = 'Posts for ' . $this->owner->getTitle();
		
		$owner = $this->owner;
		
		$this->transactionManager->run(function () use($source, $owner) {
			$source->write();
			$owner->MyPermSourceID = $source->ID;
			$owner->write();
		}, $owner);
		
		return $source;
	}
	
	public function clearCurrentPermissions() {
		$source = $this->postPermissionSource();
		
		$this->permissionService->removePermissions($source, 'View', $this->getGroupFor(self::FOLLOWERS));
		$this->permissionService->removePermissions($source, 'View', $this->getGroupFor(self::FRIENDS));

		$source->InheritPerms = false;
		$source->PublicAccess = false;
	}
	
	/**
	 * set permissions for this user's posts 
	 */
	public function updatePostPermissions() {
		$set = $this->owner->PostPermission;
		$source = $this->postPermissionSource();
		
		switch ($set) {
			case 'Hidden': {
				$this->permissionService->removePermissions($source, 'View', $this->getGroupFor(self::FOLLOWERS));
				$this->permissionService->removePermissions($source, 'View', $this->getGroupFor(self::FRIENDS));

				$source->InheritPerms = false;
				$source->PublicAccess = false;
				break;
			}
			case 'Friends only': {
				$source->InheritPerms = false;
				$source->PublicAccess = false;
				
				$this->permissionService->removePermissions($source, 'View', $this->getGroupFor(self::FOLLOWERS));
				$this->permissionService->grant($source, 'View', $this->getGroupFor(self::FRIENDS));
				break;
			}
			
			case 'Friends and followers': {
				$source->InheritPerms = false;
				$source->PublicAccess = false;

				$this->permissionService->grant($source, 'View', $this->getGroupFor(self::FOLLOWERS));
				$this->permissionService->grant($source, 'View', $this->getGroupFor(self::FRIENDS));
				break;
			}
			
			case 'Public': {
				$source->PublicAccess = true;
				break;
			}
		}
		
		$source->write();
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

	/**
	 * gets the group that this user's friends belong to 
	 */
	public function getGroupFor($type) {
		$groupType = $type.'Group';
		$groupTypeID = $type.'GroupID';
		
		if ($this->owner->$groupTypeID) {
			return $this->owner->$groupType();
		}

		$title = $this->owner->Email . ' ' . $type;
		$group = DataList::create('Group')->filter(array('Title' => $title))->first();
		if ($group && $group->exists()) {
			$this->owner->$groupTypeID = $group->ID;
			return $group;
		} else {
			$group = $this->transactionManager->runAsAdmin(function () use ($title) {
				$group = Group::create();
				$group->Title = $title;
				$group->write();
				return $group;
			});
			if ($group) {
				$this->owner->$groupTypeID = $group->ID;
			}
			return $group;
		}
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
