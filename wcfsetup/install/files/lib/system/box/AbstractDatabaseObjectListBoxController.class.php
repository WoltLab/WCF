<?php
namespace wcf\system\box;
use wcf\data\box\Box;
use wcf\data\object\type\ObjectType;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\DatabaseObjectList;
use wcf\system\condition\ConditionHandler;
use wcf\system\condition\IObjectListCondition;
use wcf\system\event\EventHandler;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Default implementation of a box controller based on an object list.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Box
 * @since	3.0
 */
abstract class AbstractDatabaseObjectListBoxController extends AbstractBoxController implements IConditionBoxController {
	/**
	 * name of the object type definition for the box controller's condition object types
	 * @var	string
	 */
	protected $conditionDefinition = '';
	
	/**
	 * condition objects types registered for the dynamic box controller
	 * @var	ObjectType[]
	 */
	protected $conditionObjectTypes = [];
	
	/**
	 * default limit value for the maximum number of shown database objects
	 * if this property is null, setting a limit is disabled
	 * @var	integer
	 */
	public $defaultLimit;
	
	/**
	 * default sort field
	 * @var	string
	 */
	public $defaultSortField;
	
	/**
	 * default sort order
	 * @var	string
	 */
	public $defaultSortOrder;
	
	/**
	 * limit value for the maximum number of shown database objects
	 * @var	integer
	 */
	public $limit;
	
	/**
	 * maximum limit value, if `null` no maximum is set
	 * @var	integer
	 */
	public $maximumLimit;
	
	/**
	 * minimum limit value
	 * @var	integer
	 */
	public $minimumLimit = 1;
	
	/**
	 * database object list used to read the objects displayed in the box
	 * @var	DatabaseObjectList
	 */
	public $objectList;
	
	/**
	 * name of the database table column used for sorting
	 * @var	string
	 */
	public $sortField;
	
	/**
	 * prefix used for the titles of the sort fields
	 * @var	string
	 */
	protected $sortFieldLanguageItemPrefix;
	
	/**
	 * order used for sorting the database objects
	 * @var	string
	 */
	public $sortOrder;
	
	/**
	 * list of valid sort fields
	 * if this property is null, sorting is disabled
	 * @var	string[]
	 */
	public $validSortFields;
	
	/**
	 * Creates a new instance of AbstractDynamicBoxController.
	 * 
	 * @throws	\LogicException
	 */
	public function __construct() {
		if ($this->conditionDefinition) {
			if (ObjectTypeCache::getInstance()->getDefinitionByName($this->conditionDefinition) === null) {
				throw new \LogicException("Unknown object type definition '" . $this->conditionDefinition . "'");
			}
			
			$this->conditionObjectTypes = ObjectTypeCache::getInstance()->getObjectTypes($this->conditionDefinition);
		}
		
		if (!empty($this->validSortFields)) {
			$this->sortField = $this->defaultSortField;
			$this->sortOrder = $this->defaultSortOrder;
		}
		
		if ($this->defaultLimit !== null) {
			if ($this->defaultLimit <= 0) {
				throw new \LogicException("The default limit may has to be positive.");
			}
			
			$this->limit = $this->defaultLimit;
		}
		
		parent::__construct();
	}
	
	/**
	 * @inheritDoc
	 */
	protected function getAdditionalData() {
		return [
			'limit' => $this->limit,
			'sortField' => $this->sortField,
			'sortOrder' => $this->sortOrder
		];
	}
	
