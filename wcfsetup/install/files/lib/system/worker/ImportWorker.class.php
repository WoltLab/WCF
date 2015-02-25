<?php
namespace wcf\system\worker;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\exception\SystemException;
use wcf\system\importer\ImportHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Worker implementation for data import.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.worker
 * @category	Community Framework
 */
class ImportWorker extends AbstractWorker {
	/**
	 * import data
	 * @var	array
	 */
	protected $importData = null;
	
	/**
	 * exporter object
	 * @var	\wcf\system\exporter\IExporter
	 */
	protected $exporter = null;
	
	/**
	 * @see	\wcf\system\worker\IWorker::validate()
	 */
	public function validate() {
		WCF::getSession()->checkPermissions(array('admin.system.canImportData'));
		
		if (!isset($this->parameters['objectType'])) {
			throw new SystemException("parameter 'objectType' missing");
		}
		
		// get import data
		$this->importData = WCF::getSession()->getVar('importData');
		if ($this->importData === null) {
			throw new SystemException("import data missing");
		}
		
		// get exporter
		$this->exporter = ObjectTypeCache::getInstance()->getObjectTypeByName('com.woltlab.wcf.exporter', $this->importData['exporterName'])->getProcessor();
		
		// set data
		$this->exporter->setData($this->importData['dbHost'], $this->importData['dbUser'], $this->importData['dbPassword'], $this->importData['dbName'], $this->importData['dbPrefix'], $this->importData['fileSystemPath'], $this->importData['additionalData']);
		$this->exporter->init();
		
		// set user merge mode
		ImportHandler::getInstance()->setUserMergeMode($this->importData['userMergeMode']);
		
		// set import hash
		ImportHandler::getInstance()->setImportHash(substr(StringUtil::getHash($this->importData['dbHost'] . $this->importData['dbName'] . $this->importData['dbPrefix']), 0, 8));
	}
	
	/**
	 * @see	\wcf\system\worker\AbstractWorker::countObjects()
	 */
	protected function countObjects() {
		$this->count = $this->exporter->countLoops($this->parameters['objectType']);
	}
	
	/**
	 * @see	\wcf\system\worker\IWorker::getProgress()
	 */
	public function getProgress() {
		$this->countObjects();
		
		if (!$this->count) {
			return 100;
		}
		
		$progress = (($this->loopCount + 1) / $this->count) * 100;
		if ($progress > 100) $progress = 100;
		return floor($progress);
	}
	
	/**
	 * @see	\wcf\system\worker\IWorker::execute()
	 */
	public function execute() {
		if (!$this->count) {
			return;
		}
		
		// disable mysql strict mode
		$sql = "SET SESSION sql_mode = 'ANSI,ONLY_FULL_GROUP_BY'";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		
		$this->exporter->exportData($this->parameters['objectType'], $this->loopCount);
	}
	
	/**
	 * @see	\wcf\system\worker\IWorker::getProceedURL()
	 */
	public function getProceedURL() {
		return '';
	}
}
