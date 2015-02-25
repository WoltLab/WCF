<?php
namespace wcf\action;
use wcf\system\clipboard\ClipboardHandler;
use wcf\system\exception\AJAXException;
use wcf\system\exception\ValidateActionException;
use wcf\util\ClassUtil;
use wcf\util\JSON;
use wcf\util\StringUtil;

/**
 * Clipboard proxy implementation.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	action
 * @category	Community Framework
 */
class ClipboardProxyAction extends AbstractSecureAction {
	/**
	 * IDatabaseObjectAction object
	 * @var	\wcf\data\IDatabaseObjectAction
	 */
	protected $objectAction = null;
	
	/**
	 * list of parameters
	 * @var	array
	 */
	protected $parameters = array();
	
	/**
	 * type name identifier
	 * @var	string
	 */
	protected $typeName = '';
	
	/**
	 * @see	\wcf\action\IAction::__run()
	 */
	public function __run() {
		try {
			parent::__run();
		}
		catch (\Exception $e) {
			if ($e instanceof AJAXException) {
				throw $e;
			}
			else {
				throw new AJAXException($e->getMessage());
			}
		}
	}
	
	/**
	 * @see	\wcf\action\IAction::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_POST['parameters']) && is_array($_POST['parameters'])) $this->parameters = $_POST['parameters'];
		if (isset($_POST['typeName'])) $this->typeName = StringUtil::trim($_POST['typeName']);
	}
	
	/**
	 * Validates parameters.
	 */
	protected function validate() {
		// validate required parameters
		if (!isset($this->parameters['className']) || empty($this->parameters['className'])) {
			throw new AJAXException("missing class name");
		}
		if (!isset($this->parameters['actionName']) || empty($this->parameters['actionName'])) {
			throw new AJAXException("missing action name");
		}
		if (empty($this->typeName)) {
			throw new AJAXException("type name cannot be empty");
		}
		
		// validate class name
		if (!class_exists($this->parameters['className'])) {
			throw new AJAXException("unknown class '".$this->parameters['className']."'");
		}
		if (!ClassUtil::isInstanceOf($this->parameters['className'], 'wcf\data\IDatabaseObjectAction')) {
			throw new AJAXException("'".$this->parameters['className']."' should implement wcf\system\IDatabaseObjectAction");
		}
	}
	
	/**
	 * Loads object ids from clipboard.
	 * 
	 * @return	array<integer>
	 */
	protected function getObjectIDs() {
		$typeID = ClipboardHandler::getInstance()->getObjectTypeID($this->typeName);
		if ($typeID === null) {
			throw new AJAXException("clipboard item type '".$this->typeName."' is unknown");
		}
		
		$objects = ClipboardHandler::getInstance()->getMarkedItems($typeID);
		if (empty($objects) || !isset($objects[$this->typeName]) || empty($objects[$this->typeName])) {
			return null;
		}
		
		return array_keys($objects[$this->typeName]);
	}
	
	/**
	 * @see	\wcf\action\IAction::execute()
	 */
	public function execute() {
		parent::execute();
		
		// get object ids
		$objectIDs = $this->getObjectIDs();
		
		// create object action instance
		$this->objectAction = new $this->parameters['className']($objectIDs, $this->parameters['actionName']);
		
		// validate action
		try {
			$this->objectAction->validateAction();
		}
		catch (ValidateActionException $e) {
			throw new AJAXException("validation failed: ".$e->getMessage());
		}
		
		// execute action
		try {
			$this->response = $this->objectAction->executeAction();
		}
		catch (\Exception $e) {
			throw new AJAXException('unknown exception caught: '.$e->getMessage());
		}
		$this->executed();
		
		// send JSON-encoded response
		header('Content-type: application/json');
		echo JSON::encode($this->response);
		exit;
	}
}
