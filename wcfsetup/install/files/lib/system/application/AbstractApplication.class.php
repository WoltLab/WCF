<?php
namespace wcf\system\application;
use wcf\data\package\PackageCache;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;

/**
 * Abstract implementation of a community framework application.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.application
 * @category	Community Framework
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
	 * @see	\wcf\system\SingletonFactory::init()
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
	 * @see	\wcf\system\application\IApplication::__run()
	 */
	public function __run() {
		// does nothing
	}
	
	/**
	 * @see	\wcf\system\application\IApplication::isActiveApplication()
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
	 * @see	\wcf\system\application\IApplication::getPrimaryController()
	 */
	public function getPrimaryController() {
		return $this->primaryController;
	}
	
	/**
	 * @see	\wcf\system\application\IApplication::__callStatic()
	 */
	public static function __callStatic($method, array $arguments) {
		return call_user_func_array(array('wcf\system\WCF', $method), $arguments);
	}
}
