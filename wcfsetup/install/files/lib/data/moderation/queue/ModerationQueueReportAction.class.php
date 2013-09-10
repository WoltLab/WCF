<?php
namespace wcf\data\moderation\queue;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\UserInputException;
use wcf\system\moderation\queue\ModerationQueueReportManager;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Executes actions for reports.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.moderation.queue
 * @category	Community Framework
 */
class ModerationQueueReportAction extends ModerationQueueAction {
	/**
	 * @see	wcf\data\AbstractDatabaseObjectAction::$allowGuestAccess
	 */
	protected $allowGuestAccess = array('prepareReport', 'removeContent', 'removeReport', 'report');
	
	/**
	 * moderation queue editor object
	 * @var	wcf\data\moderation\queue\ModerationQueueEditor
	 */
	public $queue = null;
	
	/**
	 * Validates parameters to delete reported content.
	 */
	public function validateRemoveContent() {
		$this->validateRemoveReport();
		
		$this->parameters['message'] = (isset($this->parameters['message']) ? StringUtil::trim($this->parameters['message']) : '');
	}
	
	/**
	 * Deletes reported content.
	 */
	public function removeContent() {
		// mark content as deleted
		ModerationQueueReportManager::getInstance()->removeContent($this->queue->getDecoratedObject(), $this->parameters['message']);
		
		$this->queue->markAsDone();
	}
	
	/**
	 * Validates parameters to mark this report as done.
	 */
	public function validateRemoveReport() {
		$this->queue = $this->getSingleObject();
		if (!$this->queue->canEdit()) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Removes this report by marking it as done without further processing.
	 */
	public function removeReport() {
		$this->queue->markAsDone();
	}
	
	/**
	 * Validates parameters to prepare a report.
	 */
	public function validatePrepareReport() {
		$this->readInteger('objectID');
		$this->readString('objectType');
		
		if (!ModerationQueueReportManager::getInstance()->isValid($this->parameters['objectType'])) {
			throw new UserInputException('objectType');
		}
		
		// validate the combination of object type and object id
		if (!ModerationQueueReportManager::getInstance()->isValid($this->parameters['objectType'], $this->parameters['objectID'])) {
			throw new UserInputException('objectID');
		}
		
		// validate if user may read the content (prevent information disclosure by reporting random ids)
		if (!ModerationQueueReportManager::getInstance()->canReport($this->parameters['objectType'], $this->parameters['objectID'])) {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * Prepares a report.
	 */
	public function prepareReport() {
		// content was already reported
		$alreadyReported = (ModerationQueueReportManager::getInstance()->isAlreadyReported($this->parameters['objectType'], $this->parameters['objectID'])) ? 1 : 0;
		
		WCF::getTPL()->assign(array(
			'alreadyReported' => $alreadyReported,
			'object' => ModerationQueueReportManager::getInstance()->getReportedObject($this->parameters['objectType'], $this->parameters['objectID'])
		));
		
		return array(
			'alreadyReported' => $alreadyReported,
			'template' => WCF::getTPL()->fetch('moderationReportDialog')
		);
	}
	
	/**
	 * Validates parameters for reporting.
	 */
	public function validateReport() {
		$this->readString('message');
		
		$this->validatePrepareReport();
	}
	
	/**
	 * Reports an item.
	 */
	public function report() {
		// if the specified content was already reported, e.g. a different user reported this
		// item meanwhile, silently ignore it. Just display a success and the user is happy :)
		if (!ModerationQueueReportManager::getInstance()->isAlreadyReported($this->parameters['objectType'], $this->parameters['objectID'])) {
			ModerationQueueReportManager::getInstance()->addReport(
				$this->parameters['objectType'],
				$this->parameters['objectID'],
				$this->parameters['message']
			);
		}
		
		return array(
			'reported' => 1
		);
	}
}
