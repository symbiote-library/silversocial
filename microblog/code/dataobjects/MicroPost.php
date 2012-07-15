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
		'OriginalLink'	=> 'Varchar',
		'IsOembed'		=> 'Boolean',
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
	
	/**
	 * Do we automatically detect oembed data and change comments? 
	 * 
	 * Override using injector configuration
	 * 
	 * @var boolean
	 */
	public $oembedDetect = true;
	

	public function onBeforeWrite() {
		parent::onBeforeWrite();
		if (!$this->ThreadOwnerID) {
			if ($this->ParentID) {
				$this->ThreadOwnerID = $this->Parent()->ThreadOwnerID;
			} else {
				$this->ThreadOwnerID = Member::currentUserID();
			}
		}

		if ($this->oembedDetect) {
			$url = filter_var($this->Content, FILTER_VALIDATE_URL);
			if (strlen($url)) {
				
				$graph = OpenGraph::fetch($url);
				if ($graph) {
					var_dump($graph);
					exit();
				} else {
					// let's check for stuff
					$oembed = Oembed::get_oembed_from_url($this->Content);
					if ($oembed) {
						$this->OriginalLink = $this->Content;
						$this->IsOembed = true;
						$this->Content = $oembed->forTemplate();
					}
				}
			
				
				

//				$data = array();
//				
//				if ($oembed->title) {
//					$data['Title'] = Varchar::create_field('Varchar', $oembed->title);
//				}
//				
//				$data['Thumbnail'] = $oembed->thumbnail_url ? Varchar::create_field('Varchar', $oembed->thumbnail_url) : null;
//				$data['Type'] = Varchar::create_field('Varchar', $oembed->type);
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
