<?php
namespace wcf\system\moderation\queue;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ViewableModerationQueue;

/**
 * Default interface for moderation queue managers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Moderation\Queue
 */
interface IModerationQueueManager {
	/**
	 * Creates queue assignments for matching object type ids.
	 * 
	 * @param	integer			$objectTypeID
	 * @param	ModerationQueue[]	$queues
	 */
	public function assignQueues($objectTypeID, array $queues);
	
	/**
	 * Returns true if given object type is valid, optionally checking object id.
	 * 
	 * @param	string		$objectType
	 * @param	integer		$objectID
	 * @return	boolean
	 */
	public function isValid($objectType, $objectID = null);
	
	/**
	 * Returns link for viewing/editing objects for this moderation type.
	 * 
	 * @param	integer		$queueID
	 * @return	string
	 */
	public function getLink($queueID);
	
	/**
	 * Returns object type id for given object type.
	 * 
	 * @param	string		$objectType
	 * @return	integer
	 */
	public function getObjectTypeID($objectType);
	
	/**
	 * Returns object type processor by object type.
	 * 
	 * @param	string		$objectType
	 * @param	integer		$objectTypeID
	 * @return	object
	 */
	public function getProcessor($objectType, $objectTypeID = null);
	
	/**
	 * Populates object properties for viewing.
	 * 
	 * @param	integer				$objectTypeID
	 * @param	ViewableModerationQueue[]	$objects
	 */
	public function populate($objectTypeID, array $objects);
	
	/**
	 * Returns whether the afftected content may be removed.
	 * 
	 * @param	\wcf\data\moderation\queue\ModerationQueue	$queue
	 * @return	boolean
	 */
	public function canRemoveContent(ModerationQueue $queue);
	
	/**
	 * Removes affected content. It is up to the processing object to use a
	 * soft-delete or remove the content permanently.
	 * 
	 * @param	\wcf\data\moderation\queue\ModerationQueue	$queue
	 * @param	string						$message
	 */
	public function removeContent(ModerationQueue $queue, $message = '');
}
