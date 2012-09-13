<?php

/**
 * Controller that handles timeline interaction
 *
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class TimelineController extends ContentController {
	
	const POOR_USER_THRESHOLD = -100;
	
	/**
	 * @var MicroBlogService
	 */
	public $microBlogService;
	
	/**
	 * @var SecurityContext
	 */
	public $securityContext;
	
	protected $parentController = null;
	protected $showReplies = true;
	
	/**
	 * Context user indicates who 'owns' the feed of posts being viewed
	 * 
	 * Only really relevant when deciding whether to show the 'add post' form in 
	 * Dashlet view mode, which means this code really should be refactored. 
	 * 
	 * @var Member
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
		
		Requirements::block(THIRDPARTY_DIR . '/prototype/prototype.js');
		
		Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-ui/jquery-ui.js');
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-entwine/dist/jquery.entwine-dist.js');
		Requirements::javascript(THIRDPARTY_DIR . '/javascript-templates/tmpl.js');
		Requirements::javascript(THIRDPARTY_DIR . '/javascript-loadimage/load-image.js');
		
		Requirements::javascript(FRAMEWORK_DIR . '/javascript/i18n.js');
		Requirements::javascript(FRAMEWORK_ADMIN_DIR . '/javascript/ssui.core.js');
		
		Requirements::javascript('webservices/javascript/webservices.js');
		
		Requirements::javascript('microblog/javascript/date.js');
		Requirements::javascript('microblog/javascript/microblog.js');
		
		Requirements::javascript(THIRDPARTY_DIR . '/jquery-form/jquery.form.js');
		Requirements::javascript('microblog/javascript/timeline.js');
		
		Requirements::javascript('microblog/javascript/local-storage.js');
		Requirements::javascript('microblog/javascript/microblog-statesave.js');

		Requirements::css('microblog/css/timeline.css');

		$member = $this->securityContext->getMember();
		if ($member && $member->ID) {
			if ($member->Balance < self::POOR_USER_THRESHOLD) {
				throw new Exception("Broken pipe");
			}
		}
	}

	public function index() {
		return $this->renderWith('FullTimeline');
	}

	/**
	 * Show a particular post
	 * 
	 * Note that this MAY be triggered directly from a request via 'viewpost' routing, so 
	 * don't rely on the $this->data() var to be filled
	 * 
	 * @return type 
	 */
	public function show() {
		// cast with int here forces the rest of the text to be stripped
		$id = (int) $this->request->param('ID');

		if ($id) {
			$since = $this->request->getVar('since');
			if (!$since) {
				$since = $id - 1;
			} else {
				
			}
			
			$posts = $this->microBlogService->getStatusUpdates(Member::create(), array('ID' => 'ASC'), $since, 0, false, array(), 1);
			$post = $posts->first();

			$this->showReplies = true;
			
			$timeline = trim($this->customise(array('Posts' => $posts))->renderWith('Timeline'));
			
			if (Director::is_ajax()) {
				return $timeline;
			}

			$data = array(
				'Timeline'		=> $timeline,
				'OwnerFeed'		=> $timeline,
				'Post'			=> $id,
			);

			$timeline = $this->customise($data)->renderWith('FullTimeline');
			
			return $this->customise(array('Title' => $post->Title, 'Content' => $timeline))->renderWith(array('TimelineController_show', 'Page'));
		}
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
		
		$timeline = $this->microBlogService->getTimeline($this->securityContext->getMember(), null, $since, $offset, !$replies);
		return trim($this->customise(array('Posts' => $timeline))->renderWith('Timeline'));
	}

	public function OwnerFeed() {
		$since = $this->request->getVar('since');
		$offset = (int) $this->request->getVar('offset');

		$owner = $this->contextUser;
		if (!$owner || !$owner->exists()) {
			throw new Exception("Invalid user feed for " . $owner->OwnerID);
		}
		$replies = (bool) $this->request->getVar('replies');
		
		$data = $this->microBlogService->getStatusUpdates($owner, null, $since, $offset, !$replies);
		return trim($this->customise(array('Posts' => $data))->renderWith('Timeline'));
	}

	/**
	 * Returns the object that indicates who 'owns' the feed being viewed
	 * @return Member 
	 */
	public function ContextUser() {
		return $this->contextUser;
	}
	
	public function Link($action = '') {
		if ($this->parentController) {
			$link = $this->parentController->Link('timeline');
		} else {
			$link = 'timeline';
		}
		
		return Controller::join_links($link, $action);
	}
}
