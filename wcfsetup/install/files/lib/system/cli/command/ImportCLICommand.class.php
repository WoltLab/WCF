<?php
namespace wcf\system\cli\command;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\database\DatabaseException;
use wcf\system\CLIWCF;
use wcf\system\WCF;

/**
 * Imports data.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cli.command
 * @category	Community Framework
 */
class ImportCLICommand implements ICLICommand {
	/**
	 * database host name
	 * @var	string
	 */
	public $dbHost = '';
	
	/**
	 * database name
	 * @var	string
	 */
	public $dbName = '';
	
	/**
	 * database password
	 * @var	string
	 */
	public $dbPassword = '';
	
	/**
	 * database table prefix
	 * @var	string
	 */
	public $dbPrefix = '';
	
	/**
	 * database user name
	 * @var	string
	 */
	public $dbUser = '';
	
	/**
	 * selected exporter
	 * @var	wcf\system\exporter\IExporter
	 */
	protected $exporter = null;
	
	/**
	 * name of the selected
	 * @var string
	 */
	public $exporterName = '';
	
	/**
	 * list of available exporters
	 * @var	array<wcf\data\object\type\ObjectType>
	 */
	protected $exporters = array();
	
	/**
	 * file system path
	 * @var	string
	 */
	public $fileSystemPath = '';
	
	/**
	 * list of available importers
	 * @var	array<string>
	 */
	public $importers = array();
	
	/**
	 * indicates if the imported will be quit
	 * @var	boolean
	 */
	protected $quitImport = false;
	
	/**
	 * selected data types
	 * @var	array<string>
	 */
	public $selectedData = array();
	
	/**
	 * list of supported data types
	 * @var	array
	 */
	protected $supportedData = array();
	
	/**
	 * user merge mode
	 * @var	integer
	 */
	public $userMergeMode = 0;
	
	/**
	 * @see	wcf\system\cli\command\ICLICommand::canAccess()
	 */
	public function canAccess() {
		return WCF::getSession()->getPermission('admin.system.canImportData');
	}
	
