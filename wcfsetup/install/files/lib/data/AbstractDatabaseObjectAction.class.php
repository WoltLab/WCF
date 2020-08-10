<?php
namespace wcf\data;
use wcf\system\event\EventHandler;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\request\RequestHandler;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\JSON;
use wcf\util\StringUtil;

/**
 * Default implementation for DatabaseObject-related actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data
 */
abstract class AbstractDatabaseObjectAction implements IDatabaseObjectAction, IDeleteAction {
	/**
	 * pending action
	 * @var	string
	 */
	protected $action = '';
	
	/**
	 * object editor class name
	 * @var	string
	 */
	protected $className = '';
	
	/**
	 * list of object ids
	 * @var	integer[]
	 */
	protected $objectIDs = [];
	
	/**
	 * list of object editors
	 * @var	DatabaseObjectEditor[]
	 */
	protected $objects = [];
	
	/**
	 * multi-dimensional array of parameters required by an action
	 * @var	array
	 */
	protected $parameters = [];
	
	/**
	 * list of permissions required to create objects
	 * @var	string[]
	 */
	protected $permissionsCreate = [];
	
	/**
	 * list of permissions required to delete objects
	 * @var	string[]
	 */
	protected $permissionsDelete = [];
	
	/**
	 * list of permissions required to update objects
	 * @var	string[]
	 */
	protected $permissionsUpdate = [];
	
	/**
	 * disallow requests for specified methods if the origin is not the ACP
	 * @var	string[]
	 */
	protected $requireACP = [];
	
	/**
	 * Resets cache if any of the listed actions is invoked
	 * @var	string[]
	 */
	protected $resetCache = ['create', 'delete', 'toggle', 'update', 'updatePosition'];
	
	/**
	 * values returned by executed action
	 * @var	mixed
	 */
	protected $returnValues = null;
	
	/**
	 * allows guest access for all specified methods, by default guest access
	 * is completely disabled
	 * @var	string[]
	 */
	protected $allowGuestAccess = [];
	
	const TYPE_INTEGER = 1;
	const TYPE_STRING = 2;
	const TYPE_BOOLEAN = 3;
	const TYPE_JSON = 4;
	
	const STRUCT_FLAT = 1;
	const STRUCT_ARRAY = 2;
	
	/**
	 * Initialize a new DatabaseObject-related action.
	 * 
	 * @param	mixed[]		$objects
	 * @param	string		$action
	 * @param	array		$parameters
	 * @throws	SystemException
	 */
	public function __construct(array $objects, $action, array $parameters = []) {
		// set class name
		if (empty($this->className)) {
			$className = get_called_class();
			
			if (mb_substr($className, -6) == 'Action') {
				$this->className = mb_substr($className, 0, -6).'Editor';
			}
		}
		
		$indexName = call_user_func([$this->className, 'getDatabaseTableIndexName']);
		$baseClass = call_user_func([$this->className, 'getBaseClass']);
		
		foreach ($objects as $object) {
			if (is_object($object)) {
				if ($object instanceof $this->className) {
					$this->objects[] = $object;
				}
				else if ($object instanceof $baseClass) {
					$this->objects[] = new $this->className($object);
				}
				else {
					throw new SystemException('invalid value of parameter objects given');
				}
				
				/** @noinspection PhpVariableVariableInspection */
				$this->objectIDs[] = $object->$indexName;
			}
			else {
				$this->objectIDs[] = $object;
			}
		}
		
		$this->action = $action;
		$this->parameters = $parameters;
		
		// initialize further settings
		$this->__init($baseClass, $indexName);
		
		// fire event action
		EventHandler::getInstance()->fireAction($this, 'initializeAction');
	}
	
