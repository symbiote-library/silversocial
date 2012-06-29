<?php

/**
 * @author marcus@silverstripe.com.au
 * @license BSD License http://silverstripe.org/bsd-license/
 */
class MicroBlogService {
	
	/**
	 * @var DataService 
	 */
	public $dataService;
	
	public static $dependencies = array(
		'dataService'		=> '%$DataService',
	);
	
	public function __construct() {
		
	}
	
	public function webEnabledMethods() {
		return array(
			'getStatusUpdates'	=> 'GET',
			'getTimeline'		=> 'GET',
		);
	}
	
	/**
	 * Get all feeds
	 *
	 * @param type $number 
	 */
	public function globalFeed($number = 20) {
		$number = (int) $number;
		return $this->dataService->getAllMicroPost(null, '"Created" DESC', '', '0,' . $number);
	}
	
	/**
	 * Creates a new post for the given member
	 *
	 * @param type $member
	 * @param type $content
	 * @return MicroPost 
	 */
	public function createPost(DataObject $member, $content) {
		$post = new MicroPost();
		$post->Content = $content;
		$post->OwnerID = $member->ID;
		$post->write();
		return $post;
	}
	
	/**
	 * Gets all the status updates for a particular user before a given time
	 *
	 * @param type $member
	 * @param type $beforeTime
	 * @param type $number 
	 */
	public function getStatusUpdates(DataObject $member, $sinceTime = null, $beforePost = null, $number = 10) {
		if ($member) {
			$number = (int) $number;
			$userIds[] = $member->ID;
			$sinceTime = Convert::raw2sql($sinceTime ? $sinceTime : '1980-09-22 00:00:00');
			$filter = array(
				'OwnerID'				=> $userIds, 
				'Created:GreaterThan'	=> $sinceTime,
				'ParentID'				=> 0
			);
			
			if ($beforePost) {
				$filter['ID:LessThan']	= $beforePost;
			}
			
			$posts = $this->dataService->getAllMicroPost($filter, '"Created" DESC', '', '0, ' . $number);
			return $posts;
		}
	}
	
	/**
	 * Gets all the updates for a given user's list of followers for a given time
	 * period
	 *
	 * @param type $member
	 * @param type $beforeTime
	 * @param type $number 
	 */
	public function getTimeline(DataObject $member, $sinceTime = null, $beforePost = null, $number = 10) {
		$following = $member->Following();
		$number = (int) $number;
		$userIds = array();
		if ($following) {
			$userIds = $following->map('ID', 'ID');
			$userIds = $userIds->toArray();
		}

		$userIds[] = $member->ID;
		$sinceTime = Convert::raw2sql($sinceTime ? $sinceTime : '1980-09-22 00:00:00');
		$filter = '"OwnerID" IN (' . implode(',', $userIds) . ') AND "Created" > \'' . $sinceTime .'\'';

		$filter = array(
			'OwnerID'				=> $userIds, 
			'Created:GreaterThan'	=> $sinceTime,
			'ParentID'				=> 0
		);
		
		if ($beforePost) {
			$filter['ID:LessThan']	= $beforePost;
		}
		
		$posts = $this->dataService->getAllMicroPost($filter, '"Created" DESC', '', '0, ' . $number);
		return $posts;
	}
	
	/**
	 * Search for a member or two
	 * 
	 * @param string $searchTerm 
	 */
	public function findMember($searchTerm) {
		$term = Convert::raw2sql($searchTerm);
		$filter = '"FirstName" LIKE \'%' . $term .'%\' OR "Surname" LIKE \'%' . $term . '%\'';
		
		$items = $this->dataService->getAllMember($filter);
		
		return $items;

//				$filter = array(
//			'FirstName:PartialMatch'		=> $searchTerm,
//			'Surname:PartialMatch'		=> $searchTerm,
//			'Email:PartialMatch'		=> $searchTerm,
//		);
//		
//		$list = DataList::create('Member')->dataQuery()->
	}

	public function addFollower(DataObject $member, DataObject $follower) {
		$follower->follow($member);
		return $follower;
	}
	
	public function removeFollower($member, $follower) {
		$follower->unfollow($member);
	}
}
