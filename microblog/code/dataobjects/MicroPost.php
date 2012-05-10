<?php

/**
 *
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class MicroPost extends DataObject {
	public static $db = array(
		'Title'			=> 'Varchar(255)',
		'Author'		=> 'Varchar(255)',
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
		return Convert::raw2xml($this->Title);
	}
	
	public function Link() {
		$page = DataObject::get_one('MicroBlogPage');
		return $page->Link('user') . '/' . $this->OwnerID;
	}
}
