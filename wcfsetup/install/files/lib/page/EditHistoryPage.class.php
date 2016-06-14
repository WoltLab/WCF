<?php
namespace wcf\page;
use wcf\data\edit\history\entry\EditHistoryEntry;
use wcf\data\edit\history\entry\EditHistoryEntryList;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\DatabaseObjectList;
use wcf\system\edit\IHistorySavingObject;
use wcf\system\edit\IHistorySavingObjectTypeProvider;
use wcf\system\exception\IllegalLinkException;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\Diff;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;

/**
 * Compares two templates.
 * 
 * @author	Tim Duesterhus
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Page
 */
class EditHistoryPage extends AbstractPage {
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['MODULE_EDIT_HISTORY'];
	
	/**
	 * DatabaseObjectList object
	 * @var	DatabaseObjectList
	 */
	public $objectList;
	
	/**
	 * left / old version id
	 * @var	integer
	 */
	public $oldID = 0;
	
	/**
	 * left / old version
	 * @var	EditHistoryEntry
	 */
	public $old;
	
	/**
	 * right / new version id
	 * @var	integer
	 */
	public $newID = 0;
	
	/**
	 * right / new version
	 * @var	EditHistoryEntry
	 */
	public $new;
	
	/**
	 * differences between both versions
	 * @var	Diff
	 */
	public $diff;
	
	/**
	 * object type of the requested object
	 * @var	ObjectType
	 */
	public $objectType;
	
	/**
	 * id of the requested object
	 * @var	integer
	 */
	public $objectID = 0;
	
	/**
	 * requested object
	 * @var	IHistorySavingObject
	 */
	public $object;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['oldID'])) {
			$this->oldID = intval($_REQUEST['oldID']);
			$this->old = new EditHistoryEntry($this->oldID);
			if (!$this->old->entryID) throw new IllegalLinkException();
			
			if (isset($_REQUEST['newID']) && $_REQUEST['newID'] !== 'current') {
				$this->newID = intval($_REQUEST['newID']);
				$this->new = new EditHistoryEntry($this->newID);
				if (!$this->new->entryID) throw new IllegalLinkException();
			}
			
			// if new version isn't 'current' check whether they are comparable
			if ($this->new) {
				// different objectTypes cannot be compared
				if ($this->old->objectTypeID != $this->new->objectTypeID) throw new IllegalLinkException();
				// different items cannot be compared
				if ($this->old->objectID != $this->new->objectID) throw new IllegalLinkException();
			}
			
			$this->objectID = $this->old->objectID;
			$this->objectType = ObjectTypeCache::getInstance()->getObjectType($this->old->objectTypeID);
		}
		else if (isset($_REQUEST['objectID']) && isset($_REQUEST['objectType'])) {
			$this->objectID = intval($_REQUEST['objectID']);
			$this->objectType = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.edit.historySavingObject', $_REQUEST['objectType']);
		}
		else {
			throw new IllegalLinkException();
		}
		
		if (!$this->objectType) throw new IllegalLinkException();
		
		/** @var IHistorySavingObjectTypeProvider $processor */
		$processor = $this->objectType->getProcessor();
		
		/** @var IHistorySavingObject object */
		$this->object = $processor->getObjectByID($this->objectID);
		if (!$this->object->getObjectID()) throw new IllegalLinkException();
		$processor->checkPermissions($this->object);
		$this->object->setLocation();
		
		if (isset($_REQUEST['newID']) && !$this->new) {
			$this->new = $this->object;
			$this->newID = 'current';
		}
		
		if (!empty($_POST)) {
			HeaderUtil::redirect(LinkHandler::getInstance()->getLink('EditHistory', [
				'objectID' => $this->objectID,
				'objectType' => $this->objectType->objectType,
				'newID' => $this->newID,
				'oldID' => $this->oldID
			]));
			exit;
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		parent::readData();
		
		$this->objectList = new EditHistoryEntryList();
		$this->objectList->sqlOrderBy = "time DESC, entryID DESC";
		$this->objectList->getConditionBuilder()->add('objectTypeID = ?', [$this->objectType->objectTypeID]);
		$this->objectList->getConditionBuilder()->add('objectID = ?', [$this->objectID]);
		$this->objectList->readObjects();
		
		// valid IDs were given, calculate diff
		if ($this->old && $this->new) {
			$a = explode("\n", StringUtil::unifyNewlines($this->old->getMessage()));
			$b = explode("\n", StringUtil::unifyNewlines($this->new->getMessage()));
			$this->diff = new Diff($a, $b);
		}
		
		// set default values
		if (!isset($_REQUEST['oldID']) && !isset($_REQUEST['newID'])) {
			foreach ($this->objectList as $object) {
				$this->oldID = $object->entryID;
				break;
			}
			$this->newID = 'current';
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'oldID' => $this->oldID,
			'old' => $this->old,
			'newID' => $this->newID,
			'new' => $this->new,
			'object' => $this->object,
			'diff' => $this->diff,
			'objects' => $this->objectList,
			'objectID' => $this->objectID,
			'objectType' => $this->objectType
		]);
	}
}