	/**
	 * @see	wcf\system\cli\command\ICLICommand::execute()
	 */
	public function execute(array $parameters) {
		$this->exporters = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.exporter');
		$this->importers = array_keys(ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.importer'));
		
		// step 1) previous import
		// todo
		
		// step 2) exporter
		$this->readExporter();
		
		// step 3) selected data
		$this->readSelectedData();
		if ($this->quitImport) {
			return;
		}
		
		// step 4) user merge mode
		$this->readUserMergeMode();
		
		// step 5) database connection
		$this->readDatabaseConnection();
		
		// step 6) file system path
		$this->readFileSystemPath();
		
		// step 7) save import data
		$queue = $this->exporter->getQueue();
		WCF::getSession()->register('importData', array(
			'additionalData' => array(),
			'dbHost' => $this->dbHost,
			'dbName' => $this->dbName,
			'dbPassword' => $this->dbPassword,
			'dbPrefix' => $this->dbPrefix,
			'dbUser' => $this->dbUser,
			'exporterName' => $this->exporterName,
			'fileSystemPath' => $this->fileSystemPath,
			'userMergeMode' => $this->userMergeMode
		));
		
		// step 8) import data
		CLIWCF::getReader()->println("Starting import"); // todo
		
		foreach ($queue as $objectType) {
			CLIWCF::getReader()->println(WCF::getLanguage()->get('wcf.acp.dataImport.data.'.$objectType));
			$workerCommand = CLICommandHandler::getCommand('worker');
			$workerCommand->execute(array(
				'--objectType='.$objectType,
				'ImportWorker'
			));
		}
		
		CLIWCF::getReader()->println("Import finished"); // todo
		exit(1);
	}
	
	/**
	 * Reads the database connection.
	 */
	protected function readDatabaseConnection() {
		CLIWCF::getReader()->println(WCF::getLanguage()->get('wcf.acp.dataImport.configure.database'));
		while (true) {
			$this->dbHost = CLIWCF::getReader()->readLine(WCF::getLanguage()->get('wcf.acp.dataImport.configure.database.host').'> ');
			$this->dbUser = CLIWCF::getReader()->readLine(WCF::getLanguage()->get('wcf.acp.dataImport.configure.database.user').'> ');
			$this->dbPassword = CLIWCF::getReader()->readLine(WCF::getLanguage()->get('wcf.acp.dataImport.configure.database.password').'> ');
			$this->dbName = CLIWCF::getReader()->readLine(WCF::getLanguage()->get('wcf.acp.dataImport.configure.database.name').'> ');
			$this->dbPrefix = CLIWCF::getReader()->readLine(WCF::getLanguage()->get('wcf.acp.dataImport.configure.database.prefix').'> ');
			// todo: show default value for dbPrefix in brackets?
			
			$this->exporter->setData($this->dbHost, $this->dbUser, $this->dbPassword, $this->dbName, $this->dbPrefix, '', array());
			
			try {
				$this->exporter->validateDatabaseAccess();
			}
			catch (DatabaseException $e) {
				// todo: error message contains HTML
				CLIWCF::getReader()->println(WCF::getLanguage()->getDynamicVariable('wcf.acp.dataImport.configure.database.error', array(
					'exception' => $e
				)));
				continue;
			}
			
			break;
		}
	}
	
	/**
	 * Reads the selected exporter.
	 */
	protected function readExporter() {
		CLIWCF::getReader()->println(WCF::getLanguage()->get('wcf.acp.dataImport.selectExporter'));
		$exporterSelection = array();
		$exporterIndex = 1;
		foreach ($this->exporters as $objectType) {
			CLIWCF::getReader()->println($exporterIndex.') '.WCF::getLanguage()->get('wcf.acp.dataImport.exporter.'.$objectType->objectType));
			$exporterSelection[$exporterIndex++] = $objectType->objectType;
		}
		CLIWCF::getReader()->println('Selection? [1-'.($exporterIndex - 1).']'); // todo
		
		while (true) {
			$exporterIndex = CLIWCF::getReader()->readLine(WCF::getLanguage()->get('wcf.acp.dataImport.exporter').'> ');
			if (isset($exporterSelection[$exporterIndex])) {
				$this->exporterName = $exporterSelection[$exporterIndex];
				break;
			}
			
			CLIWCF::getReader()->println('Invalid selection "'.$exporterSelection.'"'); // todo
		}
		
		$this->exporter = $this->exporters[$this->exporterName]->getProcessor();
		$this->supportedData = $this->exporter->getSupportedData();
		
		// remove unsupported data
		foreach ($this->supportedData as $objectType => $subData) {
			if (!in_array($objectType, $this->importers)) {
				unset($this->supportedData[$objectType]);
				continue;
			}
			
			foreach ($subData as $key => $value) {
				if (!in_array($value, $this->importers)) {
					unset($this->supportedData[$objectType][$key]);
				}
			}
		}
	}
	
	/**
	 * Reads the path to the file system.
	 */
	protected function readFileSystemPath() {
		CLIWCF::getReader()->println(WCF::getLanguage()->get('wcf.acp.dataImport.configure.fileSystem.path'));
		while (true) {
			$this->fileSystemPath = CLIWCF::getReader()->readLine('> ');
			$this->exporter->setData($this->dbHost, $this->dbUser, $this->dbPassword, $this->dbName, $this->dbPrefix, $this->fileSystemPath, array());
			
			if (!$this->exporter->validateFileAccess()) {
				CLIWCF::getReader()->println(WCF::getLanguage()->get('wcf.acp.dataImport.configure.fileSystem.path.error'));
				continue;
			}
			
			break;
		}
	}
	
	/**
	 * Reads the selected data which will be imported.
	 */
	protected function readSelectedData() {
		$printPrimaryTypes = true;
		$selectedData = array();
		$supportedDataSelection = array(
			'' => array()
		);
		while (true) {
			if ($printPrimaryTypes) {
				// print primary import data types
				CLIWCF::getReader()->println(WCF::getLanguage()->get('wcf.acp.dataImport.configure.data.description'));
				$supportedDataIndex = 1;
				$minSupportedDataIndex = 1;
				foreach ($this->supportedData as $objectType => $subData) {
					if (!isset($selectedData[$objectType])) {
						CLIWCF::getReader()->println($supportedDataIndex.') '.WCF::getLanguage()->get('wcf.acp.dataImport.data.'.$objectType));
						$supportedDataSelection[''][$supportedDataIndex++] = $objectType;
					}
					else {
						if ($minSupportedDataIndex == $supportedDataIndex) {
							$minSupportedDataIndex++;
						}
						$supportedDataIndex++;
					}
				}
				CLIWCF::getReader()->println('Selection? ['.$minSupportedDataIndex.'-'.($supportedDataIndex - 1).']'); // todo
				$printPrimaryTypes = false;
			}
			
			// read index of selected primary import data type
			$selectedObjectTypeIndex = CLIWCF::getReader()->readLine(WCF::getLanguage()->get('wcf.acp.dataImport.configure.data').'> ');
			
			// if no primary import data type is selected, finish data selection
			if ($selectedObjectTypeIndex == '') {
				// if no data is selected, quit import
				if (empty($selectedData)) {
					CLIWCF::getReader()->println("No data selected, quitting import."); // todo
					$this->quitImport = true;
					return;
				}
				break;
			}
			
			// validate selected primary import data type
			if (isset($supportedDataSelection[''][$selectedObjectTypeIndex])) {
				$selectedObjectType = $supportedDataSelection[''][$selectedObjectTypeIndex];
				$selectedData[$selectedObjectType] = array();
				unset($supportedDataSelection[''][$selectedObjectTypeIndex]);
			}
			else if (isset($selectedData[$selectedObjectTypeIndex])) {
				CLIWCF::getReader()->println("Already selected."); // todo
				continue;
			}
			else {
				CLIWCF::getReader()->println("Unknown."); // todo
				continue;
			}
			
			// handle secondary import data types
			if (!empty($this->supportedData[$selectedObjectType])) {
				// print secondary import data types
				CLIWCF::getReader()->println('  '.WCF::getLanguage()->get('wcf.acp.dataImport.configure.data.description'));
				CLIWCF::getReader()->println('  0) All'); // todo
				
				$supportedDataSelection[$selectedObjectType] = array();
				$supportedDataIndex = 1;
				foreach ($this->supportedData[$selectedObjectType] as $objectType) {
					CLIWCF::getReader()->println('  '.$supportedDataIndex.') '.WCF::getLanguage()->get('wcf.acp.dataImport.data.'.$objectType));
					$supportedDataSelection[$selectedObjectType][$supportedDataIndex++] = $objectType;
				}
				CLIWCF::getReader()->println('Selection? [0-'.($supportedDataIndex - 1).']'); // todo
				
				while (true) {
					// read index of selected secondary import data type
					$selectedSecondaryObjectTypeIndex = CLIWCF::getReader()->readLine('  '.WCF::getLanguage()->get('wcf.acp.dataImport.configure.data').'> ');
					
					// continue with primary import data type selection
					if ($selectedSecondaryObjectTypeIndex == '') {
						break;
					}
					
					// validate selected secondary import data type
					if ($selectedSecondaryObjectTypeIndex == intval($selectedSecondaryObjectTypeIndex) && !$selectedSecondaryObjectTypeIndex) {
						// selected all secondary import data type
						$selectedData[$selectedObjectType] = array_merge($selectedData[$selectedObjectType], $supportedDataSelection[$selectedObjectType]);
						break;
					}
					else if (isset($supportedDataSelection[$selectedObjectType][$selectedSecondaryObjectTypeIndex])) {
						$selectedSecondaryObjectType = $supportedDataSelection[$selectedObjectType][$selectedSecondaryObjectTypeIndex];
						$selectedData[$selectedObjectType][] = $selectedSecondaryObjectType;
						unset($supportedDataSelection[$selectedObjectType][$selectedSecondaryObjectTypeIndex]);
					}
					else if (in_array($selectedSecondaryObjectType, $selectedData[$selectedObjectType])) {
						CLIWCF::getReader()->println("Already selected."); // todo
						continue;
					}
					else {
						CLIWCF::getReader()->println("Unknown."); // todo
						continue;
					}
					
					// check if all possible secondary import data types are selected
					if (count($selectedData[$selectedObjectType]) == count($this->supportedData[$selectedObjectType])) {
						break;
					}
				}
				
				if (!empty($supportedDataSelection[$selectedObjectType])) {
					$printPrimaryTypes = true;
				}
			}
			
			// check if all possible primary import data types are selected
			if (count($selectedData) == count($this->supportedData)) {
				break;
			}
		}
		
		foreach ($selectedData as $objectType => $objectTypes) {
			$this->selectedData[] = $objectType;
			$this->selectedData = array_merge($this->selectedData, $objectTypes);
		}
		
		if (!$this->exporter->validateSelectedData($this->selectedData)) {
			CLIWCF::getReader()->println("Invalid selected data, quitting import."); // todo
			$this->quitImport = true;
		}
	}
	
	/**
	 * Reads the user merge mode.
	 */
	protected function readUserMergeMode() {
		CLIWCF::getReader()->println(WCF::getLanguage()->get('wcf.acp.dataImport.configure.settings.userMergeMode'));
		CLIWCF::getReader()->println('1) '.WCF::getLanguage()->get('wcf.acp.dataImport.configure.settings.userMergeMode.1'));
		CLIWCF::getReader()->println('2) '.WCF::getLanguage()->get('wcf.acp.dataImport.configure.settings.userMergeMode.2').' (*)');
		CLIWCF::getReader()->println('3) '.WCF::getLanguage()->get('wcf.acp.dataImport.configure.settings.userMergeMode.3'));
		CLIWCF::getReader()->println('Selection? [1-3]'); // todo
		
		while (true) {
			$this->userMergeMode = CLIWCF::getReader()->readLine('> ');
			if ($this->userMergeMode != intval($this->userMergeMode) || $this->userMergeMode < 1 || $this->userMergeMode > 3) {
				$this->userMergeMode = 2;
			}
			
			break;
		}
	}
}
