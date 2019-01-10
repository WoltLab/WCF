<?php
namespace wcf\acp\page;
use wcf\data\DatabaseObject;
use wcf\data\modification\log\IViewableModificationLog;
use wcf\data\modification\log\ModificationLogList;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\package\Package;
use wcf\page\SortablePage;
use wcf\system\log\modification\IExtendedModificationLogHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows a list of modification log items.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2018 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\Acp\Page
 *
 * @property    ModificationLogList $objectList
 * @since       5.2
 */
class ModificationLogListPage extends SortablePage {
	/**
	 * filter by action
	 *
	 * @var string
	 */
	public $action = '';
	
	/**
	 * list of available actions per package
	 *
	 * @var string[][]
	 */
	public $actions = [];
	
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.log.modification';
	
	/**
	 * filter by time
	 *
	 * @var string
	 */
	public $afterDate = '';
	
	/**
	 * @var int[]
	 */
	public $availableObjectTypeIDs = [];
	
	/**
	 * filter by time
	 *
	 * @var string
	 */
	public $beforeDate = '';
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'time';
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortOrder = 'DESC';
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = ModificationLogList::class;
	
	/**
	 * @var IViewableModificationLog[]
	 */
	public $logItems = [];
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.management.canViewLog'];
	
	/**
	 * @var ObjectType[]
	 */
	public $objectTypes = [];
	
	/**
	 * @var Package[]
	 */
	public $packages = [];
	
	/**
	 * filter by package id
	 *
	 * @var int
	 */
	public $packageID = 0;
	
	/**
	 * list of object types that are not implementing the new API
	 *
	 * @var ObjectType
	 */
	public $unsupportedObjectTypes = [];
	
	/**
	 * filter by username
	 *
	 * @var string
	 */
	public $username = '';
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = [
		'logID',
		'username',
		'time'
	];
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		$this->initObjectTypes();
		
