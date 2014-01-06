<?php
namespace wcf\system\moderation\queue;
use wcf\data\moderation\queue\ModerationQueue;

/**
 * Default interface for moderation queue managers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.moderation.queue
 * @category	Community Framework
 */
interface IModerationQueueManager {
	/**
	 * Creates queue assignments for matching object type ids.
	 * 
	 * @param	integer							$objectTypeID
	 * @param	array<\wcf\data\moderation\queue\ModerationQueue>	$queues
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
	 * @param	integer								$objectTypeID
	 * @param	array<\wcf\data\moderation\queue\ViewableModerationQueue>	$objects
	 */
	public function populate($objectTypeID, array $objects);
	
	/**
	 * Removes affected content. It is up to the processing object to use a
	 * soft-delete or remove the content permanently.
	 * 
	 * @param	\wcf\data\moderation\queue\ModerationQueue	$queue
	 * @param	string						$message
	 */
	public function removeContent(ModerationQueue $queue, $message = '');
}
