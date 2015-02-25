<?php
namespace wcf\data\moderation\queue;
use wcf\data\DatabaseObjectEditor;
use wcf\system\moderation\queue\ModerationQueueManager;

/**
 * Extends the moderation queue object with functions to create, update and delete queue entries.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.moderation.queue
 * @category	Community Framework
 */
class ModerationQueueEditor extends DatabaseObjectEditor {
	/**
	 * @see	\wcf\data\DatabaseObjectEditor::$baseClass
	 */
	protected static $baseClass = 'wcf\data\moderation\queue\ModerationQueue';
	
	/**
	 * Marks this entry as done.
	 * 
	 * @deprecated	2.1 - Please use markAsConfirmed() or markAsRejected()
	 */
	public function markAsDone() {
		$this->update(array('status' => ModerationQueue::STATUS_DONE));
		
		// reset moderation count
		ModerationQueueManager::getInstance()->resetModerationCount();
	}
	
	/**
	 * Marks this entry as confirmed, e.g. report was justified and content was deleted or
	 * content was approved.
	 */
	public function markAsConfirmed() {
		$this->update(array('status' => ModerationQueue::STATUS_CONFIRMED));
		
		// reset moderation count
		ModerationQueueManager::getInstance()->resetModerationCount();
	}
	
	/**
	 * Marks this entry as rejected, e.g. report was unjustified or content approval was denied.
	 */
	public function markAsRejected() {
		$this->update(array('status' => ModerationQueue::STATUS_REJECTED));
		
		// reset moderation count
		ModerationQueueManager::getInstance()->resetModerationCount();
	}
	
	/**
	 * Marks this entry as in progress.
	 */
	public function markAsInProgress() {
		$this->update(array('status' => ModerationQueue::STATUS_PROCESSING));
	}
}
