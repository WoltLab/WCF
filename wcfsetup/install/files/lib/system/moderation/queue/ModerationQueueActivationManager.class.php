<?php
namespace wcf\system\moderation\queue;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ViewableModerationQueue;
use wcf\system\exception\SystemException;
use wcf\system\request\LinkHandler;

/**
 * Moderation queue implementation for moderated content.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.moderation.queue
 * @category	Community Framework
 */
class ModerationQueueActivationManager extends AbstractModerationQueueManager {
	/**
	 * @see	\wcf\system\moderation\queue\AbstractModerationQueueManager::$definitionName
	 */
	protected $definitionName = 'com.woltlab.wcf.moderation.activation';
	
	/**
	 * Enables affected content.
	 * 
	 * @param	\wcf\data\moderation\queue\ModerationQueue	$queue
	 */
	public function enableContent(ModerationQueue $queue) {
		$this->getProcessor(null, $queue->objectTypeID)->enableContent($queue);
	}
	
	/**
	 * Returns outstanding content.
	 * 
	 * @param	\wcf\data\moderation\queue\ViewableModerationQueue	$queue
	 * @return	string
	 */
	public function getDisabledContent(ViewableModerationQueue $queue) {
		return $this->getProcessor(null, $queue->objectTypeID)->getDisabledContent($queue);
	}
	
	/**
	 * @see	\wcf\system\moderation\queue\IModerationQueueManager::getLink()
	 */
	public function getLink($queueID) {
		return LinkHandler::getInstance()->getLink('ModerationActivation', array('id' => $queueID));
	}
	
	/**
	 * Adds an entry for moderated content.
	 * 
	 * @param	string		$objectType
	 * @param	integer		$objectID
	 * @param	array		$additionalData
	 */
	public function addModeratedContent($objectType, $objectID, array $additionalData = array()) {
		if (!$this->isValid($objectType)) {
			throw new SystemException("Object type '".$objectType."' is not valid for definition 'com.woltlab.wcf.moderation.activation'");
		}
		
		$this->addEntry(
			$this->getObjectTypeID($objectType),
			$objectID,
			$this->getProcessor($objectType)->getContainerID($objectID),
			$additionalData
		);
	}
	
	/**
	 * Marks entries from moderation queue as done.
	 * 
	 * @param	string		$objectType
	 * @param	array<integer>	$objectIDs
	 */
	public function removeModeratedContent($objectType, array $objectIDs) {
		if (!$this->isValid($objectType)) {
			throw new SystemException("Object type '".$objectType."' is not valid for definition 'com.woltlab.wcf.moderation.activation'");
		}
		
		$this->removeEntries($this->getObjectTypeID($objectType), $objectIDs);
	}
}
