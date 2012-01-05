<?php
namespace wcf\acp\action;
use wcf\action\AbstractSecureAction;
use wcf\system\exception\AJAXException;
use wcf\system\exception\SystemException;
use wcf\system\WCF;
use wcf\util\ClassUtil;
use wcf\util\JSON;

/**
 * Handles worker actions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.action
 * @category 	Community Framework
 */
class WorkerProxyAction extends AbstractSecureAction {
	/**
	 * worker class name
	 * @var	string
	 */
	protected $className = '';
	
	/**
	 * loop counter
	 * @var	integer
	 */
	protected $loopCount = -1;
	
	/**
	 * parameters for worker action
	 * @var	array
	 */
	protected $parameters = array();
	
	/**
	 * worker object
	 * @var	wcf\system\worker\IWorker
	 */
	protected $worker = null;
	
	/**
	 * @see	wcf\action\AbstractAction::_construct()
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
	 * @see	wcf\action\IAction::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_POST['className'])) $this->className = $_POST['className'];
		if (isset($_POST['loopCount'])) $this->loopCount = intval($_POST['loopCount']);
		if (isset($_POST['parameters']) && (array)$_POST['parameters'] === $_POST['parameters']) $this->parameters = $_POST['parameters'];
		
		$this->validate();
	}
	
	/**
	 * Validates class name.
	 */
	protected function validate() {
		if (empty($this->className)) {
			throw new SystemException("class name cannot be empty.");
		}
		
		if (!ClassUtil::isInstanceOf($this->className, 'wcf\system\worker\IWorker')) {
			throw new SystemException("class '".$this->className."' should implement the interface 'wcf\system\worker\IWorker'");
		}
	}
	
	/**
	 * @see	wcf\action\IAction::execute()
	 */
	public function execute() {
		parent::execute();
		
		if ($this->loopCount == -1) {
			$this->sendResponse();
		}
		
		// init worker
		$this->worker = new $this->className($this->parameters);
		$this->worker->setLoopCount($this->loopCount);
		
		// validate worker parameters
		$this->worker->validate();
		
		// calculate progress, triggers countObjects()
		$progress = $this->worker->getProgress();
		
		// execute worker
		$this->worker->execute();
		
		// send current state
		$this->sendResponse($progress, $this->worker->getParameters(), $this->worker->getProceedURL());
		
	}
	
	/**
	 * Sends a JSON-encoded response.
	 * 
	 * @param	integer		$progress
	 * @param	array		$parameters
	 * @param	string		$proceedURL
	 */
	protected function sendResponse($progress = 0, array $parameters = null, $proceedURL = '') {
		if ($parameters === null) $parameters = $this->parameters;
		
		// build return values
		$returnValues = array(
			'className' => $this->className,
			'loopCount' => ($this->loopCount + 1),
			'parameters' => $parameters,
			'proceedURL' => $proceedURL,
			'progress' => $progress
		);
		
		// include template on startup
		if ($this->loopCount == -1) {
			$returnValues['template'] = WCF::getTPL()->fetch('worker');
		}
		
		// send JSON-encoded response
		header('Content-type: application/json');
		echo JSON::encode($returnValues);
		exit;
	}
}
