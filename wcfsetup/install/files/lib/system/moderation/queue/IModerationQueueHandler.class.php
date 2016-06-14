<?php
namespace wcf\system\moderation\queue;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ViewableModerationQueue;

/**
 * Default interface for moderation queue handlers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Moderation\Queue
 */
interface IModerationQueueHandler {
	/**
	 * Creates queue assignments for matching object ids.
	 * 
	 * @param	ModerationQueue[]	$queues
	 */
	public function assignQueues(array $queues);
	
	/**
	 * Returns the container id for current object id, may return 0.
	 * 
	 * @param	integer		$objectID
	 * @return	integer
	 */
	public function getContainerID($objectID);
	
	/**
	 * Validates object ids and returns orphaned queue ids.
	 * 
	 * @param	integer[]		$queues
	 * @return	integer[]
	 */
	public function identifyOrphans(array $queues);
	
	/**
	 * Returns true if given object id is valid.
	 * 
	 * @param	integer		$objectID
	 * @return	boolean
	 */
	public function isValid($objectID);
	
	/**
	 * Populates object properties for viewing.
	 * 
	 * @param	ViewableModerationQueue[]	$queues
	 */
	public function populate(array $queues);
	
	/**
	 * Removes affected content. It is up to the processing class to either
	 * soft-delete the content or remove it permanently.
	 * 
	 * @param	ModerationQueue		$queue
	 * @param	string			$message
	 */
	public function removeContent(ModerationQueue $queue, $message);
	
	/**
	 * Returns true if the affected content may be removed.
	 * 
	 * @param	ModerationQueue		$queue
	 * @return	boolean
	 */
	public function canRemoveContent(ModerationQueue $queue);
	
	/**
	 * Removes queses from database, should only be called if the referenced
	 * object is permanently deleted.
	 * 
	 * @param	integer[]		$objectIDs
	 */
	public function removeQueues(array $objectIDs);
	
	/**
	 * Returns true, if given user is affected by given queue entry.
	 * 
	 * @param	ModerationQueue		$queue
	 * @param	integer			$userID
	 * @return	boolean
	 */
	public function isAffectedUser(ModerationQueue $queue, $userID);
	
	/**
	 * Returns the prefix of language items for notifications for comments
	 * and comment reponses on moderation queues of this type. 
	 * 
	 * @return	string
	 * @since	3.0
	 */
	public function getCommentNotificationLanguageItemPrefix();
}
