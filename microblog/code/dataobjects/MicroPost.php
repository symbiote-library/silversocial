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
	);

	public static $has_many = array(
		'Replies'		=> 'MicroPost',
	);
	
	public static $defaults = array(
		'PublicAccess'		=> true,
		'InheritPerms'		=> false,
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
	
}
