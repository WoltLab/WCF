<?php
namespace wcf\data\moderation\queue;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\user\User;
use wcf\data\user\UserProfile;
use wcf\data\DatabaseObjectDecorator;
use wcf\data\ILinkableObject;
use wcf\data\ITitledObject;
use wcf\data\IUserContent;
use wcf\system\bbcode\SimpleMessageParser;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\moderation\queue\ModerationQueueManager;
use wcf\system\visitTracker\VisitTracker;

/**
 * Represents a viewable moderation queue entry.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Moderation\Queue
 * 
 * @method	ModerationQueue		getDecoratedObject()
 * @mixin	ModerationQueue
 */
class ViewableModerationQueue extends DatabaseObjectDecorator implements ILinkableObject, ITitledObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = ModerationQueue::class;
	
	/**
	 * affected object
	 * @var	IUserContent
	 */
	protected $affectedObject = null;
	
	/**
	 * true, if associated object was deleted
	 * @var	boolean
	 */
	protected $isOrphaned = false;
	
	/**
	 * user profile object
	 * @var	UserProfile
	 */
	protected $userProfile = null;
	
	/**
	 * Sets link for viewing/editing.
	 * 
	 * @param	IUserContent		$object
	 */
	public function setAffectedObject(IUserContent $object) {
		$this->affectedObject = $object;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink() {
		return ModerationQueueManager::getInstance()->getLink($this->objectTypeID, $this->queueID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitle() {
		return ($this->affectedObject === null ? '' : $this->affectedObject->getTitle());
	}
	
	/**
	 * Returns affected object.
	 * 
	 * @return	IUserContent
	 */
	public function getAffectedObject() {
		return $this->affectedObject;
	}
	
	/**
	 * Sets associated user profile object.
	 * 
	 * @param	UserProfile	$userProfile
	 * @deprecated	3.0
	 */
	public function setUserProfile(UserProfile $userProfile) {
		if ($this->affectedObject !== null && ($userProfile->userID == $this->affectedObject->getUserID())) {
			$this->userProfile = $userProfile;
		}
	}
	
	/**
	 * Returns associated user profile object.
	 * 
	 * @return	UserProfile
	 */
	public function getUserProfile() {
		if ($this->affectedObject !== null && $this->userProfile === null) {
			if ($this->affectedObject->getUserID()) {
				$this->userProfile = UserProfileRuntimeCache::getInstance()->getObject($this->affectedObject->getUserID());
			}
			else {
				$this->userProfile = new UserProfile(new User(null, []));
			}
		}
		
		return $this->userProfile;
	}
	
	/**
	 * Returns true if associated object was removed.
	 * 
	 * @return	boolean
	 */
	public function isOrphaned() {
		return $this->isOrphaned;
	}
	
	/**
	 * Marks associated objects as removed.
	 */
	public function setIsOrphaned() {
		$this->isOrphaned = true;
	}
	
	/**
	 * @inheritDoc
	 */
	public function __toString() {
		return $this->getTitle();
	}
	
	/**
	 * Returns a viewable moderation queue entry.
	 * 
	 * @param	integer		$queueID
	 * @return	ViewableModerationQueue
	 */
	public static function getViewableModerationQueue($queueID) {
		$queueList = new ViewableModerationQueueList();
		$queueList->getConditionBuilder()->add("moderation_queue.queueID = ?", [$queueID]);
		$queueList->sqlLimit = 1;
		$queueList->readObjects();
		$queues = $queueList->getObjects();
		
		return (isset($queues[$queueID]) ? $queues[$queueID] : null);
	}
	
	/**
	 * Returns formatted message text.
	 * 
	 * @return	string
	 */
	public function getFormattedMessage() {
		return SimpleMessageParser::getInstance()->parse($this->message, true, false);
	}
	
	/**
	 * Returns the object type name.
	 * 
	 * @return	string
	 */
	public function getObjectTypeName() {
		$objectType = ObjectTypeCache::getInstance()->getObjectType($this->objectTypeID);
		return $objectType->objectType;
	}
	
	/**
	 * Returns true if this queue item is new for the active user.
	 * 
	 * @return	boolean
	 */
	public function isNew() {
		if ($this->time > max(VisitTracker::getInstance()->getVisitTime('com.woltlab.wcf.moderation.queue'), VisitTracker::getInstance()->getObjectVisitTime('com.woltlab.wcf.moderation.queue', $this->queueID))) return true;
		
		return false;
	}
}
