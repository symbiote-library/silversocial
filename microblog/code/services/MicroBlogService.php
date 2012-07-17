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
	
	/**
	 * @var SecurityContext
	 */
	public $securityContext;
	
	/**
	 * @var TransactionManager
	 */
	public $transactionManager;
	
	public static $dependencies = array(
		'dataService'			=> '%$DataService',
		'permissionService'		=> '%$PermissionService',
		'securityContext'		=> '%$SecurityContext',
		'transactionManager'	=> '%$TransactionManager',
	);
	
	public function __construct() {
		
	}
	
	public function webEnabledMethods() {
		return array(
			'deletePost'		=> 'POST',
			'vote'				=> 'POST',
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
		$post = MicroPost::create();
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
	public function getStatusUpdates(DataObject $member, $since= 0, $beforePost = null, $topLevelOnly = true, $number = 10) {
		if ($member) {
			$number = (int) $number;
			$userIds[] = $member->ID;
			$filter = array(
				'ThreadOwnerID'		=> $userIds, 
			);
			return $this->microPostList($filter, $since, $beforePost, $topLevelOnly, $number);
		} else {
			return $this->microPostList(array(), $since, $beforePost, $topLevelOnly, $number);
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
	public function getTimeline(DataObject $member, $since = 0, $beforePost = null, $topLevelOnly = true, $number = 10) {
		$following = $this->friendsList($member);

		$number = (int) $number;
		$userIds = array();
		if ($following) {
			$userIds = $following->map('ID', 'ID');
			$userIds = $userIds->toArray();
		}

		$userIds[] = $member->ID;
		
		$filter = array(
			'OwnerID'				=> $userIds, 
		);
		
		return $this->microPostList($filter, $since, $beforePost, $topLevelOnly, $number);
	}
	
	/**
	 * Get the list of replies to a particular post
	 * 
	 * @param DataObject $to
	 * @param type $since
	 * @param type $beforePost
	 * @param type $topLevelOnly
	 * @param type $number 
	 * 
	 * @return DataList
	 */
	public function getRepliesTo(DataObject $to, $since = 0, $beforePost = null, $topLevelOnly = false, $number = 10) {
		$filter = array(
			'ParentID'			=> $to->ID, 
		);
		
		return $this->microPostList($filter, $since, $beforePost, $topLevelOnly, $number);
	}
	
	/**
	 * Create a list of posts depending on a filter and time range
	 * 
	 * @param type $filter
	 * @param type $since
	 * @param type $beforePost
	 * @param type $topLevelOnly
	 * @param type $number
	 * 
	 * @return DataList 
	 */
	protected function microPostList($filter, $since= 0, $beforePost = null, $topLevelOnly = true, $number = 10) {
		if ($topLevelOnly) {
			$filter['ParentID'] = '0';
		}

		if ($since) {
			$since = Convert::raw2sql($since); 
			$filter['ID:GreaterThan'] = $since;
		}

		if ($beforePost) {
			$filter['ID:LessThan']	= $beforePost;
		}

		$posts = $this->dataService->getAllMicroPost($filter, '"WilsonRating" DESC', '', '0, ' . $number);
		return $posts;
	}
	
	
	/**
	 * Search for a member or two
	 * 
	 * @param string $searchTerm 
	 * @return DataList
	 */
	public function findMember($searchTerm) {
		$term = Convert::raw2sql($searchTerm);
		$filter = '"FirstName" LIKE \'%' . $term .'%\' OR "Surname" LIKE \'%' . $term . '%\'';
		
		$items = $this->dataService->getAllMember($filter);
		
		return $items;
	}

	/**
	 * Create a friendship relationship object
	 * 
	 * @param DataObject $member
	 * @param DataObject $follower
	 * @return \Friendship
	 * @throws PermissionDeniedException 
	 */
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
	
	/** 
	 * Get a list of friends for a particular member
	 * 
	 * @param DataObject $member
	 * @return DataList
	 */
	public function friendsList(DataObject $member) {
		if (!$member) {
			return;
		}
		$list = DataList::create('Member')
				->innerJoin('Friendship', '"Friendship"."OtherID" = "Member"."ID"')
				->filter(array('InitiatorID' => $member->ID));
		return $list;
	}
	
	/**
	 * Remove someone as a follower
	 * 
	 * @param DataObject $member
	 * @param DataObject $follower 
	 */
	public function removeFollower($member, $follower) {
		$follower->unfollow($member);
	}
	
	/**
	 * Delete a post
	 * 
	 * @param DataObject $post 
	 */
	public function deletePost(DataObject $post) {
		if ($post->checkPerm('Delete')) {
			$post->delete();
		}
		
		return $post;
	}
	
	/**
	 * Vote for a particular post
	 * 
	 * @param DataObject $post 
	 */
	public function vote(DataObject $post, $dir = 1) {
		$member = Member::currentUserID();
		$currentVote = MicroPostVote::get()->filter(array('UserID' => $member, 'PostID' => $post->ID))->first();
		
		if (!$currentVote) {
			$currentVote = MicroPostVote::create();
			$currentVote->UserID = $member;
			$currentVote->PostID = $post->ID;
		}
		
		$currentVote->Direction = $dir > 0 ? 1 : -1;
		$currentVote->write();
		
		$list = DataList::create('MicroPostVote');
		
		$upList = $list->filter(array('PostID' => $post->ID, 'Direction' => 1));
		$post->Up = $upList->count();
		
		$downList = $list->filter(array('PostID' => $post->ID, 'Direction' => -1));
		$post->Down = $downList->count();
		
		// write the post as the owner
		$this->transactionManager->run(function () use ($post) {
			$post->write();
		}, $post->Owner());
		
		return $post;
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