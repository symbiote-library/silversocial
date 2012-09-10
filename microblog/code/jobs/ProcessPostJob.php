<?php

/**
 * Performs post processing of a post to do things like oembed lookup etc
 *
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class ProcessPostJob extends AbstractQueuedJob {
	
	public static $api_key = '';
	
	public static $dependencies = array(
		'socialGraphService'	=> '%$SocialGraphService',
	);
	
	/**
	 * @var SocialGraphService
	 */
	public $socialGraphService;
	
	
	public function __construct($post = null) {
		if ($post) {
			$this->setObject($post);
			$this->totalSteps = 1;
		}
	}
	
	public function getTitle() {
		return 'Processing #' . $this->getObject()->ID;
	}
	
	public function process() {
		
		$post = $this->getObject();
		
		if (self::$api_key) {
			require_once Director::baseFolder() . '/microblog/thirdparty/defensio/Defensio.php';
			$defensio = new Defensio(self::$api_key);
			$document = array(
				'type' => 'comment', 
				'content' => $post->Content, 
				'platform' => 'silverstripe_microblog', 
				'client' => 'MicroBlog Defensio-PHP | 0.1 | Marcus Nyeholt | marcus@silverstripe.com.au', 
				'async' => 'false'
			);

			try {
				$result = $defensio->postDocument($document);

				if ($result && isset($result[1])) {
					if ($result[1]->allow == 'false') {
						$post->Content = '[spam]';
						$post->Down += 100;
						$post->write();
					}
				}
			} catch (Exception $e) {
				SS_Log::log($e, SS_Log::WARN);
			}
		}

		$url = filter_var($post->Content, FILTER_VALIDATE_URL);
		if (strlen($url) && $this->socialGraphService->isWebpage($url)) {
			$this->socialGraphService->findPostContent($post, $url);
		}

		$this->isComplete = true;
	}
}
