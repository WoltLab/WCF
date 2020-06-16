<?php
namespace wcf\system\moderation\queue;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\system\exception\InvalidObjectTypeException;
use wcf\system\request\LinkHandler;

/**
 * Moderation queue implementation for moderated content.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Moderation\Queue
 */
class ModerationQueueActivationManager extends AbstractModerationQueueManager {
	/**
	 * @inheritDoc
	 */
	protected $definitionName = 'com.woltlab.wcf.moderation.activation';
	
	/**
	 * Enables affected content.
	 * 
	 * @param	ModerationQueue		$queue
	 */
	public function enableContent(ModerationQueue $queue) {
		$this->getProcessor(null, $queue->objectTypeID)->enableContent($queue);
	}
	
	/**
	 * Returns outstanding content.
	 * 
	 * @param	ViewableModerationQueue		$queue
	 * @return	string
	 */
	public function getDisabledContent(ViewableModerationQueue $queue) {
		return $this->getProcessor(null, $queue->objectTypeID)->getDisabledContent($queue);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getLink($queueID) {
		return LinkHandler::getInstance()->getLink('ModerationActivation', [
			'id' => $queueID,
			'forceFrontend' => true
		]);
	}
	
	/**
	 * Adds an entry for moderated content.
	 * 
	 * @param	string		$objectType
	 * @param	integer		$objectID
	 * @param	array		$additionalData
	 * @throws	InvalidObjectTypeException
	 */
	public function addModeratedContent($objectType, $objectID, array $additionalData = []) {
		if (!$this->isValid($objectType)) {
			throw new InvalidObjectTypeException($objectType, 'com.woltlab.wcf.moderation.activation');
		}
		
		$this->addEntry(
			$this->getObjectTypeID($objectType),
			$objectID,
			$this->getProcessor($objectType)->getContainerID($objectID),
			$additionalData
		);
	}
	
	/**
	 * Adds multiple entries for moderated content at once.
	 * 
	 * In contrast to `addModeratedContent()`, this method expects the container ids to be
	 * passed as a parameter. If no container id is given for a specific object id, `0` is
	 * used as container id.
	 * 
	 * This method is intended for bulk processing.
	 * 
	 * @param	string		$objectType
	 * @param	integer[]	$objectID
	 * @poram	integer[]	$containerIDs		format: `objectID => containerID`
	 * @param	array		$additionalData
	 * @throws	InvalidObjectTypeException
	 */
	public function addModeratedContents($objectType, array $objectIDs, array $containerIDs, array $additionalData = []) {
		if (!$this->isValid($objectType)) {
			throw new InvalidObjectTypeException($objectType, 'com.woltlab.wcf.moderation.activation');
		}
		
		$this->addEntries(
			$this->getObjectTypeID($objectType),
			$objectIDs,
			$containerIDs,
			$additionalData
		);
	} 
	
	/**
	 * Marks entries from moderation queue as done.
	 * 
	 * @param	string		$objectType
	 * @param	integer[]	$objectIDs
	 * @throws	InvalidObjectTypeException
	 */
	public function removeModeratedContent($objectType, array $objectIDs) {
		if (!$this->isValid($objectType)) {
			throw new InvalidObjectTypeException($objectType, 'com.woltlab.wcf.moderation.activation');
		}
		
		$this->removeEntries($this->getObjectTypeID($objectType), $objectIDs);
	}
}
