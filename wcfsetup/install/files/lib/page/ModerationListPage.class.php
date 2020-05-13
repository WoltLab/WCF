<?php
namespace wcf\page;
use wcf\data\moderation\queue\ModerationQueue;
use wcf\data\moderation\queue\ViewableModerationQueueList;
use wcf\system\exception\IllegalLinkException;
use wcf\system\moderation\queue\ModerationQueueManager;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;

/**
 * List of moderation queue entries.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Page
 * 
 * @property	ViewableModerationQueueList	$objectList
 */
class ModerationListPage extends SortablePage {
	/**
	 * assigned user id
	 * @var	integer
	 */
	public $assignedUserID = -1;
	
	/**
	 * list of available definitions
	 * @var	string[]
	 */
	public $availableDefinitions = [];
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'lastChangeTime';
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortOrder = 'DESC';
	
	/**
	 * definition id for filtering
	 * @var	integer
	 */
	public $definitionID = 0;
	
	/**
	 * @inheritDoc
	 */
	public $loginRequired = true;
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['mod.general.canUseModeration'];
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = ViewableModerationQueueList::class;
	
	/**
	 * status bit
	 * @var	integer
	 */
	public $status = -1;
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['username', 'time', 'comments', 'assignedUsername', 'lastChangeTime'];
	
	/**
	 * indicates if any filter is being used
	 * @var	bool
	 */
	public $hasActiveFilter = false;
	
	/**
	 * @inheritDoc
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
		
		if ($this->assignedUserID !== -1 || $this->status !== -1 || $this->definitionID !== 0) {
			$this->hasActiveFilter = true;
		}
		
		if (!empty($_POST)) {
			$url = http_build_query($_POST, '', '&');
			HeaderUtil::redirect(LinkHandler::getInstance()->getControllerLink(static::class, [], $url));
			exit;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		// filter by object type id
		$objectTypeIDs = ModerationQueueManager::getInstance()->getObjectTypeIDs( ($this->definitionID ? [$this->definitionID] : array_keys($this->availableDefinitions)) );
		if (empty($objectTypeIDs)) {
			// no object type ids given? screw that, display nothing
			$this->objectList->getConditionBuilder()->add("0 = 1");
			return;
		}
		
		$this->objectList->getConditionBuilder()->add("moderation_queue.objectTypeID IN (?)", [$objectTypeIDs]);
		
		// filter by assigned user id
		if ($this->assignedUserID == 0) $this->objectList->getConditionBuilder()->add("moderation_queue.assignedUserID IS NULL");
		else if ($this->assignedUserID > 0) $this->objectList->getConditionBuilder()->add("moderation_queue.assignedUserID = ?", [$this->assignedUserID]);
		
		// filter by status
		if ($this->status == ModerationQueue::STATUS_DONE) {
			$this->objectList->getConditionBuilder()->add("moderation_queue.status IN (?)", [[ModerationQueue::STATUS_DONE, ModerationQueue::STATUS_CONFIRMED, ModerationQueue::STATUS_REJECTED]]);
		}
		else {
			$this->objectList->getConditionBuilder()->add("moderation_queue.status IN (?)", [[ModerationQueue::STATUS_OUTSTANDING, ModerationQueue::STATUS_PROCESSING]]);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'assignedUserID' => $this->assignedUserID,
			'availableDefinitions' => $this->availableDefinitions,
			'definitionID' => $this->definitionID,
			'status' => $this->status,
			'validSortFields' => $this->validSortFields,
			'hasActiveFilter' => $this->hasActiveFilter,
		]);
	}
}
