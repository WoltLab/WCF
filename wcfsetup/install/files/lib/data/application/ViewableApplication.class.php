<?php
namespace wcf\data\application;
use wcf\data\package\PackageCache;
use wcf\data\DatabaseObjectDecorator;

/**
 * Represents a viewable application.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.application
 * @category	Community Framework
 */
class ViewableApplication extends DatabaseObjectDecorator {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\application\Application';
	
	/**
	 * package object
	 * @var	\wcf\data\package\Package
	 */
	protected $package = null;
	
	/**
	 * Returns package object.
	 * 
	 * @return	\wcf\data\package\Package
	 */
	public function getPackage() {
		if ($this->package === null) {
			$this->package = PackageCache::getInstance()->getPackage($this->packageID);
		}
		
		return $this->package;
	}
}
