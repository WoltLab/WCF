<?php
namespace wcf\action;
use wcf\system\exception\SystemException;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Default implementation for object-actions using the AJAX-API.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	action
 * @category	Community Framework
 */
class AJAXProxyAction extends AJAXInvokeAction {
	/**
	 * interface name
	 * @var	string
	 */
	protected $interfaceName = '';
	
	/**
	 * object action
	 * @var	\wcf\data\IDatabaseObjectAction
	 */
	protected $objectAction = null;
	
	/**
	 * list of object ids
	 * @var	integer[]
	 */
	protected $objectIDs = array();
	
	/**
	 * additional parameters
	 * @var	mixed[]
	 */
	protected $parameters = array();
	
	/**
	 * @see	\wcf\action\IAction::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_POST['interfaceName'])) $this->interfaceName = StringUtil::trim($_POST['interfaceName']);
		if (isset($_POST['objectIDs']) && is_array($_POST['objectIDs'])) $this->objectIDs = ArrayUtil::toIntegerArray($_POST['objectIDs']);
		if (isset($_POST['parameters']) && is_array($_POST['parameters'])) $this->parameters = $_POST['parameters'];
	}
	
	/**
	 * @see	\wcf\action\IAction::execute()
	 */
	protected function invoke() {
		if (!is_subclass_of($this->className, 'wcf\data\IDatabaseObjectAction')) {
			throw new SystemException("'".$this->className."' does not implement 'wcf\data\IDatabaseObjectAction'");
		}
		
		if (!empty($this->interfaceName)) {
			if (!is_subclass_of($this->className, $this->interfaceName)) {
				throw new SystemException("'".$this->className."' does not implement '".$this->interfaceName."'");
			}
		}
		
		// create object action instance
		$this->objectAction = new $this->className($this->objectIDs, $this->actionName, $this->parameters);
		
		// validate action
		$this->objectAction->validateAction();
		
		// execute action
		$this->response = $this->objectAction->executeAction();
	}
	
	/**
	 * @see	\wcf\action\AJAXInvokeAction::sendResponse()
	 */
	protected function sendResponse() {
		// add benchmark and debug data
		if (ENABLE_BENCHMARK) {
			$this->response['benchmark'] = array(
				'executionTime' => WCF::getBenchmark()->getExecutionTime().'s',
				'memoryUsage' => WCF::getBenchmark()->getMemoryUsage(),
				'phpExecution' => StringUtil::formatNumeric((WCF::getBenchmark()->getExecutionTime() - WCF::getBenchmark()->getQueryExecutionTime()) / WCF::getBenchmark()->getExecutionTime() * 100).'%',
				'sqlExecution' => StringUtil::formatNumeric(WCF::getBenchmark()->getQueryExecutionTime() / WCF::getBenchmark()->getExecutionTime() * 100).'%',
				'sqlQueries' => WCF::getBenchmark()->getQueryCount()
			);
			
			if (ENABLE_DEBUG_MODE) {
				$this->response['benchmark']['items'] = WCF::getBenchmark()->getItems();
			}
		}
		
		parent::sendResponse();
	}
}
