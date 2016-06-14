<?php
namespace wcf\data\application;
use wcf\data\package\Package;
use wcf\data\package\PackageCache;
use wcf\data\DatabaseObjectDecorator;

/**
 * Represents a viewable application.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Application
 * 
 * @method	Application		getDecoratedObject()
 * @mixin	Application
 */
class ViewableApplication extends DatabaseObjectDecorator {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Application::class;
	
	/**
	 * package object
	 * @var	Package
	 */
	protected $package = null;
	
	/**
	 * Returns package object.
	 * 
	 * @return	Package
	 */
	public function getPackage() {
		if ($this->package === null) {
			$this->package = PackageCache::getInstance()->getPackage($this->packageID);
		}
		
		return $this->package;
	}
}
