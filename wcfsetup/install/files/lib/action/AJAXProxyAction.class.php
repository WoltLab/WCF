<?php
namespace wcf\action;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\AJAXException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\exception\ValidateActionException;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\ClassUtil;
use wcf\util\JSON;
use wcf\util\StringUtil;

/**
 * Default implementation for object-actions using the AJAX-API.
 *
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	action
 * @category 	Community Framework
 */
class AJAXProxyAction extends AbstractSecureAction {
	/**
	 * action name
	 * @var	string
	 */
	protected $actionName = '';
	
	/**
	 * class name
	 * @var	string
	 */
	protected $className = '';
	
	/**
	 * interface name
	 * @var	string
	 */
	protected $interfaceName = '';
	
	/**
	 * debug mode
	 * @var	boolean
	 */
	protected $inDebugMode = false;
	
	/**
	 * object action
	 * @var	wcf\data\IDatabaseObjectAction
	 */
	protected $objectAction = null;
	
	/**
	 * list of object ids
	 * @var	array<integer>
	 */
	protected $objectIDs = array();
	
	/**
	 * additional parameters
	 * @var	array<mixed>
	 */
	protected $parameters = array();
	
	/**
	 * results of the executed action
	 * @var	mixed
	 */
	protected $response = null;
	
	/**
	 * @see	wcf\action\IAction::__run()
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
	}
	
	/**
	 * @see	wcf\action\IAction::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_POST['actionName'])) $this->actionName = StringUtil::trim($_POST['actionName']);
		if (isset($_POST['className'])) $this->className = StringUtil::trim($_POST['className']);
		if (isset($_POST['interfaceName'])) $this->interfaceName = StringUtil::trim($_POST['interfaceName']);
		if (isset($_POST['objectIDs']) && is_array($_POST['objectIDs'])) $this->objectIDs = ArrayUtil::toIntegerArray($_POST['objectIDs']);
		if (isset($_POST['parameters']) && is_array($_POST['parameters'])) $this->parameters = $_POST['parameters'];
	}
	
	/**
	 * @see	wcf\action\IAction::execute()
	 */
	public function execute() {
		parent::execute();
		
		// validate class name
		if (!class_exists($this->className)) {
			throw new SystemException("unknown class '".$this->className."'");
		}
		if (!ClassUtil::isInstanceOf($this->className, 'wcf\data\IDatabaseObjectAction')) {
			throw new SystemException("'".$this->className."' should implement 'wcf\system\IDatabaseObjectAction'");
		}
		
		if (!empty($this->interfaceName)) {
			if (!ClassUtil::isInstanceOf($this->className, $this->interfaceName)) {
				throw new SystemException("'".$this->className."' should implement '".$this->interfaceName."'");
			}
		}
		
		// create object action instance
		$this->objectAction = new $this->className($this->objectIDs, $this->actionName, $this->parameters);
		
		// validate action
		try {
			$this->objectAction->validateAction();
		}
		catch (UserInputException $e) {
			$this->throwException($e);
		}
		catch (ValidateActionException $e) {
			$this->throwException($e);
		}
		
		// execute action
		try {
			$this->response = $this->objectAction->executeAction();
		}
		catch (\Exception $e) {
			$this->throwException($e);
		}
		$this->executed();
		
		// send JSON-encoded response
		if (!$this->inDebugMode) {
			header('Content-type: application/json');
			echo JSON::encode($this->response);
			exit;
		}
	}
	
	/**
	 * Throws an previously catched exception while maintaing the propriate stacktrace.
	 * 
	 * @param	\Exception	$e
	 */
	protected function throwException(\Exception $e) {
		if ($this->inDebugMode) {
			throw $e;
		}
		
		if ($e instanceof IllegalLinkException) {
			throw new AJAXException(WCF::getLanguage()->get('wcf.global.ajax.error.sessionExpired'), AJAXException::SESSION_EXPIRED);
		}
		else if ($e instanceof PermissionDeniedException) {
			throw new AJAXException(WCF::getLanguage()->get('wcf.global.ajax.error.permissionDenied'), AJAXException::INSUFFICIENT_PERMISSIONS);
		}
		else if ($e instanceof SystemException) {
			throw new AJAXException($e->getMessage(), AJAXException::INTERNAL_ERROR, $e->__getTraceAsString());
		}
		else if ($e instanceof UserInputException) {
			throw new AJAXException($e->getMessage(), AJAXException::BAD_PARAMETERS, $e->getTraceAsString());
		}
		else {
			throw new AJAXException($e->getMessage(), AJAXException::INTERNAL_ERROR, $e->getTraceAsString());
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
	 * Performs a debug call to AJAXProxyAction, allowing testing without relying on JavaScript.
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
	 * @param	string		$className
	 * @param	string		$actionName
	 * @return	wcf\action\AJAXProxyAction
	 */
	public static function debugCall(array $data) {
		// validate $data array
		if (!isset($data['actionName'])) {
			throw new SystemException("Could not execute debug call, 'actionName' is missing.");
		}
		else if (!isset($data['className'])) {
			throw new SystemException("Could not execute debug call, 'className' is missing.");
		}
		
		// save $_POST variables
		$postVars = $_POST;
		
		// fake request
		$_POST['actionName'] = $data['actionName'];
		$_POST['className'] = $data['className'];
		if (isset($data['objectIDs'])) {
			$_POST['objectIDs'] = $data['objectIDs'];
		}
		if (isset($data['parameters'])) {
			$_POST['parameters'] = $data['parameters'];
		}
		
		// execute request
		$actionObject = new AJAXProxyAction();
		$actionObject->enableDebugMode();
		$actionObject->__run();
		
		// restore $_POST variables
		$_POST = $postVars;
		
		return $actionObject;
	}
}
