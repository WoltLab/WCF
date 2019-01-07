<?php
namespace wcf\action;
use wcf\system\exception\AJAXException;
use wcf\system\exception\ImplementationException;
use wcf\system\exception\ParentClassException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\IAJAXInvokeAction;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\JSON;
use wcf\util\StringUtil;

/**
 * Default implementation for AJAX-based method calls.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Action
 */
class AJAXInvokeAction extends AbstractSecureAction {
	use TAJAXException;
	
	/**
	 * method name
	 * @var	string
	 */
	public $actionName = '';
	
	/**
	 * action object
	 * @var	SingletonFactory
	 */
	public $actionObject;
	
	/**
	 * class name
	 * @var	string
	 */
	public $className = '';
	
	/**
	 * enables debug mode
	 * @var	boolean
	 */
	public $inDebugMode = false;
	
	/**
	 * results of the executed action
	 * @var	mixed
	 */
	protected $response;
	
	/**
	 * @inheritDoc
	 */
	public function __run() {
		try {
			parent::__run();
		}
		catch (\Throwable $e) {
			if ($e instanceof AJAXException) {
				throw $e;
			}
			else {
				$this->throwException($e);
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_POST['actionName'])) $this->actionName = StringUtil::trim($_POST['actionName']);
		if (isset($_POST['className'])) $this->className = StringUtil::trim($_POST['className']);
		if (empty($this->className) || !class_exists($this->className)) {
			throw new UserInputException('className');
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		parent::execute();
		
		// execute action
		try {
			$this->invoke();
		}
		catch (\Throwable $e) {
			$this->throwException($e);
		}
		$this->executed();
		
		// send JSON-encoded response
		if (!$this->inDebugMode) {
			$this->sendResponse();
		}
	}
	
	/**
	 * Invokes action method.
	 */
	protected function invoke() {
		// check for interface and inheritance of SingletonFactory
		if (!is_subclass_of($this->className, IAJAXInvokeAction::class)) {
			throw new ImplementationException($this->className, IAJAXInvokeAction::class);
		}
		else if (!is_subclass_of($this->className, SingletonFactory::class)) {
			throw new ParentClassException($this->className, SingletonFactory::class);
		}
		
		// validate action name
		if (empty($this->actionName)) {
			throw new UserInputException('actionName');
		}
		
		// validate accessibility
		$className = $this->className;
		/** @noinspection PhpUndefinedFieldInspection */
		if (!property_exists($className, 'allowInvoke') || !in_array($this->actionName, $className::$allowInvoke)) {
			throw new PermissionDeniedException();
		}
		
		$this->actionObject = call_user_func([$this->className, 'getInstance']);
		
		// check for validate method
		$validateMethod = 'validate'.ucfirst($this->actionName);
		if (method_exists($this->actionObject, $validateMethod)) {
			$this->actionObject->{$validateMethod}();
		}
		
		$this->response = $this->actionObject->{$this->actionName}();
	}
	
	/**
	 * Sends JSON-Encoded response.
	 */
	protected function sendResponse() {
		header('Content-type: application/json');
		echo JSON::encode($this->response);
		exit;
	}
	
	/**
	 * Returns action response.
	 * 
	 * @return	mixed
	 */
	public function getResponse() {
		return $this->response;
	}
	
	/**
	 * Enables debug mode.
	 */
	public function enableDebugMode() {
		$this->inDebugMode = true;
	}
	
	/**
	 * Performs a debug call to AJAXInvokeAction, allowing testing without relying on JavaScript.
	 * The $data-array should be build like within WCF.Action.Proxy, look below for an example:
	 * 
	 * $data = array(
	 * 	'actionName' => 'foo',
	 * 	'className' => 'wcf\foo\bar\FooBarAction',
	 * 	'objectIDs' => array(1, 2, 3, 4, 5), // optional
	 * 	'parameters' => array( // optional
	 * 		'foo' => 'bar',
	 * 		'data' => array(
	 * 			'baz' => 'foobar'
	 * 		)
	 * 	)
	 * )
	 * 
	 * @param	array		$data
	 * @return	AJAXInvokeAction
	 * @throws	SystemException
	 */
	public static function debugCall(array $data) {
		// validate $data array
		if (!isset($data['actionName'])) {
			throw new SystemException("Could not execute debug call, 'actionName' is missing.");
		}
		else if (!isset($data['className'])) {
			throw new SystemException("Could not execute debug call, 'className' is missing.");
		}
		
		// set security token
		$_REQUEST['t'] = WCF::getSession()->getSecurityToken();
		
		// save $_POST variables
		$postVars = $_POST;
		
		// fake request
		foreach ($data as $key => $value) {
			$_POST[$key] = $value;
		}
		
		// execute request
		$className = get_called_class();
		
		/** @var AJAXInvokeAction $actionObject */
		$actionObject = new $className();
		$actionObject->enableDebugMode();
		$actionObject->__run();
		
		// restore $_POST variables
		$_POST = $postVars;
		
		return $actionObject;
	}
}
