<?php
namespace wcf\action;
use wcf\data\IStorableObject;
use wcf\system\exception\SystemException;
use wcf\util\ArrayUtil;
use wcf\util\ClassUtil;
use wcf\util\StringUtil;

/**
 * Default implementation for object-actions using the AJAX-API.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2014 WoltLab GmbH
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
	 * @var	array<integer>
	 */
	protected $objectIDs = array();
	
	/**
	 * additional parameters
	 * @var	array<mixed>
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
		if (!ClassUtil::isInstanceOf($this->className, 'wcf\data\IDatabaseObjectAction')) {
			throw new SystemException("'".$this->className."' does not implement 'wcf\data\IDatabaseObjectAction'");
		}
		
		if (!empty($this->interfaceName)) {
			if (!ClassUtil::isInstanceOf($this->className, $this->interfaceName)) {
				throw new SystemException("'".$this->className."' does not implement '".$this->interfaceName."'");
			}
		}
		
		// create object action instance
		$this->objectAction = new $this->className($this->objectIDs, $this->actionName, $this->parameters);
		
		// validate action
		$this->objectAction->validateAction();
		
		// execute action
		$this->response = $this->objectAction->executeAction();
		if (isset($this->response['returnValues'])) {
			$this->response['returnValues'] = $this->getData($this->response['returnValues']);
		}
	}
	
	/**
	 * Gets the values of object data variables
	 * 
	 * @param	mixed		$response
	 * @return	mixed
	 */
	protected function getData($response) {
		if ($response instanceof IStorableObject) {
			return $response->getData();
		}
		if (is_array($response)) {
			foreach ($response as &$object) {
				$object = $this->getData($object);
			}
			unset($object);
		}
		return $response;
	}
}
