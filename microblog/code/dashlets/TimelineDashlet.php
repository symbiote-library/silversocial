<?php

/**
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class TimelineDashlet extends Dashlet {
	public static $title = 'Timeline';
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
	
	public function init() {
		parent::init();
		
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-form/jquery.form.js');
		Requirements::javascript('microblog/javascript/timeline.js');
	}
	
	public function PostForm () {
		$fields = new FieldList(
			$taf = new TextareaField('Content', _t('MicroBlog.POST', 'Post'))
		);
		$taf->setRows(3);
		$taf->setColumns(120);
		
		$actions = new FieldList(
			new FormAction('savepost', _t('MicroBlog.SAVE', 'Add'))
		);
		
		$form = new Form($this, 'PostForm', $fields, $actions);
		return $form;
	}
	
	public function UploadForm() {
		Requirements::combine_files('minimal_uploadfield.js', array(
			THIRDPARTY_DIR . '/jquery-fileupload/jquery.iframe-transport.js',
			THIRDPARTY_DIR . '/jquery-fileupload/jquery.fileupload.js',
		));

		$fields = new FieldList($field = new FileField('Attachment', _t('MicroBlog.FILE_UPLOAD', 'Upload files')));
		$actions = new FieldList(new FormAction('uploadFiles', _t('MicroBlog.UPLOAD_FILES', 'Upload')));
		
		$field->setFolderName($this->securityContext->getMember()->memberFolder()->Filename);
		
		$form = new Form($this, 'UploadForm', $fields, $actions);
		$form->addExtraClass('fileUploadForm');
		return $form;
				
	}
	
	public function savepost($data, Form $form) {
		if (!$this->securityContext->getMember()) {
			return Security::permissionFailure($this);
		}
		if (isset($data['Content']) && strlen($data['Content'])) {
			$this->microBlogService->createPost($this->securityContext->getMember(), $data['Content']);
		}
		if (Director::is_ajax()) {
			return '{"status": "ok"}';
		}
		$this->redirectBack();
	}
	
	public function uploadFiles($data, Form $form) {
		if (isset($data['Attachment'])) {
			$post = new MicroPost();
			$form->saveInto($post);
			
			if ($post->AttachmentID) {
				$post->write();
				
				// @todo clean this up for NON js browsers
				
				return Convert::raw2json($post->toMap());
			}
		}
	}
	
	public function Timeline() {
		$since = $this->request->getVar('since');
		$before = $this->request->getVar('before');
		return $this->microBlogService->getTimeline($this->securityContext->getMember(), $since, $before)->renderWith('Timeline');
	}
	
	public function OwnerFeed() {
		$since = $this->request->getVar('since');
		$before = $this->request->getVar('before');
		
		$owner = $this->getWidget()->Owner();
		if (!$owner || !$owner->exists()) {
			throw new Exception("Invalid user feed for " . $this->getWidget()->OwnerID);
		}
		$data = $this->microBlogService->getStatusUpdates($owner, $since, $before);
		return trim($data->renderWith('Timeline'));
	}
}