		if (!empty($_REQUEST['action'])) {
			$this->action = StringUtil::trim($_REQUEST['action']);
		}
		if (!empty($_REQUEST['afterDate'])) {
			$this->afterDate = StringUtil::trim($_REQUEST['afterDate']);
		}
		if (!empty($_REQUEST['beforeDate'])) {
			$this->beforeDate = StringUtil::trim($_REQUEST['beforeDate']);
		}
		if (!empty($_REQUEST['packageID'])) {
			$this->packageID = intval($_REQUEST['packageID']);
		}
		if (!empty($_REQUEST['username'])) {
			$this->username = StringUtil::trim($_REQUEST['username']);
		}
	}
	
	protected function initObjectTypes() {
		foreach (ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.modifiableContent') as $objectType) {
			$this->objectTypes[$objectType->objectTypeID] = $objectType;
			
			/** @var IExtendedModificationLogHandler $processor */
			$processor = $objectType->getProcessor();
			if ($processor === null) {
				$this->unsupportedObjectTypes[] = $objectType;
			}
			else if ($processor->includeInLogList()) {
				$this->availableObjectTypeIDs[] = $objectType->objectTypeID;
				if (!isset($this->packages[$objectType->packageID])) {
					$this->actions[$objectType->packageID] = [];
					$this->packages[$objectType->packageID] = $objectType->getPackage();
				}
				
				foreach ($processor->getAvailableActions() as $action) {
					$this->actions[$objectType->packageID]["{$objectType->objectType}-{$action}"] = WCF::getLanguage()->get("wcf.acp.modificationLog.{$objectType->objectType}.{$action}");
				}
			}
		}
		
		foreach ($this->actions as &$actions) {
			asort($actions, SORT_NATURAL);
		}
		unset($actions);
		
		uasort($this->packages, function (Package $a, Package $b) {
			return strnatcasecmp($a->package, $b->package);
		});
	}
	
	/**
	 * @inheritDoc
	 */
	public function initObjectList() {
		parent::initObjectList();
		
		if (!empty($this->availableObjectTypeIDs)) {
			$action = '';
			$objectTypeID = 0;
			if (preg_match('~^(?P<objectType>.+)\-(?P<action>[^\-]+)$~', $this->action, $matches)) {
				foreach ($this->objectTypes as $objectType) {
					if ($objectType->objectType === $matches['objectType']) {
						/** @var IExtendedModificationLogHandler $processor */
						$processor = $objectType->getProcessor();
						if ($processor !== null && in_array($matches['action'], $processor->getAvailableActions())) {
							$action = $matches['action'];
							$objectTypeID = $objectType->objectTypeID;
						}
						
						break;
					}
				}
			}
			
			if ($objectTypeID) {
				$this->objectList->getConditionBuilder()->add('modification_log.objectTypeID = ?', [$objectTypeID]);
				$this->objectList->getConditionBuilder()->add('modification_log.action = ?', [$action]);
			}
			else {
				if (isset($this->packages[$this->packageID])) {
					$objectTypeIDs = [];
					foreach ($this->objectTypes as $objectType) {
						if ($objectType->packageID == $this->packageID) {
							$objectTypeIDs[] = $objectType->objectTypeID;
						}
					}
					
					$this->objectList->getConditionBuilder()->add('modification_log.objectTypeID IN (?)', [$objectTypeIDs]);
				}
				else {
					$this->objectList->getConditionBuilder()->add('modification_log.objectTypeID IN (?)', [$this->availableObjectTypeIDs]);
				}
			}
			
			if (!empty($this->username)) {
				$this->objectList->getConditionBuilder()->add('modification_log.username LIKE ?', [addcslashes($this->username, '%') . '%']);
			}
			
			$afterDate = $beforeDate = 0;
			if (!empty($this->afterDate)) {
				$afterDate = intval(@strtotime($this->afterDate));
			}
			if (!empty($this->beforeDate)) {
				$beforeDate = intval(@strtotime($this->beforeDate));
			}
			
			if ($afterDate && $beforeDate) {
				$this->objectList->getConditionBuilder()->add('modification_log.time BETWEEN ? AND ?', [
					$afterDate,
					$beforeDate
				]);
			}
			else {
				if ($afterDate) {
					$this->objectList->getConditionBuilder()->add('modification_log.time > ?', [$afterDate]);
				}
				else if ($beforeDate) {
					$this->objectList->getConditionBuilder()->add('modification_log.time < ?', [$beforeDate]);
				}
			}
		}
		else {
			$this->objectList->getConditionBuilder()->add('1=0');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		$itemsPerType = [];
		foreach ($this->objectList as $modificationLog) {
			if (!isset($itemsPerType[$modificationLog->objectTypeID])) {
				$itemsPerType[$modificationLog->objectTypeID] = [];
			}
			
			$itemsPerType[$modificationLog->objectTypeID][] = $modificationLog;
		}
		
		if (!empty($itemsPerType)) {
			foreach ($this->objectTypes as $objectType) {
				/** @var IExtendedModificationLogHandler $processor */
				$processor = $objectType->getProcessor();
				if ($processor === null) {
					continue;
				}
				
				if (isset($itemsPerType[$objectType->objectTypeID])) {
					$this->logItems = array_merge($this->logItems, $processor->processItems($itemsPerType[$objectType->objectTypeID]));
				}
			}
		}
		
		DatabaseObject::sort($this->logItems, $this->sortField, $this->sortOrder);
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'action' => $this->action,
			'actions' => $this->actions,
			'afterDate' => $this->afterDate,
			'beforeDate' => $this->beforeDate,
			'logItems' => $this->logItems,
			'objectTypes' => $this->objectTypes,
			'packageID' => $this->packageID,
			'packages' => $this->packages,
			'unsupportedObjectTypes' => $this->unsupportedObjectTypes,
			'username' => $this->username,
		]);
	}
}
