<?php
namespace wcf\action;
use wcf\system\api\rest\response\IRESTfulResponse;
use wcf\system\exception\AJAXException;
use wcf\system\exception\IllegalLinkException;
use wcf\system\request\RouteHandler;
use wcf\system\WCF;

/**
 * This action provides RESTful access to database objects.
 * 
 * @author	Jeffrey Reichardt
 * @copyright	2001-2012 WoltLab GmbH
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
		
		$this->data = $this->prune($object);
		
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
	 * @return array
	 */
	protected function prune(IRESTfulResponse $object) {
		$prunedArray = array();
		
		foreach ($object->getResponseFields() as $fieldName) {
			if ($object->$fieldName) {
				$prunedArray[$fieldName] = $object->$fieldName;
			}
		}
		
		return $prunedArray;
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
