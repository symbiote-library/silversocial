<?php

/**
 *
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class MicroBlogPage extends Page {
//	public static $has_one = array(
//		'RelatedTwitterFeed'		=> 'ExternalContentSource'
//	);
//	
//	
//	public function getCMSFields() {
//		$fields = parent::getCMSFields();
//		$fields->addFieldToTab('Root.Content.Main', 
//			new ExternalTreeDropdownField('RelatedTwitterFeedID', _t('MicroBlog.RELATED_FEED', 'Related Twitter Feed'), 'ExternalContentSource'),
//			'Content'
//		);
//		
//		return $fields;
//	}
}

class MicroBlogPage_Controller extends Page_Controller {
	
	public function StatusForm () {
		$fields = new FieldList(
			$taf = new TextareaField('Title', _t('MicroBlog.POST', 'Post'))
		);
		$taf->setRows(3);
		$taf->setColumns(120);
		
		$actions = new FieldList(
			new FormAction('savepost', _t('MicroBlog.SAVE', 'Add'))
		);
		
		$form = new Form($this, 'StatusForm', $fields, $actions);
		return $form;
	}
	
	public function FollowForm() {
		$fields = new FieldList(
			new HiddenField('OtherID', 'Other', $this->ViewingUserID())
		);
		$actions = new FieldList(
			new FormAction('follow', _t('MicroBlog.FOLLOW', 'Follow'))
		);
		return new Form($this, 'FollowForm', $fields, $actions);
	}
	
	public function UnFollowForm() {
		$fields = new FieldSet(
			new HiddenField('OtherID', 'Other', $this->ViewingUserID())
		);
		$actions = new FieldSet(
			new FormAction('unfollow', _t('MicroBlog.UNFOLLOW', 'UnFollow'))
		);
		return new Form($this, 'UnFollowForm', $fields, $actions);
	}
	
	public function savepost($data, Form $form) {
		if (!Member::currentUserID()) {
			return Security::permissionFailure($this);
		}
		if (isset($data['Title']) && strlen($data['Title'])) {
			singleton('MicroBlogService')->createPost(Member::currentUser(), $data['Title']);
		}
		$this->redirect($this->data()->Link());
	}

	public function follow($data, $form) {
		if (!Member::currentUserID()) {
			return Security::permissionFailure($this);
		}
		$otherID = (int) (isset($data['OtherID']) ? $data['OtherID'] : null);
		if ($otherID) {
			$other = DataObject::get_by_id('Member', $otherID);
			singleton('MicroBlogService')->addFollower($other, Member::currentUser());
		}
		$this->redirectBack();
	}
	
	public function unfollow($data, $form) {
		if (!Member::currentUserID()) {
			return Security::permissionFailure($this);
		}
		$otherID = (int) (isset($data['OtherID']) ? $data['OtherID'] : null);
		if ($otherID) {
			$other = DataObject::get_by_id('Member', $otherID);
			singleton('MicroBlogService')->removeFollower($other, Member::currentUser());
		}
		$this->redirectBack();
	}
	
	/**
	 * Output RSS feed
	 */
	public function rss() {
		$entries = singleton('MicroBlogService')->globalFeed();
		$feed = new RSSFeed($entries, $this->Link('rss'), 'Global updates');
		$feed->outputToBrowser();
	}
	
	/**
	 * View a particular user's feed
	 */
	public function user() {
		return array();
	}
	
	public function UserFeed() {
		if (!Member::currentUserID()) {
			// return;
		}
		$id = $this->ViewingUserID();
		if ($id) {
			$user = DataObject::get_by_id('Member', $id);
			if ($user && $user->exists()) {
				$data = singleton('MicroBlogService')->getStatusUpdates($user);
				
			}
		} else if (Member::currentUserID()) {
			$data = singleton('MicroBlogService')->getTimeline(Member::currentUser());
		}
		return $data;
	}

	public function ViewingUserID() {
		$id = (int) $this->request->param('ID');
		return $id;
	}

	public function CanFollow() {
		if (!Member::currentUserID()) {
			return false;
		}

		$viewing = $this->ViewingUserID();
		if ($viewing && $viewing != Member::currentUserID()) {
			// check if in the list of followers
			$following = Member::currentUser()->Following();
			if ($following) {
				$exists = $following->find('ID', $viewing);
				if ($exists) {
					return false;
				}
			}
			return true;
		}
		return false;
	}
	
	public function CanUnFollow() {
		if (!Member::currentUserID()) {
			return false;
		}

		$viewing = $this->ViewingUserID();
		if ($viewing && $viewing != Member::currentUserID()) {
			// check if in the list of followers
			$following = Member::currentUser()->Following();
			if ($following) {
				$exists = $following->find('ID', $viewing);
				if ($exists) {
					return true;
				}
			}
		}
	}
}