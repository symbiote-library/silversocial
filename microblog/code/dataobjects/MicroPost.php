<?php

/**
 *
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class MicroPost extends DataObject {
	public static $db = array(
		'Title'			=> 'Varchar(255)',
		'Content'		=> 'Text',
		'Author'		=> 'Varchar(255)',
	);

	public static $has_one = array(
		'ThreadOwner'	=> 'Member',
		'Parent'		=> 'MicroPost',
		'Attachment'	=> 'File',
	);
	
	public static $has_many = array(
		'Replies'		=> 'MicroPost',
	);
	
	public static $defaults = array(
		'PublicAccess'		=> true
	);
	
	public static $extensions = array(
		'Restrictable'
	);
	
	public static $summary_fields = array(
		'Title', 
		'Content',
		'Created'
	);
	
	public static $searchable_fields = array(
		'Title',
		'Content'
	);

	public function onBeforeWrite() {
		parent::onBeforeWrite();
		if (!$this->ThreadOwnerID) {
			if ($this->ParentID) {
				$this->ThreadOwnerID = $this->Parent()->ThreadOwnerID;
			} else {
				$this->ThreadOwnerID = Member::currentUserID();
			}
		}
		
		$this->Author = Member::currentUser()->getTitle();
	}
	
	/**
	 * handles SiteTree::canAddChildren, useful for other types too
	 */
	public function canAddChildren() {
		if ($this->checkPerm('View')) {
			return true;
		} else {
			return false;
		}
	}

	public function formattedPost() {
		return Convert::raw2xml($this->Content);
	}
	
	public function Link() {
		$page = DataObject::get_one('MicroBlogPage');
		return $page->Link('user') . '/' . $this->OwnerID;
	}
	
	public function Posts() {
		return $this->Replies();
	}
}