	/**
	 * @inheritDoc
	 */
	public function getConditionDefinition() {
		return $this->conditionDefinition;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getConditionObjectTypes() {
		return $this->conditionObjectTypes;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getConditionsTemplate() {
		if ($this->defaultLimit !== null || !empty($this->validSortFields) || !empty($this->conditionObjectTypes)) {
			return WCF::getTPL()->fetch('boxConditions', 'wcf', [
				'boxController' => $this,
				'conditionObjectTypes' => $this->conditionObjectTypes,
				'defaultLimit' => $this->defaultLimit,
				'limit' => $this->limit,
				'maximumLimit' => $this->maximumLimit,
				'minimumLimit' => $this->minimumLimit,
				'sortField' => $this->sortField,
				'sortFieldLanguageItemPrefix' => $this->sortFieldLanguageItemPrefix,
				'sortOrder' => $this->sortOrder,
				'validSortFields' => $this->validSortFields
			], true);
		}
		
		return '';
	}
	
	/**
	 * Returns the database object list used to read the objects displayed in the box.
	 *
	 * @return	DatabaseObjectList
	 */
	abstract protected function getObjectList();
	
	/**
	 * Returns the template to display the box.
	 * 
	 * @return	string
	 */
	abstract protected function getTemplate();
	
	/**
	 * @inheritDoc
	 */
	public function hasContent() {
		EventHandler::getInstance()->fireAction($this, 'hasContent');

		if ($this->objectList === null) {
			$this->loadContent();
		}
		
		return ($this->objectList !== null && count($this->objectList) > 0);
	}
	
	/**
	 * @inheritDoc
	 */
	protected function loadContent() {
		EventHandler::getInstance()->fireAction($this, 'beforeLoadContent');
		
		$this->objectList = $this->getObjectList();
		
		if ($this->limit) {
			$this->objectList->sqlLimit = $this->limit;
		}
		
		if ($this->sortOrder && $this->sortField) {
			$alias = $this->objectList->getDatabaseTableAlias();
			$this->objectList->sqlOrderBy = $this->sortField . ' ' . $this->sortOrder . ", " . ($alias ? $alias . "." : "") . $this->objectList->getDatabaseTableIndexName() . " " . $this->sortOrder;
		}
		
		if ($this->conditionDefinition) {
			foreach ($this->box->getConditions() as $condition) {
				/** @var IObjectListCondition $processor */
				$processor = $condition->getObjectType()->getProcessor();
				$processor->addObjectListCondition($this->objectList, $condition->conditionData);
			}
		}
		
		$this->readObjects();
		
		$this->content = $this->getTemplate();
		
		EventHandler::getInstance()->fireAction($this, 'afterLoadContent');
	}
	
	/**
	 * @inheritDoc
	 */
	public function readConditions() {
		if (isset($_POST['limit'])) $this->limit = intval($_POST['limit']);
		if (isset($_POST['sortField'])) $this->sortField = StringUtil::trim($_POST['sortField']);
		if (isset($_POST['sortOrder'])) $this->sortOrder = StringUtil::trim($_POST['sortOrder']);
		
		foreach ($this->conditionObjectTypes as $objectType) {
			$objectType->getProcessor()->readFormParameters();
		}
	}
	
	/**
	 * Reads the displayed database objects.
	 */
	protected function readObjects() {
		EventHandler::getInstance()->fireAction($this, 'readObjects');
		
		$this->objectList->readObjects();
	}
	
	/**
	 * @inheritDoc
	 */
	public function saveAdditionalData() {
		parent::saveAdditionalData();
		
		if ($this->conditionDefinition) {
			// do not use Box::getConditions() here to avoid setting box data by internally calling
			// Box::getController()
			ConditionHandler::getInstance()->updateConditions(
				$this->box->boxID,
				ConditionHandler::getInstance()->getConditions($this->conditionDefinition, $this->box->boxID),
				$this->conditionObjectTypes
			);
		}
	}
	
	/**
	 * Sets the box the controller object belongs to and populates the condition object types
	 * with the box conditions.
	 * 
	 * @param	Box		$box			box object
	 * @param	boolean		$setConditionData	if true, the condition object types are populated with the box conditions' data
	 */
	public function setBox(Box $box, $setConditionData = true) {
		parent::setBox($box);
		
		if ($setConditionData) {
			if ($this->defaultLimit !== null && $this->box->limit) {
				$this->limit = $this->box->limit;
			}
			
			if (!empty($this->validSortFields) && $this->box->sortOrder && $this->box->sortField) {
				$this->sortOrder = $this->box->sortOrder;
				$this->sortField = $this->box->sortField;
			}
			
			if ($this->conditionDefinition) {
				$conditions = [];
				foreach ($this->box->getConditions() as $condition) {
					$conditions[$condition->objectTypeID] = $condition;
				}
				
				foreach ($this->conditionObjectTypes as $objectType) {
					if (isset($conditions[$objectType->objectTypeID])) {
						$objectType->getProcessor()->setData($conditions[$objectType->objectTypeID]);
					}
				}
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateConditions() {
		if ($this->defaultLimit !== null) {
			if ($this->limit < $this->minimumLimit) {
				throw new UserInputException('limit', 'greaterThan');
			}
			else if ($this->maximumLimit !== null && $this->limit > $this->maximumLimit) {
				throw new UserInputException('limit', 'lessThan');
			}
		}
		
		if (!empty($this->validSortFields)) {
			if (!in_array($this->sortField, $this->validSortFields)) {
				throw new UserInputException('sorting', 'invalidSortField');
			}
			
			if ($this->sortOrder !== 'ASC' && $this->sortOrder !== 'DESC') {
				throw new UserInputException('sorting', 'invalidSortOrder');
			}
		}
		
		foreach ($this->conditionObjectTypes as $objectType) {
			$objectType->getProcessor()->validate();
		}
	}
}
