<?php

/**
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class TimelineDashlet extends Dashlet {
	
}

class TimelineDashlet_Controller extends Dashlet_Controller {
	public $microBlogService;
	public $securityContext;

	static $dependencies = array(
		'microBlogService'		=> '%$MicroBlogService',
		'securityContext'		=> '%$SecurityContext',
	);
	
	public function __construct($widget = null, $parent = null) {
		parent::__construct($widget, $parent);
	}
	
	public function PostForm () {
		Requirements::combine_files('minimal_uploadfield.js', array(
			THIRDPARTY_DIR . '/jquery-fileupload/jquery.iframe-transport.js',
			THIRDPARTY_DIR . '/jquery-fileupload/jquery.fileupload.js',
			THIRDPARTY_DIR . '/jquery-fileupload/jquery.fileupload-ui.js',
		));

		$fields = new FieldList(
			$taf = new TextareaField('Title', _t('MicroBlog.POST', 'Post'))
		);
		$taf->setRows(3);
		$taf->setColumns(120);
		
		$actions = new FieldList(
			new FormAction('savepost', _t('MicroBlog.SAVE', 'Add'))
		);
		
		$form = new Form($this, 'PostForm', $fields, $actions);
		return $form;
	}
	
	public function savepost($data, Form $form) {
		if (!$this->securityContext->getMember()) {
			return Security::permissionFailure($this);
		}
		if (isset($data['Title']) && strlen($data['Title'])) {
			$this->microBlogService->createPost($this->securityContext->getMember(), $data['Title']);
		}
		$this->redirectBack();
	}
	
	public function uploadFile($request) {
		if ($request) {
			$one = '';
		}
	}
	
	public function OwnerFeed() {
		$owner = $this->getWidget()->Owner();
		if (!$owner || !$owner->exists()) {
			throw new Exception("Invalid user feed for " . $this->getWidget()->OwnerID);
		}
		
		$data = $this->microBlogService->getStatusUpdates($owner);

		return $data;
	}
}
