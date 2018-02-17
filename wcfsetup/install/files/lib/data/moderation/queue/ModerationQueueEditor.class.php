<?php
namespace wcf\data\moderation\queue;
use wcf\data\DatabaseObjectEditor;
use wcf\system\moderation\queue\ModerationQueueManager;

/**
 * Extends the moderation queue object with functions to create, update and delete queue entries.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Moderation\Queue
 * 
 * @method static	ModerationQueue		create(array $parameters = [])
 * @method		ModerationQueue		getDecoratedObject()
 * @mixin		ModerationQueue
 */
class ModerationQueueEditor extends DatabaseObjectEditor {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = ModerationQueue::class;
	
	/**
	 * Marks this entry as done.
	 * 
	 * @deprecated	2.1 - Please use markAsConfirmed() or markAsRejected()
	 */
	public function markAsDone() {
		$this->update(['status' => ModerationQueue::STATUS_DONE]);
		
		// reset moderation count
		ModerationQueueManager::getInstance()->resetModerationCount();
	}
	
	/**
	 * Marks this entry as confirmed, e.g. report was justified and content was deleted or
	 * content was approved.
	 */
	public function markAsConfirmed() {
		$this->update(['status' => ModerationQueue::STATUS_CONFIRMED]);
		
		// reset moderation count
		ModerationQueueManager::getInstance()->resetModerationCount();
	}
	
	/**
	 * Marks this entry as rejected, e.g. report was unjustified or content approval was denied.
	 * 
	 * @param       boolean         $markAsJustified
	 */
	public function markAsRejected($markAsJustified = false) {
		$data = ['status' => ModerationQueue::STATUS_REJECTED];
		if ($markAsJustified) {
			$additionalData = $this->getDecoratedObject()->additionalData;
			if (!is_array($additionalData)) $additionalData = [];
			$additionalData['markAsJustified'] = true;
			
			$data['additionalData'] = serialize($additionalData);
		}
		
		$this->update($data);
		
		// reset moderation count
		ModerationQueueManager::getInstance()->resetModerationCount();
	}
	
	/**
	 * Marks this entry as in progress.
	 */
	public function markAsInProgress() {
		$this->update(['status' => ModerationQueue::STATUS_PROCESSING]);
	}
}
