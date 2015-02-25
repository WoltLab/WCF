<?php
namespace wcf\data\moderation\queue;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\moderation\queue\ModerationQueueActivationManager;

/**
 * Executes actions for reports.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.moderation.queue
 * @category	Community Framework
 */
class ModerationQueueActivationAction extends ModerationQueueAction {
	/**
	 * @see	\wcf\data\AbstractDatabaseObjectAction::$allowGuestAccess
	 */
	protected $allowGuestAccess = array('enableContent', 'removeContent');
	
	/**
	 * moderation queue editor object
	 * @var	\wcf\data\moderation\queue\ModerationQueueEditor
	 */
	public $queue = null;
	
	/**
	 * Validates parameters to enable content.
	 */
	public function validateEnableContent() {
		$this->queue = $this->getSingleObject();
		if (!$this->queue->canEdit()) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Enables content.
	 */
	public function enableContent() {
		// enable content
		ModerationQueueActivationManager::getInstance()->enableContent($this->queue->getDecoratedObject());
		
		$this->queue->markAsConfirmed();
	}
	
	/**
	 * Validates parameters to delete reported content.
	 */
	public function validateRemoveContent() {
		$this->readString('message', true);
		$this->validateEnableContent();
	}
	
	/**
	 * Deletes reported content.
	 */
	public function removeContent() {
		// mark content as deleted
		ModerationQueueActivationManager::getInstance()->removeContent($this->queue->getDecoratedObject(), $this->parameters['message']);
		
		$this->queue->markAsRejected();
	}
}
