<?php
namespace wcf\action;
use wcf\data\DatabaseObject;
use wcf\system\api\rest\response\IRESTfulResponse;
use wcf\system\event\EventHandler;
use wcf\system\exception\AJAXException;
use wcf\system\exception\IllegalLinkException;
use wcf\system\request\RouteHandler;

/**
 * This action provides RESTful access to database objects.
 * 
 * @author	Jeffrey Reichardt
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	action
 * @category	Community Framework
 */
final class APIAction extends AbstractAjaxAction {
	/**
	 * needed modules to execute this action
	 * @var	array<string>
	 */
	public $neededModules = array('MODULE_API_ACCESS');
	
	/**
	 * Holds json data
	 * @var	array<mixed>
	 */
	public $data = array();
	
	/**
	 * Holds additional response fields by controller
	 * @var	array<string>
	 */
	public $additionalFields = array();
	
	/**
	 * @see	wcf\action\IAction::execute()
	 */
	public function execute() {
		parent::execute();
		
		$routeData = RouteHandler::getInstance()->getRouteData();
		
		if (!isset($routeData['className']) || !isset($routeData['id'])) {
			throw new IllegalLinkException();
		}
		
		// validate class name
		if (!preg_match('~^[a-z0-9_]+$~i', $routeData['className'])) {
			throw new AJAXException("Illegal class name '".$routeData['className']."'");
		}
		
		//get class data
		$classData = $this->getClassData($routeData['className']);
		
		if ($classData === null) {
			throw new AJAXException("unable to find class for controller '".$routeData['className']."'");
		}
		else if (!class_exists($classData['className'])) {
			throw new AJAXException("unable to find class '".$classData['className']."'");
		}
		
		//create object
		$object = new $classData['className']($routeData['id']);
		
		if (!$object || !($object instanceof IRESTfulResponse)) {
			throw new AJAXException("unable to create object of '".$routeData['className']."'");
		}
		
		// fire event for extending additionalFields
		EventHandler::getInstance()->fireAction($this, 'beforePrune');
		
		$this->data = $this->prune($object, $this->data);
		
		if (empty($this->data)) {
			throw new AJAXException("no results");
		}
		
		$this->executed();
	}
	
	/**
	 * @see	wcf\action\AbstractAction::executed()
	 */
	protected function executed() {
		$this->sendJsonResponse($this->data);
	}
	
	/**
	 * Checks fields, prunes given array and returns it
	 * 
	 * @param	wcf\system\api\rest\IRESTfulResponse	$object
	 * @param	array<mixed>	$data
	 * @param	array<wcf\data\DatabaseObject>	$traversedObjects
	 * @return	array<mixed>
	 */
	protected function prune(IRESTfulResponse $object, $data = array(), &$traversedObjects = array()) {
		// avoid self-recursion
		foreach ($traversedObjects as $key => $traversedObject) {
			if (DatabaseObject::compare($object, $traversedObject))
				return $data;
		}
		
		$traversedObjects[] = $object;
		
		foreach (array_merge($object->getResponseFields(), $this->getAdditionalFields(get_class($object))) as $fieldName) {
			if (isset($object->$fieldName)) {
				if (is_object($object->$fieldName) && ($object->$fieldName instanceof IRESTfulResponse)) {
					$data[$fieldName] = array();
					$data[$fieldName] = $this->prune($object->$fieldName, $data[$fieldName], $traversedObjects);
				} 
				else if (is_array($object->$fieldName)) {
					$data[$fieldName] = array();
					
					foreach ($object->$fieldName as $key => $value) {
						if (is_object($value) && ($value instanceof IRESTfulResponse)) {
							$data[$fieldName][$key] = array();
							$data[$fieldName][$key] = $this->prune($value, $data[$fieldName][$key], $traversedObjects);
						} 
						else {
							$data[$fieldName][$key] = $value;
						}
					}
				} 
				else {
					$data[$fieldName] = $object->$fieldName;
				}
			}
		}

		return $data;
	}
	
	/**
	 * Returns the additional fields for the given class.
	 *
	 * @param	string	$className
	 * @return	array<mixed>
	 */
	protected function getAdditionalFields($className) {
		if (isset($this->additionalFields[$className])) {
			return $this->additionalFields[$className];
		}
		
		return array();
	}
	
	/**
	 * Tries to find class and returns class name and controller.
	 *
	 * @return array<string>
	 */
	protected function getClassData($controller, $application = 'wcf') {
		$className = $application.'\\data\\'.$controller.'\\'.ucfirst($controller);
		if ($application != 'wcf' && !class_exists($className)) {
			$className = 'wcf\\data\\'.$controller.'\\'.ucfirst($controller);
		}
		if (!class_exists($className)) {
			return null;
		}
		
		// check whether the class is abstract
		$reflectionClass = new \ReflectionClass($className);
		if ($reflectionClass->isAbstract()) {
			return null;
		}
		
		return array(
			'className' => $className,
			'controller' => $controller
		);
	}
}
