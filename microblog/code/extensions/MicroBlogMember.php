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
	
	public function onBeforeWrite() {
		parent::onBeforeWrite();
		$this->uploadFolder();
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
}
