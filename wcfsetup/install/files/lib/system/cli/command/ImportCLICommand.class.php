<?php
namespace wcf\system\cli\command;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\database\DatabaseException;
use wcf\system\importer\ImportHandler;
use wcf\system\importer\UserImporter;
use wcf\system\CLIWCF;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Imports data.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
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
	 * @var	\wcf\system\exporter\IExporter
	 */
	protected $exporter = null;
	
	/**
	 * name of the selected
	 * @var	string
	 */
	public $exporterName = '';
	
	/**
	 * list of available exporters
	 * @var	array<\wcf\data\object\type\ObjectType>
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
	 * @see	\wcf\system\cli\command\ICLICommand::canAccess()
	 */
	public function canAccess() {
		return WCF::getSession()->getPermission('admin.management.canImportData');
	}
	
	/**
	 * @see	\wcf\system\cli\command\ICLICommand::execute()
	 */
	public function execute(array $parameters) {
		CLIWCF::getReader()->setHistoryEnabled(false);
		
		$this->exporters = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.exporter');
		$this->importers = array_keys(ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.importer'));
		
		if (empty($this->exporters)) {
			CLIWCF::getReader()->println(WCF::getLanguage()->get('wcf.acp.dataImport.selectExporter.noExporters'));
			return;
		}
		
		if (PACKAGE_ID == 1) {
			CLIWCF::getReader()->println(StringUtil::stripHTML(WCF::getLanguage()->get('wcf.acp.dataImport.cli.info.wcf')));
			
			$answer = CLIWCF::getReader()->readLine('> ');
			if ($answer === null) exit;
			if (mb_strtolower($answer) != 'y') {
				CLIWCF::getReader()->setHistoryEnabled(true);
				return;
			}
		}
		
		// step 1) previous import
		$sql = "SELECT	COUNT(*)
			FROM	wcf".WCF_N."_import_mapping";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		if ($statement->fetchColumn()) {
			CLIWCF::getReader()->println(StringUtil::stripHTML(WCF::getLanguage()->get('wcf.acp.dataImport.existingMapping.notice')));
			CLIWCF::getReader()->println(WCF::getLanguage()->get('wcf.acp.dataImport.existingMapping.confirmMessage') . ' [YN]');
			
			$answer = CLIWCF::getReader()->readLine('> ');
			if ($answer === null) exit;
			if (mb_strtolower($answer) == 'y') {
				ImportHandler::getInstance()->resetMapping();
			}
		}
		
		// step 2) exporter
		$this->readExporter();
		
		// step 3) selected data
		$this->readSelectedData();
		if ($this->quitImport) {
			CLIWCF::getReader()->setHistoryEnabled(true);
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
		CLIWCF::getReader()->println(WCF::getLanguage()->get('wcf.acp.dataImport.started'));
		
		foreach ($queue as $objectType) {
			CLIWCF::getReader()->println(WCF::getLanguage()->get('wcf.acp.dataImport.data.'.$objectType));
			$workerCommand = CLICommandHandler::getCommand('worker');
			$workerCommand->execute(array(
				'--objectType='.$objectType,
				'ImportWorker'
			));
		}
		
		CLIWCF::getReader()->println(WCF::getLanguage()->get('wcf.acp.dataImport.completed'));
		
		CLIWCF::getReader()->setHistoryEnabled(true);
	}
	
	/**
	 * Reads the database connection.
	 */
	protected function readDatabaseConnection() {
		while (true) {
			CLIWCF::getReader()->println(WCF::getLanguage()->get('wcf.acp.dataImport.configure.database'));
			$this->dbHost = CLIWCF::getReader()->readLine(WCF::getLanguage()->get('wcf.acp.dataImport.configure.database.host').'> ');
			if ($this->dbHost === null) exit;
			$this->dbUser = CLIWCF::getReader()->readLine(WCF::getLanguage()->get('wcf.acp.dataImport.configure.database.user').'> ');
			if ($this->dbUser === null) exit;
			$this->dbPassword = CLIWCF::getReader()->readLine(WCF::getLanguage()->get('wcf.acp.dataImport.configure.database.password').'> ', '*');
			if ($this->dbPassword === null) exit;
			$this->dbName = CLIWCF::getReader()->readLine(WCF::getLanguage()->get('wcf.acp.dataImport.configure.database.name').'> ');
			if ($this->dbName === null) exit;
			$this->dbPrefix = CLIWCF::getReader()->readLine(WCF::getLanguage()->get('wcf.acp.dataImport.configure.database.prefix').'> ');
			if ($this->dbPrefix === null) exit;
			
			$this->exporter->setData($this->dbHost, $this->dbUser, $this->dbPassword, $this->dbName, $this->dbPrefix, '', array());
			
			try {
				$this->exporter->validateDatabaseAccess();
			}
			catch (DatabaseException $e) {
				$errorMessage = WCF::getLanguage()->getDynamicVariable('wcf.acp.dataImport.configure.database.error', array(
					'exception' => $e
				));
				$errorMessageLines = explode('<br />', $errorMessage);
				foreach ($errorMessageLines as &$line) {
					$line = StringUtil::stripHTML($line);
				}
				unset($line);
				
				foreach ($errorMessageLines as $line) {
					CLIWCF::getReader()->println($line);
				}
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
		CLIWCF::getReader()->println(WCF::getLanguage()->getDynamicVariable('wcf.acp.dataImport.cli.selection', array(
			'minSelection' => 1,
			'maxSelection' => $exporterIndex - 1
		)));
		
		while (true) {
			$exporterIndex = CLIWCF::getReader()->readLine(WCF::getLanguage()->get('wcf.acp.dataImport.exporter').'> ');
			if ($exporterIndex === null) exit;
			
			if (isset($exporterSelection[$exporterIndex])) {
				$this->exporterName = $exporterSelection[$exporterIndex];
				break;
			}
			
			CLIWCF::getReader()->println(WCF::getLanguage()->get('wcf.acp.dataImport.selectExporter.error.notValid'));
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
			if ($this->fileSystemPath === null) exit;
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
		
		$i = 1;
		$availablePrimaryDataTypes = array();
		foreach ($this->supportedData as $objectType => $subData) {
			$availablePrimaryDataTypes[$i++] = $objectType;
		}
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
				CLIWCF::getReader()->println(WCF::getLanguage()->getDynamicVariable('wcf.acp.dataImport.cli.selection', array(
					'minSelection' => $minSupportedDataIndex,
					'maxSelection' => $supportedDataIndex - 1
				)));
				$printPrimaryTypes = false;
			}
			
			// read index of selected primary import data type
			$selectedObjectTypeIndex = CLIWCF::getReader()->readLine(WCF::getLanguage()->get('wcf.acp.dataImport.configure.data').'> ');
			if ($selectedObjectTypeIndex === null) exit;
			
			// if no primary import data type is selected, finish data selection
			if ($selectedObjectTypeIndex == '') {
				// if no data is selected, quit import
				if (empty($selectedData)) {
					CLIWCF::getReader()->println(WCF::getLanguage()->get('wcf.acp.dataImport.cli.configure.data.error.noSelection'));
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
			else if (isset($availablePrimaryDataTypes[$selectedObjectTypeIndex])) {
				CLIWCF::getReader()->println(WCF::getLanguage()->get('wcf.acp.dataImport.cli.configure.data.alreadySelected'));
				continue;
			}
			else {
				CLIWCF::getReader()->println(WCF::getLanguage()->get('wcf.acp.dataImport.cli.configure.data.error.notValid'));
				continue;
			}
			
			// handle secondary import data types
			if (!empty($this->supportedData[$selectedObjectType])) {
				// print secondary import data types
				CLIWCF::getReader()->println('  '.WCF::getLanguage()->get('wcf.acp.dataImport.configure.data.description'));
				CLIWCF::getReader()->println('  0) '.WCF::getLanguage()->get('wcf.acp.dataImport.cli.configure.data.selectAll'));
				
				$supportedDataSelection[$selectedObjectType] = array();
				$supportedDataIndex = 1;
				foreach ($this->supportedData[$selectedObjectType] as $objectType) {
					CLIWCF::getReader()->println('  '.$supportedDataIndex.') '.WCF::getLanguage()->get('wcf.acp.dataImport.data.'.$objectType));
					$supportedDataSelection[$selectedObjectType][$supportedDataIndex++] = $objectType;
				}
				CLIWCF::getReader()->println('  '.WCF::getLanguage()->getDynamicVariable('wcf.acp.dataImport.cli.selection', array(
					'minSelection' => 0,
					'maxSelection' => $supportedDataIndex - 1
				)));
				
				while (true) {
					// read index of selected secondary import data type
					$selectedSecondaryObjectTypeIndex = CLIWCF::getReader()->readLine('  '.WCF::getLanguage()->get('wcf.acp.dataImport.configure.data').'> ');
					if ($selectedSecondaryObjectTypeIndex === null) exit;
					
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
						$selectedData[$selectedObjectType][$selectedSecondaryObjectTypeIndex] = $selectedSecondaryObjectType;
						unset($supportedDataSelection[$selectedObjectType][$selectedSecondaryObjectTypeIndex]);
					}
					else if (isset($selectedData[$selectedObjectType][$selectedSecondaryObjectTypeIndex])) {
						CLIWCF::getReader()->println('  '.WCF::getLanguage()->get('wcf.acp.dataImport.cli.configure.data.alreadySelected'));
						continue;
					}
					else {
						CLIWCF::getReader()->println('  '.WCF::getLanguage()->get('wcf.acp.dataImport.cli.configure.data.error.notValid'));
						continue;
					}
					
					// check if all possible secondary import data types are selected
					if (count($selectedData[$selectedObjectType]) == count($this->supportedData[$selectedObjectType])) {
						$printPrimaryTypes = true;
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
			CLIWCF::getReader()->println(WCF::getLanguage()->get('wcf.acp.dataImport.cli.configure.data.error.noSelection'));
			$this->quitImport = true;
		}
	}
	
	/**
	 * Reads the user merge mode.
	 */
	protected function readUserMergeMode() {
		CLIWCF::getReader()->println(WCF::getLanguage()->get('wcf.acp.dataImport.configure.settings.userMergeMode'));
		CLIWCF::getReader()->println('1) '.WCF::getLanguage()->get('wcf.acp.dataImport.configure.settings.userMergeMode.4').' (*)');
		CLIWCF::getReader()->println('2) '.WCF::getLanguage()->get('wcf.acp.dataImport.configure.settings.userMergeMode.5'));
		CLIWCF::getReader()->println(WCF::getLanguage()->getDynamicVariable('wcf.acp.dataImport.cli.selection', array(
			'minSelection' => 1,
			'maxSelection' => 2
		)));
		
		while (true) {
			$this->userMergeMode = CLIWCF::getReader()->readLine('> ');
			if ($this->userMergeMode === null) exit;
			switch (intval($this->userMergeMode)) {
				case 1:
					$this->userMergeMode = UserImporter::MERGE_MODE_EMAIL;
				break;
				case 2:
					$this->userMergeMode = UserImporter::MERGE_MODE_USERNAME_OR_EMAIL;
				break;
				default:
					$this->userMergeMode = UserImporter::MERGE_MODE_EMAIL;
				break;
			}
			
			break;
		}
	}
}
