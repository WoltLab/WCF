<?php
namespace wcf\system\exporter;
use wcf\system\database\DatabaseException;
use wcf\system\database\MySQLDatabase;
use wcf\system\exception\SystemException;
use wcf\util\FileUtil;

/**
 * Basic implementation of IExporter.
 *
 * @author	Marcel Werk
 * @copyright	2001-2013 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.exporter
 * @category	Community Framework
 */
abstract class AbstractExporter implements IExporter {
	/**
	 * additional data
	 * @var array
	 */
	public $additionalData = array();

	/**
	 * database host name
	 * @var string
	 */
	protected $databaseHost = '';

	/**
	 * database username
	 * @var string
	 */
	protected $databaseUser = '';

	/**
	 * database password
	 * @var string
	 */
	protected $databasePassword = '';

	/**
	 * database name
	 * @var string
	 */
	protected $databaseName = '';

	/**
	 * table prefix
	 * @var string
	 */
	protected $databasePrefix = '';

	/**
	 * file system path
	 * @var string
	 */
	protected $fileSystemPath = '';

	/**
	 * database connection
	 * @var wcf\system\database\Database
	 */
	protected $database = null;

	/**
	 * object type => method names
	 * @var array
	 */
	protected $methods = array();

	/**
	 * limits for items per run
	 * @var array<integer>
	 */
	protected $limits = array();

	/**
	 * default limit for items per run
	 * @var integer
	 */
	protected $defaultLimit = 1000;

	/**
	 * selected import data
	 * @var array
	 */
	protected $selectedData = array();
	
	/**
	 * @see wcf\system\exporter\IExporter::setData()
	 */
	public function setData($databaseHost, $databaseUser, $databasePassword, $databaseName, $databasePrefix, $fileSystemPath, $additionalData) {
		$this->databaseHost = $databaseHost;
		$this->databaseUser = $databaseUser;
		$this->databasePassword = $databasePassword;
		$this->databaseName = $databaseName;
		$this->databasePrefix = $databasePrefix;
		$this->fileSystemPath = ($fileSystemPath ? FileUtil::addTrailingSlash($fileSystemPath) : '');
		$this->additionalData = $additionalData;
	}

	/**
	 * @see wcf\system\exporter\IExporter::init()
	 */
	public function init() {
		$this->database = new MySQLDatabase($this->databaseHost, $this->databaseUser, $this->databasePassword, $this->databaseName, 0);
	}

	/**
	 * @see wcf\system\exporter\IExporter::validateDatabaseAccess()
	 */
	public function validateDatabaseAccess() {
		$this->init();
	}

	/**
	 * @see wcf\system\exporter\IExporter::getDefaultDatabasePrefix()
	 */
	public function getDefaultDatabasePrefix() {
		return '';
	}

	/**
	 * @see wcf\system\exporter\IExporter::countLoops()
	 */
	public function countLoops($objectType) {
		if (!isset($this->methods[$objectType]) || !method_exists($this, 'count'.$this->methods[$objectType])) {
			throw new SystemException("unknown object type '".$objectType."' given");
		}

		$count = call_user_func(array($this, 'count'.$this->methods[$objectType]));
		$limit = (isset($this->limits[$objectType]) ? $this->limits[$objectType] : $this->defaultLimit);
		return ceil($count / $limit);
	}

	/**
	 * @see wcf\system\exporter\IExporter::exportData()
	 */
	public function exportData($objectType, $loopCount = 0) {
		if (!isset($this->methods[$objectType]) || !method_exists($this, 'export'.$this->methods[$objectType])) {
			throw new SystemException("unknown object type '".$objectType."' given");
		}

		$limit = (isset($this->limits[$objectType]) ? $this->limits[$objectType] : $this->defaultLimit);
		call_user_func(array($this, 'export'.$this->methods[$objectType]), $loopCount * $limit, $limit);
	}
	
	/**
	 * @see wcf\system\exporter\IExporter::validateSelectedData()
	 */
	public function validateSelectedData(array $selectedData) {
		$this->selectedData = $selectedData;
	
		if (!count($this->selectedData)) {
			return false;
		}
		
		$supportedData = $this->getSupportedData();
		foreach ($this->selectedData as $name) {
			if (isset($supportedData[$name])) break;
			
			foreach ($supportedData as $key => $data) {
				if (in_array($name, $data)) {
					if (!in_array($key, $selectedData)) return false;
					
					break 2;
				}
			}
				
			return false;
		}
		
		return true;
	}
	
}
