<?php

/**
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class TestMicroBlogService extends SapphireTest {
	
	public function setUp() {
		Restrictable::set_enabled(false);
	}
	
	public function testTagExtract() {
		
		
		$this->logInWithPermission();
		$post = MicroPost::create();
		$post->Content = <<<POST
	This is #content
		
being created in this #post
		
POST;
		$post->write();
		
		$tags = singleton('MicroBlogService')->extractTags($post);
	}
}
