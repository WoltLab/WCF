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
 * @copyright	2001-2016 WoltLab GmbH
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
	 * true, if current application is active (directly invoked, not dependent)
	 * @var	boolean
	 */
	protected $isActiveApplication = false;
	
	/**
	 * application's package id
	 * @var	integer
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
		
		// check if current application is active (directly invoked, not dependent)
		if ($application->packageID == ApplicationHandler::getInstance()->getActiveApplication()->packageID) {
			$this->isActiveApplication = true;
		}
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
	 * @inheritDoc
	 */
	public static function __callStatic($method, array $arguments) {
		return call_user_func_array([WCF::class, $method], $arguments);
	}
}
