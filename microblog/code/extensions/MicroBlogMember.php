<?php

/**
 *
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class MicroBlogMember extends DataExtension {
	
	public static $has_one = array(
		'UploadFolder'		=> 'Folder'
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
		$this->memberFolder();
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