	/**
	 * This function can be overridden in children to perform custom initialization
	 * of a DBOAction before the 'initializeAction' event is fired.
	 * 
	 * @param	string		$baseClass
	 * @param	string		$indexName
	 */
	protected function __init($baseClass, $indexName) {
		// does nothing
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateAction() {
		// validate if user is logged in
		if (!WCF::getUser()->userID && !in_array($this->getActionName(), $this->allowGuestAccess)) {
			throw new PermissionDeniedException();
		}
		else if (!RequestHandler::getInstance()->isACPRequest() && in_array($this->getActionName(), $this->requireACP)) {
			// attempt to invoke method, but origin is not the ACP
			throw new PermissionDeniedException();
		}
		
		// validate action name
		if (!method_exists($this, $this->getActionName())) {
			throw new SystemException("unknown action '".$this->getActionName()."'");
		}
		
		$actionName = 'validate'.StringUtil::firstCharToUpperCase($this->getActionName());
		if (!method_exists($this, $actionName)) {
			throw new PermissionDeniedException();
		}
		
		// validate action
		$this->{$actionName}();
		
		// fire event action
		EventHandler::getInstance()->fireAction($this, 'validateAction');
	}
	
	/**
	 * @inheritDoc
	 */
	public function executeAction() {
		// execute action
		if (!method_exists($this, $this->getActionName())) {
			throw new SystemException("call to undefined function '".$this->getActionName()."'");
		}
		
		$this->returnValues = $this->{$this->getActionName()}();
		
		// reset cache
		if (in_array($this->getActionName(), $this->resetCache)) {
			$this->resetCache();
		}
		
		// fire event action
		EventHandler::getInstance()->fireAction($this, 'finalizeAction');
		
		return $this->getReturnValues();
	}
	
	/**
	 * Resets cache of database object.
	 */
	protected function resetCache() {
		if (is_subclass_of($this->className, IEditableCachedObject::class)) {
			call_user_func([$this->className, 'resetCache']);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getActionName() {
		return $this->action;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getObjectIDs() {
		return $this->objectIDs;
	}
	
	/**
	 * Sets the database objects.
	 * 
	 * @param	DatabaseObject[]	$objects
	 */
	public function setObjects(array $objects) {
		$this->objects = $objects;
		
		// update object IDs
		$this->objectIDs = [];
		foreach ($this->getObjects() as $object) {
			$this->objectIDs[] = $object->getObjectID();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function getParameters() {
		return $this->parameters;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getReturnValues() {
		return [
			'actionName' => $this->action,
			'objectIDs' => $this->getObjectIDs(),
			'returnValues' => $this->returnValues
		];
	}
	
	/**
	 * Validates permissions and parameters.
	 */
	public function validateCreate() {
		// validate permissions
		if (is_array($this->permissionsCreate) && !empty($this->permissionsCreate)) {
			WCF::getSession()->checkPermissions($this->permissionsCreate);
		}
		else {
			throw new PermissionDeniedException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function validateDelete() {
		// validate permissions
		if (is_array($this->permissionsDelete) && !empty($this->permissionsDelete)) {
			WCF::getSession()->checkPermissions($this->permissionsDelete);
		}
		else {
			throw new PermissionDeniedException();
		}
		
		// read objects
		if (empty($this->objects)) {
			$this->readObjects();
			
			if (empty($this->objects)) {
				throw new UserInputException('objectIDs');
			}
		}
	}
	
	/**
	 * Validates permissions and parameters.
	 */
	public function validateUpdate() {
		// validate permissions
		if (is_array($this->permissionsUpdate) && !empty($this->permissionsUpdate)) {
			WCF::getSession()->checkPermissions($this->permissionsUpdate);
		}
		else {
			throw new PermissionDeniedException();
		}
		
		// read objects
		if (empty($this->objects)) {
			$this->readObjects();
			
			if (empty($this->objects)) {
				throw new UserInputException('objectIDs');
			}
		}
	}
	
	/**
	 * Creates new database object.
	 * 
	 * @return	DatabaseObject
	 */
	public function create() {
		return call_user_func([$this->className, 'create'], $this->parameters['data']);
	}
	
	/**
	 * @inheritDoc
	 */
	public function delete() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		// get ids
		$objectIDs = [];
		foreach ($this->getObjects() as $object) {
			$objectIDs[] = $object->getObjectID();
		}
		
		// execute action
		return call_user_func([$this->className, 'deleteAll'], $objectIDs);
	}
	
	/**
	 * Updates data.
	 */
	public function update() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		if (isset($this->parameters['data'])) {
			foreach ($this->getObjects() as $object) {
				$object->update($this->parameters['data']);
			}
		}
		
		if (isset($this->parameters['counters'])) {
			foreach ($this->getObjects() as $object) {
				$object->updateCounters($this->parameters['counters']);
			}
		}
	}
	
	/**
	 * Reads data by data id.
	 */
	protected function readObjects() {
		if (empty($this->objectIDs)) {
			return;
		}
		
		// get base class
		$baseClass = call_user_func([$this->className, 'getBaseClass']);
		
		// get db information
		$tableName = call_user_func([$this->className, 'getDatabaseTableName']);
		$indexName = call_user_func([$this->className, 'getDatabaseTableIndexName']);
		
		// get objects
		$sql = "SELECT	*
			FROM	".$tableName."
			WHERE	".$indexName." IN (".str_repeat('?,', count($this->objectIDs) - 1)."?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($this->objectIDs);
		while ($object = $statement->fetchObject($baseClass)) {
			$this->objects[] = new $this->className($object);
		}
	}
	
	/**
	 * Returns a single object and throws a UserInputException if no or more than one object is given.
	 * 
	 * @return	DatabaseObject
	 * @throws	UserInputException
	 */
	protected function getSingleObject() {
		if (empty($this->objects)) {
			$this->readObjects();
		}
		
		if (count($this->objects) != 1) {
			throw new UserInputException('objectIDs');
		}
		
		reset($this->objects);
		return current($this->objects);
	}
	
	/**
	 * Reads an integer value and validates it.
	 * 
	 * @param	string		$variableName
	 * @param	boolean		$allowEmpty
	 * @param	string		$arrayIndex
	 */
	protected function readInteger($variableName, $allowEmpty = false, $arrayIndex = '') {
		$this->readValue($variableName, $allowEmpty, $arrayIndex, self::TYPE_INTEGER, self::STRUCT_FLAT);
	}
	
	/**
	 * Reads an integer array and validates it.
	 * 
	 * @param	string		$variableName
	 * @param	boolean		$allowEmpty
	 * @param	string		$arrayIndex
	 * @since	3.0
	 */
	protected function readIntegerArray($variableName, $allowEmpty = false, $arrayIndex = '') {
		$this->readValue($variableName, $allowEmpty, $arrayIndex, self::TYPE_INTEGER, self::STRUCT_ARRAY);
	}
	
	/**
	 * Reads a string value and validates it.
	 * 
	 * @param	string		$variableName
	 * @param	boolean		$allowEmpty
	 * @param	string		$arrayIndex
	 */
	protected function readString($variableName, $allowEmpty = false, $arrayIndex = '') {
		$this->readValue($variableName, $allowEmpty, $arrayIndex, self::TYPE_STRING, self::STRUCT_FLAT);
	}
	
	/**
	 * Reads a string array and validates it.
	 * 
	 * @param	string		$variableName
	 * @param	boolean		$allowEmpty
	 * @param	string		$arrayIndex
	 * @since	3.0
	 */
	protected function readStringArray($variableName, $allowEmpty = false, $arrayIndex = '') {
		$this->readValue($variableName, $allowEmpty, $arrayIndex, self::TYPE_STRING, self::STRUCT_ARRAY);
	}
	
	/**
	 * Reads a boolean value and validates it.
	 * 
	 * @param	string		$variableName
	 * @param	boolean		$allowEmpty
	 * @param	string		$arrayIndex
	 */
	protected function readBoolean($variableName, $allowEmpty = false, $arrayIndex = '') {
		$this->readValue($variableName, $allowEmpty, $arrayIndex, self::TYPE_BOOLEAN, self::STRUCT_FLAT);
	}
	
	/**
	 * Reads a json-encoded value and validates it.
	 * 
	 * @param	string		$variableName
	 * @param	boolean		$allowEmpty
	 * @param	string		$arrayIndex
	 */
	protected function readJSON($variableName, $allowEmpty = false, $arrayIndex = '') {
		$this->readValue($variableName, $allowEmpty, $arrayIndex, self::TYPE_JSON, self::STRUCT_FLAT);
	}
	
	/**
	 * Reads a value and validates it. If you set $allowEmpty to true, no exception will
	 * be thrown if the variable evaluates to 0 (integer) or '' (string). Furthermore the
	 * variable will be always created with a sane value if it does not exist.
	 * 
	 * @param	string		$variableName
	 * @param	boolean		$allowEmpty
	 * @param	string		$arrayIndex
	 * @param	integer		$type
	 * @param	integer		$structure
	 * @throws	SystemException
	 * @throws	UserInputException
	 */
	protected function readValue($variableName, $allowEmpty, $arrayIndex, $type, $structure) {
		if ($arrayIndex) {
			if (!isset($this->parameters[$arrayIndex])) {
				if ($allowEmpty) {
					// Implicitly create the structure to permit implicitly defined values.
					$this->parameters[$arrayIndex] = [];
				}
				else {
					throw new SystemException("Corrupt parameters, index '" . $arrayIndex . "' is missing");
				}
			}
			
			$target =& $this->parameters[$arrayIndex];
		}
		else {
			$target =& $this->parameters;
		}
		
		switch ($type) {
			case self::TYPE_INTEGER:
				if (!isset($target[$variableName])) {
					if ($allowEmpty) {
						$target[$variableName] = ($structure === self::STRUCT_FLAT) ? 0 : [];
					}
					else {
						throw new UserInputException($variableName);
					}
				}
				else {
					if ($structure === self::STRUCT_FLAT) {
						$target[$variableName] = intval($target[$variableName]);
						if (!$allowEmpty && !$target[$variableName]) {
							throw new UserInputException($variableName);
						}
					}
					else {
						$target[$variableName] = ArrayUtil::toIntegerArray($target[$variableName]);
						if (!is_array($target[$variableName])) {
							throw new UserInputException($variableName);
						}
						
						for ($i = 0, $length = count($target[$variableName]); $i < $length; $i++) {
							if ($target[$variableName][$i] === 0) {
								throw new UserInputException($variableName);
							}
						}
					}
				}
			break;
			
			case self::TYPE_STRING:
				if (!isset($target[$variableName])) {
					if ($allowEmpty) {
						$target[$variableName] = ($structure === self::STRUCT_FLAT) ? '' : [];
					}
					else {
						throw new UserInputException($variableName);
					}
				}
				else {
					if ($structure === self::STRUCT_FLAT) {
						$target[$variableName] = StringUtil::trim($target[$variableName]);
						if (!$allowEmpty && $target[$variableName] === '') {
							throw new UserInputException($variableName);
						}
					}
					else {
						$target[$variableName] = ArrayUtil::trim($target[$variableName]);
						if (!is_array($target[$variableName])) {
							throw new UserInputException($variableName);
						}
						
						for ($i = 0, $length = count($target[$variableName]); $i < $length; $i++) {
							if ($target[$variableName][$i] === '') {
								throw new UserInputException($variableName);
							}
						}
					}
				}
			break;
			
			case self::TYPE_BOOLEAN:
				if (!isset($target[$variableName])) {
					if ($allowEmpty) {
						$target[$variableName] = false;
					}
					else {
						throw new UserInputException($variableName);
					}
				}
				else {
					if (is_numeric($target[$variableName])) {
						$target[$variableName] = (bool) $target[$variableName];
					}
					else {
						$target[$variableName] = $target[$variableName] != 'false';
					}
				}
			break;
			
			case self::TYPE_JSON:
				if (!isset($target[$variableName])) {
					if ($allowEmpty) {
						$target[$variableName] = [];
					}
					else {
						throw new UserInputException($variableName);
					}
				}
				else {
					try {
						$target[$variableName] = JSON::decode($target[$variableName]);
					}
					catch (SystemException $e) {
						throw new UserInputException($variableName);
					}
					
					if (!$allowEmpty && empty($target[$variableName])) {
						throw new UserInputException($variableName);
					}
				}
			break;
		}
	}
	
	/**
	 * Returns object class name.
	 * 
	 * @return	string
	 */
	public function getClassName() {
		return $this->className;
	}
	
	/**
	 * Returns a list of currently loaded objects.
	 * 
	 * @return	DatabaseObjectEditor[]
	 */
	public function getObjects() {
		return $this->objects;
	}
}
