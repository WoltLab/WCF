<?php
namespace wcf\data\moderation\queue;
use wcf\data\DatabaseObjectEditor;
use wcf\system\moderation\queue\ModerationQueueManager;

/**
 * Extends the moderation queue object with functions to create, update and delete queue entries.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2014 WoltLab GmbH
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
	 */
	public function markAsDone() {
		$this->update(array('status' => ModerationQueue::STATUS_DONE));
		
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
