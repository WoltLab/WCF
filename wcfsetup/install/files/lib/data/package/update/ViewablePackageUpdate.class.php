<?php
namespace wcf\data\package\update;
use wcf\data\package\update\version\PackageUpdateVersion;
use wcf\data\DatabaseObjectDecorator;

/**
 * Provides a viewable package update object.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.package.update
 * @category	Community Framework
 */
class ViewablePackageUpdate extends DatabaseObjectDecorator {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\package\update\PackageUpdate';
	
	/**
	 * latest accessible package update version object
	 * @var	\wcf\data\package\update\version\PackageUpdateVersion
	 */
	protected $accessibleVersion = null;
	
	/**
	 * latest package update version object
	 * @var	\wcf\data\package\update\version\PackageUpdateVersion
	 */
	protected $latestVersion = null;
	
	/**
	 * Sets latest accessible package update version object.
	 * 
	 * @param	\wcf\data\package\update\version\PackageUpdateVersion	$latestVersion
	 */
	public function setAccessibleVersion(PackageUpdateVersion $latestVersion) {
		$this->accessibleVersion = $latestVersion;
	}
	
	/**
	 * Sets latest package update version object.
	 * 
	 * @param	\wcf\data\package\update\version\PackageUpdateVersion	$latestVersion
	 */
	public function setLatestVersion(PackageUpdateVersion $latestVersion) {
		$this->latestVersion = $latestVersion;
	}
	
	/**
	 * Returns latest accessible package update version object.
	 * 
	 * @return	\wcf\data\package\update\version\PackageUpdateVersion
	 */
	public function getAccessibleVersion() {
		return $this->accessibleVersion;
	}
	
	/**
	 * Returns latest package update version object.
	 * 
	 * @return	\wcf\data\package\update\version\PackageUpdateVersion
	 */
	public function getLatestVersion() {
		return $this->latestVersion;
	}
}
