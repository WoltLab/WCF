<?php
namespace wcf\system\user\activity\event;
use wcf\data\comment\Comment;
use wcf\data\comment\response\CommentResponse;
use wcf\data\user\activity\event\ViewableUserActivityEvent;
use wcf\data\user\UserProfile;
use wcf\system\cache\runtime\CommentResponseRuntimeCache;
use wcf\system\cache\runtime\CommentRuntimeCache;
use wcf\system\cache\runtime\UserProfileRuntimeCache;

/**
 * Provides a method to read the comment response, comment, and user objects related to comment
 * response user activity events.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\User\Activity\Event
 * @since	5.3
 */
trait TCommentResponseUserActivityEvent {
	/**
	 * user objects for the comment authors
	 * @var	UserProfile[]
	 */
	protected $commentAuthors = [];
	
	/**
	 * ids of the objects the comments belongs to
	 * @var	int[] 
	 */
	protected $commentObjectIDs = [];
	
	/**
	 * comment objects the responses belongs to
	 * @var	Comment[]
	 */
	protected $comments = [];
	
	/**
	 * comment response the comment response user activity events belong to
	 * @var	CommentResponse[]
	 */
	protected $responses = [];
	
	/**
	 * Reads the data of the comment responses the given events belong to.
	 * 
	 * @param	ViewableUserActivityEvent[]	$events
	 */
	protected function readResponseData(array $events) {
		$responseIDs = [];
		foreach ($events as $event) {
			$responseIDs[] = $event->objectID;
		}
		
		$this->responses = CommentResponseRuntimeCache::getInstance()->getObjects($responseIDs);
		
		$commentIDs = [];
		foreach ($this->responses as $response) {
			$commentIDs[] = $response->commentID;
		}
		
		if (!empty($commentIDs)) {
			$this->comments = CommentRuntimeCache::getInstance()->getObjects($commentIDs);
		}
		
		$userIDs = [];
		foreach ($this->comments as $comment) {
			$userIDs[] = $comment->userID;
			$this->commentObjectIDs[] = $comment->objectID;
		}
		if (!empty($userIDs)) {
			$this->commentAuthors = UserProfileRuntimeCache::getInstance()->getObjects($userIDs);
		}
	}
}
