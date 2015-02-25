<?php
namespace wcf\page;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\system\exception\IllegalLinkException;
use wcf\system\moderation\queue\ModerationQueueManager;
use wcf\system\WCF;

/**
 * List of moderation queue entries.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	page
 * @category	Community Framework
 */
class ModerationListPage extends SortablePage {
	/**
	 * assigned user id
	 * @var	integer
	 */
	public $assignedUserID = -1;
	
	/**
	 * list of available definitions
	 * @var	array<string>
	 */
	public $availableDefinitions = array();
	
	/**
	 * @see	\wcf\page\SortablePage::$defaultSortField
	 */
	public $defaultSortField = 'lastChangeTime';
	
	/**
	 * @see	\wcf\page\SortablePage::$defaultSortField
	 */
	public $defaultSortOrder = 'DESC';
	
	/**
	 * definiton id for filtering
	 * @var	integer
	 */
	public $definitionID = 0;
	
	/**
	 * @see	\wcf\page\AbstractPage::$loginRequired
	 */
	public $loginRequired = true;
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('mod.general.canUseModeration');
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$objectListClassName
	 */
	public $objectListClassName = 'wcf\data\moderation\queue\ViewableModerationQueueList';
	
	/**
	 * status bit
	 * @var	integer
	 */
	public $status = -1;
	
	/**
	 * @see	\wcf\page\SortablePage::$validSortFields
	 */
	public $validSortFields = array('assignedUsername', 'lastChangeTime', 'queueID', 'time', 'username', 'comments');
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['assignedUserID'])) $this->assignedUserID = intval($_REQUEST['assignedUserID']);
		if (isset($_REQUEST['status'])) $this->status = intval($_REQUEST['status']);
		
		$this->availableDefinitions = ModerationQueueManager::getInstance()->getDefinitions();
		if (isset($_REQUEST['definitionID'])) {
			$this->definitionID = intval($_REQUEST['definitionID']);
			if ($this->definitionID && !isset($this->availableDefinitions[$this->definitionID])) {
				throw new IllegalLinkException();
			}
		}
	}
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::initObjectList()
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		// filter by object type id
		$objectTypeIDs = ModerationQueueManager::getInstance()->getObjectTypeIDs( ($this->definitionID ? array($this->definitionID) : array_keys($this->availableDefinitions)) );
		if (empty($objectTypeIDs)) {
			// no object type ids given? screw that, display nothing
			$this->objectList->getConditionBuilder()->add("0 = 1");
			return;
		}
		
		$this->objectList->getConditionBuilder()->add("moderation_queue.objectTypeID IN (?)", array($objectTypeIDs));
		
		// filter by assigned user id
		if ($this->assignedUserID == 0) $this->objectList->getConditionBuilder()->add("moderation_queue.assignedUserID IS NULL");
		else if ($this->assignedUserID > 0) $this->objectList->getConditionBuilder()->add("moderation_queue.assignedUserID = ?", array($this->assignedUserID));
		
		// filter by status
		if ($this->status == ModerationQueue::STATUS_DONE) {
			$this->objectList->getConditionBuilder()->add("moderation_queue.status IN (?)", array(array(ModerationQueue::STATUS_DONE, ModerationQueue::STATUS_CONFIRMED, ModerationQueue::STATUS_REJECTED)));
		}
		else {
			$this->objectList->getConditionBuilder()->add("moderation_queue.status IN (?)", array(array(ModerationQueue::STATUS_OUTSTANDING, ModerationQueue::STATUS_PROCESSING)));
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'assignedUserID' => $this->assignedUserID,
			'availableDefinitions' => $this->availableDefinitions,
			'definitionID' => $this->definitionID,
			'definitionNames' => ModerationQueueManager::getInstance()->getDefinitionNamesByObjectTypeIDs(),
			'status' => $this->status
		));
	}
}
