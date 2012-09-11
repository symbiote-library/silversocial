<?php

/**
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class SocialGraphService {
	
	public $oembedOptions = array(
		'maxwidth'		=> '600',
		'maxheight'		=> '400',
	);
	
	/**
	 * Check whether a given URL is actually an html page 
	 */
	public function isWebpage($url) {
		$c = curl_init(); 
		curl_setopt($c, CURLOPT_URL, $url); 
		curl_setopt($c, CURLOPT_HEADER, 1); // get the header 
		curl_setopt($c, CURLOPT_NOBODY, 1); // and *only* get the header 
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1); // get the response as a string from curl_exec(), rather than echoing it 
		curl_setopt($c, CURLOPT_FRESH_CONNECT, 1); // don't use a cached version of the url 
		$result = curl_exec($c);
		
		if (!$result) { 
			return false; 
		} 
		
		if (stripos($result, 'Content-type: text/html')) {
//			$svc = new RestfulService($url);
//			$response = $svc->request('', 'GET');
			return true;
		}
		
		return false;
	}
	
	/**
	 * Extract a title from a given piece of content
	 * 
	 * @param string $content 
	 *					The content to get a title for
	 * @param boolean $retrieveTitle
	 *					Whether to pull the locations title tag down
	 */
	public function extractTitle($content, $retrieveTitle = false) {
		if ($retrieveTitle) {
			
		} else {
			
		}
	}
	
	/**
	 * Analyse a post and see if there's particular content that should be extracted
	 * 
	 * @param string $post
	 * @param string $url
	 * @return type 
	 */
	public function convertPostContent($post, $url) {
		
		// let's check for stuff
		$oembed = Oembed::get_oembed_from_url($post->Content, false, $this->oembedOptions);
		if ($oembed) {
			$post->OriginalLink = $post->Content;
			$post->IsOembed = true;
			$post->Content = $oembed->forTemplate();
		} else {
			$graph = OpenGraph::fetch($url);

			if ($graph) {
				foreach ($graph as $key => $value) {
					$data[$key] = Varchar::create_field('Varchar', $value);
				}
				if (isset($data['url'])) {
					$post->OriginalLink = $post->Content;
					$post->IsOembed = true;
					$post->Content = $post->customise($data)->renderWith('OpenGraphPost');
				}
			}
		}

		return $post;
	}
}
