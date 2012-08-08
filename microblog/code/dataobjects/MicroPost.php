<?php

/**
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class MicroPost extends DataObject {
	public static $db = array(
		'Title'			=> 'Varchar(255)',
		'Content'		=> 'Text',
		'Author'		=> 'Varchar(255)',
		'OriginalLink'	=> 'Varchar',
		'IsOembed'		=> 'Boolean',
		'Deleted'		=> 'Boolean',
		'ThreadID'		=> 'Int',
		'NumReplies'	=> 'Int',
	);

	public static $has_one = array(
		'ThreadOwner'	=> 'PublicProfile',
		'OwnerProfile'	=> 'PublicProfile',
		'Parent'		=> 'MicroPost',
		'Attachment'	=> 'File',
		
		'PermSource'	=> 'PermissionParent',
	);

	public static $has_many = array(
		'Replies'		=> 'MicroPost',
	);
	
	public static $defaults = array(
		'PublicAccess'		=> false,
		'InheritPerms'		=> true,		// we'll have  default container set soon
	);
	
	public static $extensions = array(
		'Rateable',
		'Restrictable',
		'TaggableExtension',
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
	
	public static $dependencies = array(
		'socialGraphService'	=> '%$SocialGraphService',
		'microBlogService'		=> '%$MicroBlogService',
		'securityContext'		=> '%$SecurityContext',
	);

	/**
	 * Do we automatically detect oembed data and change comments? 
	 * 
	 * Override using injector configuration
	 * 
	 * @var boolean
	 */
	public $oembedDetect = true;
	
	/**
	 * @var SocialGraphService
	 */
	public $socialGraphService;
	
	/**
	 * @var MicroBlogService
	 */
	public $microBlogService;
	
	/**
	 * @var SecurityContext
	 */
	public $securityContext;

	public function onBeforeWrite() {
		parent::onBeforeWrite();
		$member = $this->securityContext->getMember();
		if (!$this->ThreadOwnerID) {
			if ($this->ParentID) {
				$this->ThreadOwnerID = $this->Parent()->ThreadOwnerID;
			} else {
				$this->ThreadOwnerID = $member->ProfileID;
			}
		}

		if ($this->oembedDetect) {
			$url = filter_var($this->Content, FILTER_VALIDATE_URL);
			if (strlen($url) && $this->socialGraphService->isWebpage($url)) {
				$this->socialGraphService->findPostContent($this, $url);
			}
		}

		if (!$this->OwnerProfileID) {
			$this->OwnerProfileID = $member->ProfileID;
			$this->Author = $this->securityContext->getMember()->getTitle();
		}
	}
	
	/**
	 * Handle the wilson rating specially 
	 * 
	 * @param type $field
	 * @return string 
	 */
	public function hasOwnTableDatabaseField($field) {
		if ($field == 'WilsonRating') {
			return "Double";
		}
		return parent::hasOwnTableDatabaseField($field);
	}

	public function IsImage() {
		$url = filter_var($this->Content, FILTER_VALIDATE_URL);
		$pattern = '!^https?://([a-z0-9\-\.\/\_]+\.(?:jpe?g|png|gif))$!Ui';
		return strlen($url) && preg_match($pattern, $url);
	}

	/**
	 * When 'deleting' an object, we actually just remove all its content 
	 */
	public function delete() {
		if ($this->checkPerm('Delete')) {
			$this->Tags()->removeAll();
			// if we have replies, we can't delete completely!
			if ($this->Replies()->exists()) {
				$this->Deleted = true;
				$this->Content = _t('MicroPost.DELETED', '[deleted]');
				$this->Author = $this->Content;
				$this->write();
			} else {
				return parent::delete();
			}
		}
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
		
		if ($page) {
			return $page->Link('user') . '/' . $this->OwnerID;
		}
		
		return $this->Owner()->Link();
	}

	public function Posts() {
		return $this->microBlogService->getRepliesTo($this);
	}
	
	/**
	 * We need to define a  permission source to ensure the 
	 * ParentID isn't used for permission inheritance 
	 */
	public function permissionSource() {
		if ($this->PermSourceID) {
			return $this->PermSource();
		}
		
		// otherwise, find a post by this user and use the shared parent
		$owner = $this->Owner();
		if ($owner) {
			$source = $owner->postPermissionSource();
			$this->PermSourceID = $source->ID;
			// TODO: Remove this; it's only used until all posts have an appropriate permission source...
			Restrictable::set_enabled(false);
			$this->write();
			Restrictable::set_enabled(true);
			return $source;
		}
	}
}
