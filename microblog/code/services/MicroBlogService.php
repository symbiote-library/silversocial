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
	
	public $securityContext;
	
	public static $dependencies = array(
		'dataService'		=> '%$DataService',
		'permissionService'	=> '%$PermissionService',
		'securityContext'	=> '%$SecurityContext',
	);
	
	public function __construct() {
		
	}
	
	public function webEnabledMethods() {
		return array(
			'getStatusUpdates'	=> 'GET',
			'getTimeline'		=> 'GET',
			'addFriendship'		=> 'POST',
		);
	}
	
	/**
	 * Get all feeds
	 *
	 * @param type $number 
	 */
	public function globalFeed($number = 20) {
		$number = (int) $number;
		return $this->dataService->getAllMicroPost(null, '"ID" DESC', '', '0,' . $number);
	}
	
	/**
	 * Creates a new post for the given member
	 *
	 * @param type $member
	 * @param type $content
	 * @return MicroPost 
	 */
	public function createPost(DataObject $member, $content, $parentId = 0) {
		$post = new MicroPost();
		$post->Content = $content;
		$post->OwnerID = $member->ID;
		if ($parentId) {
			$parent = $this->dataService->microPostById($parentId);
			if ($parent) {
				$post->ParentID = $parentId;
			}
		}
		
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
			
			$posts = $this->dataService->getAllMicroPost($filter, '"ID" DESC', '', '0, ' . $number);
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
		$following = $this->friendsList($member);

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
		
		$posts = $this->dataService->getAllMicroPost($filter, '"ID" DESC', '', '0, ' . $number);
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

	public function addFriendship(DataObject $member, DataObject $follower) {
		
		if (!$member || !$follower) {
			throw new PermissionDeniedException('Read', 'Cannot read those users');
		}

		if (!$member->ID == $this->securityContext->getMember()->ID) {
			throw new PermissionDeniedException('Write', 'Cannot create a friendship for that user');
		}

		$existing = DataList::create('Friendship')->filter(array(
			'InitiatorID'		=> $member->ID,
			'OtherID'			=> $follower->ID,
		))->first();

		if ($existing) {
			return $existing;
		}
		
		// otherwise, we have a new one!
		$friendship = new Friendship;
		$friendship->InitiatorID = $member->ID;
		$friendship->OtherID = $follower->ID;
		
		$friendship->write();
		
		
		return $friendship;
	}
	
	public function friendsList(DataObject $member) {
		$list = DataList::create('Member')
				->innerJoin('Friendship', '"Friendship"."OtherID" = "Member"."ID"')
				->filter(array('InitiatorID' => $member->ID));
		return $list;
	}
	
	public function removeFollower($member, $follower) {
		$follower->unfollow($member);
	}
}

class MicroblogPermissions implements PermissionDefiner {
	public function definePermissions() {
		return array(
			'ViewPosts',
			'ViewProfile',
		);
	}
}