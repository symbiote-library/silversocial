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
	
	/**
	 * @var PermissionService
	 */
	public $permissionService;
	
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
			'removeFriendship'	=> 'POST',
			'rawPost'			=> 'GET',
			'savePost'			=> 'POST',
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
				$post->ThreadID = $parent->ThreadID;
			}
		}

		$post->write();
		
		// set its thread ID
		if (!$post->ParentID) {
			$post->ThreadID = $post->ID;
			$post->write();
		}
		
		if ($post->ID != $post->ThreadID) {
			$thread = $this->dataService->microPostById($post->ThreadID);
			if ($thread) {
				$owner = $thread->Owner();
				$this->transactionManager->run(function () use ($post, $thread) {
					$thread->NumReplies += 1;
					$thread->write();
				}, $owner);
			}
		}

		$this->extractTags($post);

		$this->rewardMember($member, 3);
		
		// we stick this in here so the UI can update...
		$post->RemainingVotes = $member->VotesToGive;
		
		$source = $post->permissionSource();

		return $post;
	}
	
	/**
	 * Gets the raw post if allowed
	 * 
	 * @param int $id 
	 */
	public function rawPost($id) {
		$item = $this->dataService->byId('MicroPost', $id);
		if ($item->checkPerm('Write')) {
			return $item;
		}
	}
	
	/**
	 * Save the post
	 * 
	 * @param DataObject $post
	 * @param type $data 
	 */
	public function savePost(DataObject $post, $data) {
		if ($post->checkPerm('Write') && isset($data['Content'])) {
			$post->Content = $data['Content'];
			$post->write();
			return $post;
		}
	}

	/**
	 * Extracts tags from an object's content where the tag is preceded by a #
	 * 
	 * @param MicroPost $object 
	 * 
	 */
	public function extractTags(DataObject $object, $field = 'Content') {
		if (!$object->hasExtension('TaggableExtension')) {
			return array();
		}
		$content = $object->$field;

		if (preg_match_all('/#([a-z0-9_-]+)/is', $content, $matches)) {
			$object->Tags()->removeAll();
			foreach ($matches[1] as $tag) {
				$existing = DataList::create('Tag')->filter(array('Title' => $tag))->first();
				if (!$existing) {
					$existing = Tag::create();
					$existing->Title = $tag;
					$existing->write();
				}
				$object->Tags()->add($existing, array('Tagged' => date('Y-m-d H:i:s')));
			}
		}

		return $object->Tags();
	}

	/**
	 * Reward a member with a number of votes to be given
	 * @param type $member
	 * @param type $votes 
	 */
	public function rewardMember($member, $votes) {
		$member->VotesToGive += $votes;
		$this->transactionManager->run(function () use ($member) {
			$member->write();
		}, $member);
	}
	
	/**
	 * Gets all the status updates for a particular user before a given time
	 * 
	 * @param type $member
	 * @param type $beforeTime
	 * @param type $number 
	 */
	public function getStatusUpdates(DataObject $member, $sortBy = 'ID', $since = 0, $offset = 0, $topLevelOnly = true, $tags = array(), $number = 10) {
		if ($member && $member->ID) {
			$number = (int) $number;
			$userIds[] = $member->ProfileID;
			$filter = array(
				'ThreadOwnerID'		=> $userIds, 
			);
			return $this->microPostList($filter, $sortBy, $since, $offset, $topLevelOnly, $tags, $number);
		} else {
			// otherwise, we're implying that we ONLY want 'public' updates
			$filter = array(); // array('PublicAccess'	=> 1);
			return $this->microPostList($filter, $sortBy, $since, $offset, $topLevelOnly, $tags, $number);
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
	public function getTimeline(DataObject $member, $sortBy = 'ID',  $since = 0, $offset = 0, $topLevelOnly = true, $tags = array(), $number = 10) {
		$following = $this->friendsList($member);

		// TODO Following points to a list of Profile IDs, NOT user ids.
		$number = (int) $number;
		$userIds = array();
		if ($following) {
			$userIds = $following->map('OtherID', 'OtherID');
			$userIds = $userIds->toArray();
		}

		$userIds[] = $member->ProfileID;
		
		$filter = array(
			'OwnerProfileID' => $userIds, 
		);
		
		return $this->microPostList($filter, $sortBy, $since, $offset, $topLevelOnly, $tags, $number);
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
	public function getRepliesTo(DataObject $to, $sortBy = 'ID', $since = 0, $offset = 0, $topLevelOnly = false, $tags = array(), $number = 100) {
		$filter = array(
			'ParentID'			=> $to->ID, 
		);
		
		return $this->microPostList($filter, $sortBy, $since, $offset, $topLevelOnly, $tags, $number);
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
	protected function microPostList($filter, $sortBy = 'ID', $since = 0, $offset = 0, $topLevelOnly = true, $tags = array(), $number = 10) {
		if ($topLevelOnly) {
			$filter['ParentID'] = '0';
		}

		if ($since) {
			$since = Convert::raw2sql($since); 
			$filter['ID:GreaterThan'] = $since;
		}

		$canSort = array('WilsonRating');
		$sort = array();
		
		if (is_string($sortBy)) {
			if (in_array($sortBy, $canSort)) {
				$sort[$sortBy] = 'DESC';
			}

			// final sort if none other specified
			$sort['ID'] = 'DESC';
		} else {
			$sort = $sortBy;
		}

		$limit = $number ? ((int) $offset) . ', ' . $number : '';

		$join = null;

		if (count($tags)) {
			array_walk($tags, function (&$item) {
				$item = Convert::raw2sql($item);
			});
			
			$join = array(
				array(
					'table'		=> 'MicroPost_Tags',
					'clause'	=> 'MicroPost_Tags.MicroPostID = MicroPost.ID'
				),
				array(
					'table'		=> 'Tag',
					'clause'	=> 'MicroPost_Tags.TagID = Tag.ID',
					'where'		=> '"Tag"."Title" IN (\''.  implode('\',\'', $tags) .'\')'
				)
			);
		}

		$posts = $this->dataService->getAllMicroPost($filter, $sort, $join, $limit);
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
		$current = (int) $this->securityContext->getMember()->ID;
		$filter = '("FirstName" LIKE \'%' . $term .'%\' OR "Surname" LIKE \'%' . $term . '%\') AND "ID" <> ' . $current;

		$items = $this->dataService->getAllPublicProfile($filter);
		
		return $items;
	}

	/**
	 * Create a friendship relationship object
	 * 
	 * @param DataObject $member
	 *				"me", as in the person who triggered the follow
	 * @param DataObject $followed
	 *				"them", the person "me" is wanting to add 
	 * @return \Friendship
	 * @throws PermissionDeniedException 
	 */
	public function addFriendship(DataObject $member, DataObject $followed) {
		if (!$member || !$followed) {
			throw new PermissionDeniedException('Read', 'Cannot read those users');
		}

		if ($member->MemberID != $this->securityContext->getMember()->ID) {
			throw new PermissionDeniedException('Write', 'Cannot create a friendship for that user');
		}

		$existing = DataList::create('Friendship')->filter(array(
			'InitiatorID'		=> $member->ID,
			'OtherID'			=> $followed->ID,
		))->first();

		if ($existing) {
			return $existing;
		}
		
		// otherwise, we have a new one!
		$friendship = new Friendship;
		$friendship->InitiatorID = $member->ID;
		$friendship->OtherID = $followed->ID;
		
		// we add the initiator into the 
		
		// lets see if we have the reciprocal; if so, we can mark these as verified 
		$reciprocal = $friendship->reciprocal();

		// so we definitely add the 'member' to the 'followers' group of $followed
		$followers = $followed->member()->getGroupFor(MicroBlogMember::FOLLOWERS);
		$member->member()->Groups()->add($followers);

		if ($reciprocal) {
			$reciprocal->Status = 'Approved';
			$reciprocal->write();
			
			$friendship->Status = 'Approved';
			
			// add to each other's friends groups
			$friends = $followed->member()->getGroupFor(MicroBlogMember::FRIENDS);
			$member->member()->Groups()->add($friends);
			
			$friends = $member->member()->getGroupFor(MicroBlogMember::FRIENDS);
			$followed->member()->Groups()->add($friends);
		}

		$friendship->write();
		return $friendship;
	}
	
	/**
	 * Remove a friendship object
	 * @param DataObject $relationship 
	 */
	public function removeFriendship(DataObject $relationship) {
		if ($relationship && $relationship->canDelete()) {
			
			// need to remove this user from the 'other's followers group and friends group
			// if needbe
			if ($relationship->Status == 'Approved') {
				$reciprocal = $relationship->reciprocal();
				if ($reciprocal) {
					// set it back to pending
					$reciprocal->Status = 'Pending';
					$reciprocal->write();
				}
				
				$friends = $relationship->Other()->member()->getGroupFor(MicroBlogMember::FRIENDS);
				$relationship->Initiator()->member()->Groups()->remove($friends);
				
				$friends = $relationship->Initiator()->member()->getGroupFor(MicroBlogMember::FRIENDS);
				$relationship->Other()->member()->Groups()->remove($friends);
			}
			
			$followers = $relationship->Other()->member()->getGroupFor(MicroBlogMember::FOLLOWERS);
			$relationship->Initiator()->member()->Groups()->remove($followers);
			
			$relationship->delete();
			return $relationship;
		}
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
		$list = DataList::create('Friendship')->filter(array('InitiatorID' => $member->Profile()->ID));
		return $list;
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
		$member = $this->securityContext->getMember();
		
		if ($member->VotesToGive <= 0) {
			$post->RemainingVotes = 0;
			return $post;
		}

		// we allow multiple votes - as many as the user has to give!
		$currentVote = null; // MicroPostVote::get()->filter(array('UserID' => $member->ID, 'PostID' => $post->ID))->first();
		
		if (!$currentVote) {
			$currentVote = MicroPostVote::create();
			$currentVote->UserID = $member->ID;
			$currentVote->PostID = $post->ID;
		}
		
		$currentVote->Direction = $dir > 0 ? 1 : -1;
		$currentVote->write();
		
		$list = DataList::create('MicroPostVote');
		
		$upList = $list->filter(array('PostID' => $post->ID, 'Direction' => 1));
		$post->Up = $upList->count();
		
		$downList = $list->filter(array('PostID' => $post->ID, 'Direction' => -1));
		$post->Down = $downList->count();
		
		$owner = $post->Owner();
		if (!$post->OwnerID || !$owner || !$owner->exists()) {
			$owner = Security::findAnAdministrator();
		}
		
		// write the post as the owner, and calculate some changes for the author
		$this->transactionManager->run(function () use ($post, $currentVote, $member) {
			$author = $post->Owner();
			if ($author && $author->exists() && $author->ID != $member->ID) {
				if ($currentVote->Direction > 0) {
					$author->Up += 1;
				} else {
					$author->Down += 1;
				}
				$author->write();
			}
			$post->write();
		}, $owner);

		$this->rewardMember($member, -1);
		$post->RemainingVotes = $member->VotesToGive;

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