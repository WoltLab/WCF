<?php
namespace wcf\system\application;
use wcf\data\package\PackageCache;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Abstract implementation of a WoltLab Suite application.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Application
 */
abstract class AbstractApplication extends SingletonFactory implements IApplication {
	/**
	 * application's abbreviation
	 * @var	string
	 */
	protected $abbreviation = '';
	
	/**
	 * evaluation end date, `0` to disable
	 * @var int
	 */
	protected $evaluationEndDate = 0;
	
	/**
	 * WoltLab Plugin-Store file id
	 * @var int
	 */
	protected $evaluationPluginStoreID = 0;
	
	/**
	 * true, if current application is active (directly invoked, not dependent)
	 * @var	bool
	 */
	protected $isActiveApplication = false;
	
	/**
	 * application's package id
	 * @var	int
	 */
	protected $packageID = 0;
	
	/**
	 * qualified name of application's primary controller
	 * @var	string
	 */
	protected $primaryController = '';
	
	/**
	 * @inheritDoc
	 */
	protected final function init() {
		if (empty($this->abbreviation)) {
			$classParts = explode('\\', get_called_class());
			$this->abbreviation = $classParts[0];
		}
		else if ($this->abbreviation == 'wcf') {
			throw new SystemException("Unable to determine application, abbreviation is missing");
		}
		
		$application = ApplicationHandler::getInstance()->getApplication($this->abbreviation);
		if ($application === null) {
			throw new SystemException("Unable to determine application, abbreviation is unknown");
		}
		
		$this->packageID = $application->packageID;
		
		$this->rebuildActiveApplication();
	}
	
	/**
	 * @inheritDoc
	 */
	public function __run() {
		// does nothing
	}
	
	/**
	 * @inheritDoc
	 */
	public function isActiveApplication() {
		return $this->isActiveApplication;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEvaluationEndDate() {
		return $this->evaluationEndDate;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getEvaluationPluginStoreID() {
		return $this->evaluationPluginStoreID;
	}
	
	/**
	 * Returns application package.
	 * 
	 * @return	\wcf\data\package\Package
	 */
	public function getPackage() {
		return PackageCache::getInstance()->getPackage($this->packageID);
	}
	
	/**
	 * @inheritDoc
	 */
	public function getPrimaryController() {
		return $this->primaryController;
	}
	
	/**
	 * @since 5.2
	 */
	public function rebuildActiveApplication() {
		$this->isActiveApplication = ($this->packageID == ApplicationHandler::getInstance()->getActiveApplication()->packageID);
	}
	
	/**
	 * @inheritDoc
	 */
	public static function __callStatic($method, array $arguments) {
		return call_user_func_array([WCF::class, $method], $arguments);
	}
}
