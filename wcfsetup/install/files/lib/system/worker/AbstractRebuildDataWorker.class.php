<?php
namespace wcf\system\worker;
use wcf\system\event\EventHandler;
use wcf\system\exception\SystemException;
use wcf\system\request\LinkHandler;
use wcf\system\search\SearchIndexManager;
use wcf\system\WCF;
use wcf\util\ClassUtil;

/**
 * Abstract implementation of rebuild data worker.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.worker
 * @category	Community Framework
 */
abstract class AbstractRebuildDataWorker extends AbstractWorker implements IRebuildDataWorker {
	/**
	 * class name for DatabaseObjectList
	 * @var	string
	 */
	protected $objectListClassName = '';
	
	/**
	 * database object list
	 * @var	\wcf\data\DatabaseObjectList
	 */
	protected $objectList = null;
	
	/**
	 * @see	\wcf\system\worker\IRebuildDataWorker::getObjectList()
	 */
	public function getObjectList() {
		return $this->objectList;
	}
	
	/**
	 * @see	\wcf\system\worker\IWorker::getLoopCount()
	 */
	public function setLoopCount($loopCount) {
		parent::setLoopCount($loopCount);
		
		$this->initObjectList();
	}
	
	/**
	 * @see	\wcf\system\worker\IWorker::validate()
	 */
	public function validate() {
		WCF::getSession()->checkPermissions(array('admin.system.canRebuildData'));
	}
	
	/**
	 * @see	\wcf\system\worker\IWorker::countObjects()
	 */
	public function countObjects() {
		if ($this->count === null) {
			if ($this->objectList === null) {
				$this->initObjectList();
			}
			
			$this->count = $this->objectList->countObjects();
		}
	}
	
	/**
	 * @see	\wcf\system\worker\IWorker::execute()
	 */
	public function execute() {
		$this->objectList->readObjects();
		
		SearchIndexManager::getInstance()->beginBulkOperation();
		
		EventHandler::getInstance()->fireAction($this, 'execute');
	}
	
	/**
	 * @see	\wcf\system\worker\IWorker::getProceedURL()
	 */
	public function getProceedURL() {
		return LinkHandler::getInstance()->getLink('RebuildData');
	}
	
	/**
	 * Initializes DatabaseObjectList instance.
	 */
	protected function initObjectList() {
		if (empty($this->objectListClassName)) {
			throw new SystemException('DatabaseObjectList class name not specified.');
		}
		
		if (!ClassUtil::isInstanceOf($this->objectListClassName, 'wcf\data\DatabaseObjectList')) {
			throw new SystemException("'".$this->objectListClassName."' does not extend 'wcf\data\DatabaseObjectList'");
		}
		
		$this->objectList = new $this->objectListClassName();
		$this->objectList->sqlLimit = $this->limit;
		$this->objectList->sqlOffset = $this->limit * $this->loopCount;
	}
	
	/**
	 * @see	\wcf\system\worker\IWorker::finalize()
	 */
	public function finalize() {
		SearchIndexManager::getInstance()->commitBulkOperation();
	}
}
