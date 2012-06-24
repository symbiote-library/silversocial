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
		'Parent'		=> 'MicroPost',
		'Attachment'	=> 'File',
	);
	
	public static $defaults = array(
		'PublicAccess'		=> true
	);
	
	public static $extensions = array(
		'Restrictable'
	);
	
	public function onBeforeWrite() {
		parent::onBeforeWrite();
		$this->Author = Member::currentUser()->getTitle();
	}

	public function formattedPost() {
		return Convert::raw2xml($this->Content);
	}
	
	public function Link() {
		$page = DataObject::get_one('MicroBlogPage');
		return $page->Link('user') . '/' . $this->OwnerID;
	}
}
