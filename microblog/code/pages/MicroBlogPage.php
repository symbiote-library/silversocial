<?php

/**
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class MicroBlogPage extends Page {

}

class MicroBlogPage_Controller extends Page_Controller {
	public $microBlogService;
	public $securityContext;

	static $dependencies = array(
		'microBlogService'		=> '%$MicroBlogService',
		'securityContext'		=> '%$SecurityContext',
	);
	
	public function __construct($dataRecord = null) {
		parent::__construct($dataRecord);
	}

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
		if (!$this->securityContext->getMember()) {
			return Security::permissionFailure($this);
		}
		if (isset($data['Title']) && strlen($data['Title'])) {
			$this->microBlogService->createPost($this->securityContext->getMember(), $data['Title']);
		}
		$this->redirect($this->data()->Link());
	}

	/**
	 * TODO Update to match new api... 
	 */
	public function follow($data, $form) {
		if (!$this->securityContext->getMember()) {
			return Security::permissionFailure($this);
		}
		$otherID = (int) (isset($data['OtherID']) ? $data['OtherID'] : null);
		if ($otherID) {
			$other = DataObject::get_by_id('Member', $otherID);
			$this->microBlogService->addFollower($other, $this->securityContext->getMember());
		}
		$this->redirectBack();
	}
	
	/**
	 * TODO Update to match new api... 
	 */
	public function unfollow($data, $form) {
		if (!$this->securityContext->getMember()) {
			return Security::permissionFailure($this);
		}
		$otherID = (int) (isset($data['OtherID']) ? $data['OtherID'] : null);
		if ($otherID) {
			$other = DataObject::get_by_id('Member', $otherID);
			$this->microBlogService->removeFollower($other, $this->securityContext->getMember());
		}
		$this->redirectBack();
	}
	
	/**
	 * Output RSS feed
	 */
	public function rss() {
		$entries = $this->microBlogService->globalFeed();
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
		if (!$this->securityContext->getMember()) {
			// return;
		}
		$id = $this->ViewingUserID();
		if ($id) {
			$user = DataObject::get_by_id('Member', $id);
			if ($user && $user->exists()) {
				$data = $this->microBlogService->getStatusUpdates($user);
			}
		} else if ($this->securityContext->getMember()) {
			$data = $this->microBlogService->getTimeline($this->securityContext->getMember());
		}
		return $data;
	}

	public function ViewingUserID() {
		$id = (int) $this->request->param('ID');
		return $id;
	}
}