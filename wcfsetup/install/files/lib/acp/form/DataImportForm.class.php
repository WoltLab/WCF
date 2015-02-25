<?php
namespace wcf\acp\form;
use wcf\data\object\type\ObjectTypeCache;
use wcf\form\AbstractForm;
use wcf\system\database\DatabaseException;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\UserInputException;
use wcf\system\importer\UserImporter;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Provides the data import form.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.user
 * @subpackage	acp.form
 * @category	Community Framework
 */
class DataImportForm extends AbstractForm {
	/**
	 * additional data
	 * @var	array
	 */
	public $additionalData = array();
	
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.maintenance.import';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.system.canImportData');
	
	/**
	 * list of available exporters
	 * @var	array
	 */
	public $exporters = array();
	
	/**
	 * exporter name
	 * @var	string
	 */
	public $exporterName = '';
	
	/**
	 * exporter object
	 * @var	\wcf\system\exporter\IExporter
	 */
	public $exporter = null;
	
	/**
	 * list of available importers
	 * @var	array<string>
	 */
	public $importers = array();
	
	/**
	 * list of supported data types
	 * @var	array
	 */
	public $supportedData = array();
	
	/**
	 * selected data types
	 * @var	array
	 */
	public $selectedData = array();
	
	/**
	 * database host name
	 * @var	string
	 */
	public $dbHost = '';
	
	/**
	 * database user name
	 * @var	string
	 */
	public $dbUser = '';
	
	/**
	 * database password
	 * @var	string
	 */
	public $dbPassword = '';
	
	/**
	 * database name
	 * @var	string
	 */
	public $dbName = '';
	
	/**
	 * database table prefix
	 * @var	string
	 */
	public $dbPrefix = '';
	
	/**
	 * file system path
	 * @var	string
	 */
	public $fileSystemPath = '';
	
	/**
	 * display a warning if InnoDB uses a slow configuration
	 * @var	boolean
	 */
	public $showInnoDBWarning = false;
	
	/**
	 * display notice for existing import mappings
	 * @var	boolean
	 */
	public $showMappingNotice = false;
	
	/**
	 * user merge mode
	 * @var	integer
	 */
	public $userMergeMode = UserImporter::MERGE_MODE_EMAIL;
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		// get available exporters/importers
		$this->exporters = ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.exporter');
		
		// sort exporters by name
		uksort($this->exporters, function ($a, $b) {
			return strcasecmp(WCF::getLanguage()->get('wcf.acp.dataImport.exporter.'.$a), WCF::getLanguage()->get('wcf.acp.dataImport.exporter.'.$b));
		});
		
		$this->importers = array_keys(ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.importer'));
		
		if (isset($_REQUEST['exporterName'])) {
			$this->exporterName = $_REQUEST['exporterName'];
			if (!isset($this->exporters[$this->exporterName])) {
				throw new IllegalLinkException();
			}
			
			$this->exporter = $this->exporters[$this->exporterName]->getProcessor();
			$this->supportedData = $this->exporter->getSupportedData();
			
			// remove unsupported data
			foreach ($this->supportedData as $key => $subData) {
				if (!in_array($key, $this->importers)) {
					unset($this->supportedData[$key]);
					continue;
				}
				
				foreach ($subData as $key2 => $value) {
					if (!in_array($value, $this->importers)) {
						unset($this->supportedData[$key][$key2]);
					}
				}
			}
			
			// get default database prefix
			if (!count($_POST)) {
				$this->dbPrefix = $this->exporter->getDefaultDatabasePrefix();
			}
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::readFormParameters()
	 */
	public function readFormParameters() {
		parent::readFormParameters();
		
		if (isset($_POST['selectedData']) && is_array($_POST['selectedData'])) $this->selectedData = $_POST['selectedData'];
		
		if (isset($_POST['dbHost'])) $this->dbHost = StringUtil::trim($_POST['dbHost']);
		if (isset($_POST['dbUser'])) $this->dbUser = StringUtil::trim($_POST['dbUser']);
		if (isset($_POST['dbPassword'])) $this->dbPassword = $_POST['dbPassword'];
		if (isset($_POST['dbName'])) $this->dbName = StringUtil::trim($_POST['dbName']);
		if (isset($_POST['dbPrefix'])) $this->dbPrefix = StringUtil::trim($_POST['dbPrefix']);
		if (isset($_POST['fileSystemPath'])) $this->fileSystemPath = StringUtil::trim($_POST['fileSystemPath']);
		if (isset($_POST['userMergeMode'])) $this->userMergeMode = intval($_POST['userMergeMode']);
		if (isset($_POST['additionalData'])) $this->additionalData = ArrayUtil::trim($_POST['additionalData']);
	}
	
	/**
	 * @see	\wcf\form\IForm::submit()
	 */
	public function submit() {
		if (!isset($_POST['sourceSelection'])) {
			parent::submit();
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::validate()
	 */
	public function validate() {
		parent::validate();
		
		$this->exporter->setData($this->dbHost, $this->dbUser, $this->dbPassword, $this->dbName, $this->dbPrefix, $this->fileSystemPath, $this->additionalData);
		
		// validate database Access
		try {
			$this->exporter->validateDatabaseAccess();
		}
		catch (DatabaseException $e) {
			WCF::getTPL()->assign('exception', $e);
			throw new UserInputException('database');
		}
		
		// validate selected data
		if (!$this->exporter->validateSelectedData($this->selectedData)) {
			throw new UserInputException('selectedData');
		}
		
		// validate file access
		if (!$this->exporter->validateFileAccess()) {
			throw new UserInputException('fileSystemPath');
		}
		
		// validate user merge mode
		switch ($this->userMergeMode) {
			case UserImporter::MERGE_MODE_EMAIL:
			case UserImporter::MERGE_MODE_USERNAME_OR_EMAIL:
			break;
			default:
				$this->userMergeMode = UserImporter::MERGE_MODE_EMAIL;
		}
	}
	
	/**
	 * @see	\wcf\form\IForm::save()
	 */
	public function save() {
		parent::save();
		
		// get queue
		$queue = $this->exporter->getQueue();
		
		// save import data
		WCF::getSession()->register('importData', array(
			'exporterName' => $this->exporterName,
			'dbHost' => $this->dbHost,
			'dbUser' => $this->dbUser,
			'dbPassword' => $this->dbPassword,
			'dbName' => $this->dbName,
			'dbPrefix' => $this->dbPrefix,
			'fileSystemPath' => $this->fileSystemPath,
			'userMergeMode' => $this->userMergeMode,
			'additionalData' => $this->additionalData
		));
		
		WCF::getTPL()->assign('queue', $queue);
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
		if (empty($_POST)) {
			$this->fileSystemPath = (!empty($_SERVER['DOCUMENT_ROOT']) ? $_SERVER['DOCUMENT_ROOT'] : WCF_DIR);
			
			if (!$this->exporterName) {
				$sql = "SELECT	COUNT(*) AS count
					FROM	wcf".WCF_N."_import_mapping";
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute();
				$row = $statement->fetchArray();
				
				if ($row['count']) {
					$this->showMappingNotice = true;
				}
			}
		}
		
		$sql = "SHOW VARIABLES LIKE 'innodb_flush_log_at_trx_commit'";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		$row = $statement->fetchArray();
		if ($row && $row['Value'] == 1) {
			$this->showInnoDBWarning = true;
		}
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'exporter' => $this->exporter,
			'importers' => $this->importers,
			'exporterName' => $this->exporterName,
			'availableExporters' => $this->exporters,
			'supportedData' => $this->supportedData,
			'selectedData' => $this->selectedData,
			'dbHost' => $this->dbHost,
			'dbUser' => $this->dbUser,
			'dbPassword' => $this->dbPassword,
			'dbName' => $this->dbName,
			'dbPrefix' => $this->dbPrefix,
			'fileSystemPath' => $this->fileSystemPath,
			'userMergeMode' => $this->userMergeMode,
			'showInnoDBWarning' => $this->showInnoDBWarning,
			'showMappingNotice' => $this->showMappingNotice,
			'additionalData' => $this->additionalData
		));
	}
}
