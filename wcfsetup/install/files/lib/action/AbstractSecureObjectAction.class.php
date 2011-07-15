<?php
namespace wcf\action;

/**
 * Abstract handler for object-actions using the AJAX-API.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	action
 * @category 	Community Framework
 */
abstract class AbstractSecureObjectAction extends AbstractAction {
	protected $action = '';
	protected $actionClass = '';
	protected $actionData = array();
	protected $data = array();
	protected $objectAction = null;
	protected $objectIDs = array();
		
	/**
	 * @see	wcf\action\Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_POST['actionClass'])) {
			$this->actionClass = StringUtil::trim($_POST['actionClass']);
		}
		if (isset($_POST['data'])) {
			$data = json_decode($_POST['data'], true);
			if (is_array($data)) $this->data = $data;
		}
		if (isset($_POST['objectIDs'])) {
			$objectIDs = json_decode($_POST['objectIDs']);
			if (is_array($objectIDs)) $this->objectIDs = ArrayUtil::toIntegerArray($objectIDs);
		}
	}
	
	/**
	 * @see		wcf\action\Action::execute()
	 * @todo	Add validation for $actionClass, $data and $objectIDs,
	 * 		possibly with some kind of derived exception maintaining
	 * 		a js-readable output (do not use printable exception!)
	 */
	public function execute() {
		parent::execute();
		
		$className = $this->actionClass.'Action';
		$classPath = $this->getClassPath().$this->actionClass.'Action.class.php';
		
		require_once($classPath);
		$this->objectAction = new $className($this->objectIDs, $this->action, $this->actionData);
	}
	
	/**
	 * Executes chosen action. This method is not called automatically,
	 * you must call this method in any derived class.
	 */
	protected function executeAction() {
		$this->objectAction->validateAction();
		$this->objectAction->executeAction();
		
		$this->handleResult();
	}
	
	/**
	 * Returns class path based upon object action's name (excluding Action-suffix)
	 * 
	 * @return	string
	 */
	protected function getClassPath() {
		$directories = array();
		$components = preg_split('~(?<=[a-z])(?=[A-Z])~', $this->actionClass);
		
		foreach ($components as $part) {
			$directories[] = StringUtil::toLowerCase($part);
		}
		
		$path = WCF_DIR . 'lib/data/' . implode('/', $directories);
		return FileUtil::addTrailingSlash($path);
	}
	
	/**
	 * Handles action result, derived classes must implement this but leave it empty.
	 */
	abstract protected function handleResult();
}
