<?php
namespace wcf\system\moderation\queue;
use wcf\data\moderation\queue\ModerationQueue;

/**
 * Default interface for moderation queue handlers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.moderation.queue
 * @category	Community Framework
 */
interface IModerationQueueHandler {
	/**
	 * Creates queue assignments for matching object ids.
	 * 
	 * @param	array<wcf\data\moderation\queue\ModerationQueue>	$queues
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
	 * Returns true if given object id is valid.
	 * 
	 * @param	integer		$objectID
	 * @return	boolean
	 */
	public function isValid($objectID);
	
	/**
	 * Populates object properties for viewing.
	 * 
	 * @param	array<wcf\data\moderation\queue\ViewableModerationQueue>	$queues
	 */
	public function populate(array $queues);
	
	/**
	 * Removes affected content. It is up to the processing class to either
	 * soft-delete the content or remove it permanently.
	 * 
	 * @param	wcf\data\moderation\queue\ModerationQueue	$queue
	 * @param	string						$message
	 */
	public function removeContent(ModerationQueue $queue, $message);
	
	/**
	 * Removes queses from database, should only be called if the referenced
	 * object is permanently deleted.
	 * 
	 * @param	array<integer>		$objectIDs
	 */
	public function removeQueues(array $objectIDs);
}
