<?php
namespace wcf\data\comment\response;
use wcf\data\comment\Comment;
use wcf\data\user\UserProfile;
use wcf\system\comment\manager\ICommentManager;
use wcf\system\like\LikeHandler;

/**
 * Provides a structured comment response list.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.comment.response
 * @category	Community Framework
 */
class StructuredCommentResponseList extends CommentResponseList {
	/**
	 * comment object
	 * @var	\wcf\data\comment\Comment;
	 */
	public $comment = null;
	
	/**
	 * comment manager
	 * @var	\wcf\system\comment\manager\ICommentManager
	 */
	public $commentManager = null;
	
	/**
	 * minimum response time
	 * @var	integer
	 */
	public $minResponseTime = 0;
	
	/**
	 * @see	\wcf\data\DatabaseObjectList::$sqlLimit
	 */
	public $sqlLimit = 50;
	
	/**
	 * Creates a new structured comment response list.
	 * 
	 * @param	\wcf\system\comment\manager\ICommentManager	$commentManager
	 * @param	\wcf\data\comment\Comment			$comment
	 */
	public function __construct(ICommentManager $commentManager, Comment $comment) {
		parent::__construct();
		
		$this->comment = $comment;
		$this->commentManager = $commentManager;
		
		$this->getConditionBuilder()->add("comment_response.commentID = ?", array($this->comment->commentID));
		$this->sqlLimit = $this->commentManager->getCommentsPerPage();
	}
	
	/**
	 * @see	\wcf\data\DatabaseObjectList::readObjects()
	 */
	public function readObjects() {
		parent::readObjects();
		
		// get user ids
		$userIDs = array();
		foreach ($this->objects as &$response) {
			if (!$this->minResponseTime || $response->time < $this->minResponseTime) $this->minResponseTime = $response->time;
			$userIDs[] = $response->userID;
			
			$response = new StructuredCommentResponse($response);
			$response->setIsDeletable($this->commentManager->canDeleteResponse($response->getDecoratedObject()));
			$response->setIsEditable($this->commentManager->canEditResponse($response->getDecoratedObject()));
		}
		unset($response);
		
		// fetch user data and avatars
		if (!empty($userIDs)) {
			$userIDs = array_unique($userIDs);
			
			$users = UserProfile::getUserProfiles($userIDs);
			foreach ($this->objects as $response) {
				if (isset($users[$response->userID])) {
					$response->setUserProfile($users[$response->userID]);
				}
			}
		}
	}
	
	/**
	 * Fetches the like data.
	 * 
	 * @return	array
	 */
	public function getLikeData() {
		if (empty($this->objectIDs)) return array();
		
		$objectType = LikeHandler::getInstance()->getObjectType('com.woltlab.wcf.comment.response');
		LikeHandler::getInstance()->loadLikeObjects($objectType, $this->objectIDs);
		$likeData = array('response' => LikeHandler::getInstance()->getLikeObjects($objectType));
		
		return $likeData;
	}
	
	/**
	 * Returns mimimum response time.
	 * 
	 * @return	integer
	 */
	public function getMinResponseTime() {
		return $this->minResponseTime;
	}
}
