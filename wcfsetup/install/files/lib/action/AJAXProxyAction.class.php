<?php
namespace wcf\action;
use wcf\system\exception\AJAXException;
use wcf\system\exception\ValidateActionException;
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
	 * @var string
	 */
	protected $className = '';
	
	/**
	 * action name
	 * @var string
	 */
	protected $actionName = '';
	
	/**
	 * list of object ids
	 * @var array<integer>
	 */
	protected $objectIDs = array();
	
	/**
	 * additional parameters
	 * @var array<mixed>
	 */
	protected $parameters = array();
	
	/**
	 * object action
	 * @var DatabaseObjectAction
	 */
	protected $objectAction = null;
	
	/**
	 * results of the executed action
	 * @var mixed
	 */
	protected $response = null;
	
	/**
	 * @see	AbstractAction::_construct()
	 */
	public function __construct() {
		try {
			parent::__construct();
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
	 * @see	Action::readParameters()
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
			if (is_array($_POST['parameters'])) $this->parameters = $parameters;
		}
	}
	
	/**
	 * @see	Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		// validate class name
		if (!class_exists($this->className)) {
			throw new AJAXException("unknown class '".$this->className."'");
		}
		if (!ClassUtil::isInstanceOf($this->className, 'wcf\data\DatabaseObjectAction')) {
			throw new AJAXException("'".$this->className."' should implement DatabaseObjectAction");
		}
		
		// create object action instance
		$this->objectAction = new $this->className($this->objectIDs, $this->actionName, $this->parameters);
		
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
?>