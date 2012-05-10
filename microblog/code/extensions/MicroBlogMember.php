<?php

/**
 *
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class MicroBlogMember extends DataExtension {
	
	public static $many_many = array(
				'Following'			=> 'Member',
			);
	
	public static $belongs_many_many = array(
				'Followers'			=> 'Member',
			);
	
	public function follow($otherMember) {
		$this->owner->Following()->add($otherMember);
	}
	
	public function unfollow($otherMember) {
		$this->owner->Following()->remove($otherMember);
	}
	
	public function canView() {
		return true;
	}
}
