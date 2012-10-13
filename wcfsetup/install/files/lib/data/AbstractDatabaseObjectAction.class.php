<?php
namespace wcf\data;
use wcf\system\event\EventHandler;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\exception\ValidateActionException;
use wcf\system\WCF;
use wcf\util\ClassUtil;
use wcf\util\StringUtil;

/**
 * Default implementation for DatabaseObject-related actions.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data
 * @category 	Community Framework
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
	 * @var	array<integer>
	 */
	protected $objectIDs = array();
	
	/**
	 * list of object editors
	 * @var	array<wcf\data\DatabaseObjectEditor>
	 */
	protected $objects = array();
	
	/**
	 * multi-dimensional array of parameters required by an action
	 * @var	array<array>
	 */
	protected $parameters = array();
	
	/**
	 * list of permissions required to create objects
	 * @var	array<string>
	 */
	protected $permissionsCreate = array();
	
	/**
	 * list of permissions required to delete objects
	 * @var	array<string>
	 */
	protected $permissionsDelete = array();
	
	/**
	 * list of permissions required to update objects
	 * @var	array<string>
	 */
	protected $permissionsUpdate = array();
	
	/**
	 * values returned by executed action
	 * @var	mixed
	 */
	protected $returnValues = null;
	
	/**
	 * allows guest access for all specified methods, by default
	 * guest access is completely disabled
	 * @var	array<string>
	 */
	protected $allowGuestAccess = array();
	
	/**
	 * Initialize a new DatabaseObject-related action.
	 *
	 * @param	array<mixed>	$objects
	 * @param	string		$action
	 * @param	array		$parameters
	 */
	public function __construct(array $objects, $action, array $parameters = array()) {
		// set class name
		if (empty($this->className)) {
			$className = get_called_class();
			
			if (StringUtil::substring($className, -6) == 'Action') {
				$this->className = StringUtil::substring($className, 0, -6).'Editor';
			}
		}
		
		$indexName = call_user_func(array($this->className, 'getDatabaseTableIndexName'));
		$baseClass = call_user_func(array($this->className, 'getBaseClass'));
		
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
				
				$this->objectIDs[] = $object->$indexName;
			}
			else {
				$this->objectIDs[] = $object;
			}
		}
		
		$this->action = $action;
		$this->parameters = $parameters;
		
		// fire event action
		EventHandler::getInstance()->fireAction($this, 'initializeAction');
	}
	
	/**
	 * @see	wcf\data\IDatabaseObjectAction::validateAction()
	 */
	public function validateAction() {
		// validate if user is logged in
		if (!WCF::getUser()->userID && !in_array($this->getActionName(), $this->allowGuestAccess)) {
			throw new ValidateActionException("Please login before executing this action");
		}
		
		// validate action name
		if (!method_exists($this, $this->getActionName())) {
			throw new ValidateActionException("unknown action '".$this->getActionName()."'");
		}
		
		$actionName = 'validate'.StringUtil::firstCharToUpperCase($this->getActionName());
		if (!method_exists($this, $actionName)) {
			throw new ValidateActionException("validation of action '".$this->getActionName()."' failed");
		}
		
		// execute action
		call_user_func_array(array($this, $actionName), $this->getParameters());
	}
	
	/**
	 * @see	wcf\data\IDatabaseObjectAction::executeAction()
	 */
	public function executeAction() {
		// execute action
		if (method_exists($this, $this->getActionName())) {
			$this->returnValues = call_user_func_array(array($this, $this->getActionName()), $this->getParameters());
		}
		
		// reset cache
		if (ClassUtil::isInstanceOf($this->className, 'wcf\data\IEditableCachedObject')) {
			call_user_func(array($this->className, 'resetCache'));
		}
		
		// fire event action
		EventHandler::getInstance()->fireAction($this, 'finalizeAction');
		
		return $this->getReturnValues();
	}
	
	/**
	 * @see	wcf\data\IDatabaseObjectAction::getActionName()
	 */
	public function getActionName() {
		return $this->action;
	}
	
	/**
	 * @see	wcf\data\IDatabaseObjectAction::getObjectIDs()
	 */
	public function getObjectIDs() {
		return $this->objectIDs;
	}
	
	/**
	 * Sets the database objects.
	 * 
	 * @param	array<wcf\data\DatabaseObject>		$objects
	 */
	public function setObjects(array $objects) {
		$this->objects = $objects;
	}
	
	/**
	 * @see	wcf\data\IDatabaseObjectAction::getParameters()
	 */
	public function getParameters() {
		return $this->parameters;
	}
	
	/**
	 * @see	wcf\data\IDatabaseObjectAction::getReturnValues()
	 */
	public function getReturnValues() {
		return array(
			'objectIDs' => $this->getObjectIDs(),
			'returnValues' => $this->returnValues
		);
	}
	
	/**
	 * Validates permissions and parameters.
	 */
	public function validateCreate() {
		// validate permissions
		if (is_array($this->permissionsCreate) && count($this->permissionsCreate)) {
			try {
				WCF::getSession()->checkPermissions($this->permissionsCreate);
			}
			catch (PermissionDeniedException $e) {
				throw new ValidateActionException('Insufficient permissions');
			}
		}
		else {
			throw new ValidateActionException('Insufficient permissions');
		}
	}
	
	/**
	 * @see	wcf\data\IDeleteAction::validateDelete()
	 */
	public function validateDelete() {
		// validate permissions
		if (is_array($this->permissionsDelete) && count($this->permissionsDelete)) {
			try {
				WCF::getSession()->checkPermissions($this->permissionsDelete);
			}
			catch (PermissionDeniedException $e) {
				throw new ValidateActionException('Insufficient permissions');
			}
		}
		else {
			throw new ValidateActionException('Insufficient permissions');
		}
		
		// read objects
		if (!count($this->objects)) {
			$this->readObjects();
		}
		
		if (!count($this->objects)) {
			throw new ValidateActionException('Invalid object id');
		}
	}
	
	/**
	 * Validates permissions and parameters.
	 */
	public function validateUpdate() {
		// validate permissions
		if (is_array($this->permissionsUpdate) && count($this->permissionsUpdate)) {
			try {
				WCF::getSession()->checkPermissions($this->permissionsUpdate);
			}
			catch (PermissionDeniedException $e) {
				throw new ValidateActionException('Insufficient permissions');
			}
		}
		else {
			throw new ValidateActionException('Insufficient permissions');
		}
		
		// read objects
		if (!count($this->objects)) {
			$this->readObjects();
		}
		
		if (!count($this->objects)) {
			throw new ValidateActionException('Invalid object id');
		}
	}
	
	/**
	 * Creates new database object.
	 *
	 * @return	wcf\data\DatabaseObject
	 */
	public function create() {
		return call_user_func(array($this->className, 'create'), $this->parameters['data']);
	}
	
	/**
	 * @see	wcf\data\IDeleteAction::delete()
	 */
	public function delete() {
		if (!count($this->objects)) {
			$this->readObjects();
		}
		
		// get index name
		$indexName = call_user_func(array($this->className, 'getDatabaseTableIndexName'));
		
		// get ids
		$objectIDs = array();
		foreach ($this->objects as $object) {
			$objectIDs[] = $object->__get($indexName);
		}
		
		// execute action
		return call_user_func(array($this->className, 'deleteAll'), $objectIDs);
	}
	
	/**
	 * Updates data.
	 */
	public function update() {
		if (!count($this->objects)) {
			$this->readObjects();
		}
		
		if (isset($this->parameters['data'])) {
			foreach ($this->objects as $object) {
				$object->update($this->parameters['data']);
			}
		}
	}
	
	/**
	 * Reads data by data id.
	 */
	protected function readObjects() {
		if (!count($this->objectIDs)) {
			return;
		}
		
		// get base class
		$baseClass = call_user_func(array($this->className, 'getBaseClass'));
		
		// get db information
		$tableName = call_user_func(array($this->className, 'getDatabaseTableName'));
		$indexName = call_user_func(array($this->className, 'getDatabaseTableIndexName'));
		
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
	 * @return	array<wcf\data\IEditableObject>
	 */
	public function getObjects() {
		return $this->objects;
	}
}
