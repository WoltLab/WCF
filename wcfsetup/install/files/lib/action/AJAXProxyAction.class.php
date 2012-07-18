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
	 * class name
	 * @var	string
	 */
	protected $className = '';
	
	/**
	 * action name
	 * @var	string
	 */
	protected $actionName = '';
	
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
	 * object action
	 * @var	wcf\data\IDatabaseObjectAction
	 */
	protected $objectAction = null;
	
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
		
		if (isset($_POST['className'])) {
			$this->className = StringUtil::trim($_POST['className']);
		}
		if (isset($_POST['actionName'])) {
			$this->actionName = StringUtil::trim($_POST['actionName']);
		}
		if (isset($_POST['objectIDs'])) {
			if (is_array($_POST['objectIDs'])) $this->objectIDs = ArrayUtil::toIntegerArray($_POST['objectIDs']);
		}
		if (isset($_POST['parameters'])) {
			if (is_array($_POST['parameters'])) $this->parameters = $_POST['parameters'];
		}
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
			throw new SystemException("'".$this->className."' should implement wcf\system\IDatabaseObjectAction");
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
			if ($this->response instanceof \wcf\data\IStorableObject) {
				$this->reponse = $this->response->getData();
			}
		}
		catch (\Exception $e) {
			$this->throwException($e);
		}
		$this->executed();
		
		// send JSON-encoded response
		header('Content-type: application/json');
		echo JSON::encode($this->response);
		exit;
	}
	
	/**
	 * Throws an previously catched exception while maintaing the propriate stacktrace.
	 * 
	 * @param	\Exception	$e
	 */
	protected function throwException(\Exception $e) {
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
			throw new AJAXException($e->getMessage(), AJAXException::BAD_PARAMETERS);
		}
		else {
			throw new AJAXException($e->getMessage(), AJAXException::INTERNAL_ERROR, $e->getTraceAsString());
		}
	}
}
