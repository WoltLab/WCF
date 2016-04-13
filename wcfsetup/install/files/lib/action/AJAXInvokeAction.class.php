<?php
namespace wcf\action;
use wcf\system\exception\AJAXException;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\InvalidSecurityTokenException;
use wcf\system\exception\LoggedException;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\exception\ValidateActionException;
use wcf\system\WCF;
use wcf\util\JSON;
use wcf\util\StringUtil;

/**
 * Default implementation for AJAX-based method calls.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	action
 * @category	Community Framework
 */
class AJAXInvokeAction extends AbstractSecureAction {
	/**
	 * method name
	 * @var	string
	 */
	public $actionName = '';
	
	/**
	 * action object
	 * @var	\wcf\system\SingletonFactory
	 */
	public $actionObject = null;
	
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
	protected $response = null;
	
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
				$this->throwException($e);
			}
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
	 * @see	\wcf\action\IAction::readParameters()
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
	 * @see	\wcf\action\IAction::execute()
	 */
	public function execute() {
		parent::execute();
		
		// execute action
		try {
			$this->invoke();
		}
		catch (\Exception $e) {
			$this->throwException($e);
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
		if (!is_subclass_of($this->className, 'wcf\system\IAJAXInvokeAction')) {
			throw new SystemException("'".$this->className."' does not implement 'wcf\system\IAJAXInvokeAction'");
		}
		else if (!is_subclass_of($this->className, 'wcf\system\SingletonFactory')) {
			throw new SystemException("'".$this->className."' does not extend 'wcf\system\SingletonFactory'");
		}
		
		// validate action name
		if (empty($this->actionName)) {
			throw new UserInputException('actionName');
		}
		
		// validate accessibility
		$className = $this->className;
		if (!property_exists($className, 'allowInvoke') || !in_array($this->actionName, $className::$allowInvoke)) {
			throw new PermissionDeniedException();
		}
		
		$this->actionObject = call_user_func(array($this->className, 'getInstance'));
		
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
	 * Throws an previously catched exception while maintaing the propriate stacktrace.
	 * 
	 * @param	\Exception|\Throwable	$e
	 * @throws	AJAXException
	 * @throws	\Exception
	 */
	protected function throwException($e) {
		if ($this->inDebugMode) {
			throw $e;
		}

		if ($e instanceof InvalidSecurityTokenException) {
			throw new AJAXException(WCF::getLanguage()->get('wcf.ajax.error.sessionExpired'), AJAXException::SESSION_EXPIRED, $e->getTraceAsString());
		}
		else if ($e instanceof PermissionDeniedException) {
			throw new AJAXException(WCF::getLanguage()->get('wcf.ajax.error.permissionDenied'), AJAXException::INSUFFICIENT_PERMISSIONS, $e->getTraceAsString());
		}
		else if ($e instanceof IllegalLinkException) {
			throw new AJAXException(WCF::getLanguage()->get('wcf.ajax.error.illegalLink'), AJAXException::ILLEGAL_LINK, $e->getTraceAsString());
		}
		else if ($e instanceof UserInputException) {
			// repackage as ValidationActionException
			$exception = new ValidateActionException($e->getField(), $e->getType(), $e->getVariables());
			throw new AJAXException($exception->getMessage(), AJAXException::BAD_PARAMETERS, $e->getTraceAsString(), array(
				'errorMessage' => $exception->getMessage(),
				'errorType' => $e->getType(),
				'fieldName' => $exception->getFieldName(),
			));
		}
		else if ($e instanceof ValidateActionException) {
			throw new AJAXException($e->getMessage(), AJAXException::BAD_PARAMETERS, $e->getTraceAsString(), array(
				'errorMessage' => $e->getMessage(),
				'fieldName' => $e->getFieldName()
			));
		}
		else if ($e instanceof NamedUserException) {
			throw new AJAXException($e->getMessage(), AJAXException::BAD_PARAMETERS, $e->getTraceAsString());
		}
		else {
			throw new AJAXException($e->getMessage(), AJAXException::INTERNAL_ERROR, $e->getTraceAsString(), array(), \wcf\functions\exception\logThrowable($e));
		}
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
		$actionObject = new $className();
		$actionObject->enableDebugMode();
		$actionObject->__run();
		
		// restore $_POST variables
		$_POST = $postVars;
		
		return $actionObject;
	}
}
