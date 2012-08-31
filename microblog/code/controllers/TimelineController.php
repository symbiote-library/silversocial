<?php

/**
 * Controller that handles timeline interaction
 *
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class TimelineController extends Controller {
	/**
	 * @var MicroBlogService
	 * 
	 */
	public $microBlogService;
	public $securityContext;
	
	protected $parentController = null;
	protected $showReplies = true;
	
	/**
	 * Context user for this request cycle
	 * @var type 
	 */
	protected $contextUser = null;

	static $dependencies = array(
		'microBlogService'		=> '%$MicroBlogService',
		'securityContext'		=> '%$SecurityContext',
	);

	public function __construct($parent = null, $replies = true, $context = null) {
		parent::__construct();
		
		$this->parentController = $parent;
		$this->showReplies = $replies;
		$this->contextUser = $context;
	}

	public function init() {
		parent::init();
		
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-form/jquery.form.js');
		Requirements::javascript('microblog/javascript/timeline.js');
	}
	
	public function index() {
		return $this->renderWith('FullTimeline');
	}

	public function PostForm () {
		$fields = new FieldList(
			$taf = new TextareaField('Content', _t('MicroBlog.POST', 'Post'))
		);
		$taf->setRows(3);
		$taf->setColumns(120);
		$taf->addExtraClass('expandable');
		
		$taf->addExtraClass('postContent');
		
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
		$post = null;

		if (isset($data['Content']) && strlen($data['Content'])) {
			$parentId = isset($data['ParentID']) ? $data['ParentID'] : 0;
			$post = $this->microBlogService->createPost($this->securityContext->getMember(), $data['Content'], $parentId);
		}
		if (Director::is_ajax() && $post && $post->ID) {
			$result = array(
				'response'		=> $post->toMap(),
			);
			$this->response->addHeader('Content-type', 'application/json');
			return Convert::raw2json($result);
		}
		if (Director::is_ajax()) {
			return '{"message": "invalid"}';
		}
		
		$this->redirectBack();
	}
	
	public function uploadFiles($data, Form $form) {
		if (!$this->securityContext->getMember()) {
			throw new PermissionDeniedException('Write');
		}
		if (isset($data['Attachment'])) {
			$post = MicroPost::create();
			$form->saveInto($post);
			if ($post->AttachmentID) {
				if (isset($data['ParentID'])) {
					$post->ParentID = $data['ParentID'];
				}
				$post->write();
				// @todo clean this up for NON js browsers
				
				return Convert::raw2json($post->toMap());
			}
		}
	}
	
	public function ShowReplies() {
		return $this->showReplies;
	}
	
	public function Timeline() {
		$replies = (bool) $this->request->getVar('replies');
		
		$since = $this->request->getVar('since');
		$offset = (int) $this->request->getVar('offset');
		
		// @TODO Fix this logic as we've switched to using offsets properly nowww
		if ($post = $this->request->getVar('post')) {
			$since = ((int) $post) - 1;
			$before = $since + 2;
		}
		
		$timeline = $this->microBlogService->getTimeline($this->securityContext->getMember(), null, $since, $offset, !$replies);
		return trim($this->customise(array('Posts' => $timeline))->renderWith('Timeline'));
	}

	public function OwnerFeed() {
		$since = $this->request->getVar('since');
		$offset = (int) $this->request->getVar('offset');
		
		// @TODO Fix this logic properly!
		if ($post = $this->request->getVar('post')) {
			$since = ((int) $post) - 1;
			$before = $since + 2;
		}

		$owner = $this->contextUser;
		if (!$owner || !$owner->exists()) {
			throw new Exception("Invalid user feed for " . $owner->OwnerID);
		}
		$replies = (bool) $this->request->getVar('replies');
		
		$data = $this->microBlogService->getStatusUpdates($owner, null, $since, $offset, !$replies);
		return trim($this->customise(array('Posts' => $data))->renderWith('Timeline'));
	}
	
	public function ContextUser() {
		return $this->contextUser;
	}
	
	public function Link($action = '') {
		$link = $this->parentController->Link('timeline');
		return Controller::join_links($link, $action);
	}
}
