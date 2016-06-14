<?php
namespace wcf\system\worker;
use wcf\data\DatabaseObjectList;
use wcf\system\event\EventHandler;
use wcf\system\exception\ParentClassException;
use wcf\system\exception\SystemException;
use wcf\system\request\LinkHandler;
use wcf\system\search\SearchIndexManager;
use wcf\system\WCF;

/**
 * Abstract implementation of rebuild data worker.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Worker
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
	 * @inheritDoc
	 */
	public function getObjectList() {
		return $this->objectList;
	}
	
	/**
	 * @inheritDoc
	 */
	public function setLoopCount($loopCount) {
		parent::setLoopCount($loopCount);
		
		$this->initObjectList();
	}
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		WCF::getSession()->checkPermissions(['admin.management.canRebuildData']);
	}
	
	/**
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	public function execute() {
		$this->objectList->readObjects();
		
		SearchIndexManager::getInstance()->beginBulkOperation();
		
		EventHandler::getInstance()->fireAction($this, 'execute');
	}
	
	/**
	 * @inheritDoc
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
		
		if (!is_subclass_of($this->objectListClassName, DatabaseObjectList::class)) {
			throw new ParentClassException($this->objectListClassName, DatabaseObjectList::class);
		}
		
		$this->objectList = new $this->objectListClassName();
		$this->objectList->sqlLimit = $this->limit;
		$this->objectList->sqlOffset = $this->limit * $this->loopCount;
	}
	
	/**
	 * @inheritDoc
	 */
	public function finalize() {
		SearchIndexManager::getInstance()->commitBulkOperation();
	}
}
