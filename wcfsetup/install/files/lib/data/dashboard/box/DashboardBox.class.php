<?php
namespace wcf\data\dashboard\box;
use wcf\data\package\PackageCache;
use wcf\data\DatabaseObject;

/**
 * Represents a dashboard box.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.dashboard.box
 * @category	Community Framework
 */
class DashboardBox extends DatabaseObject {
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'dashboard_box';
	
	/**
	 * @see	\wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'boxID';
	
	/**
	 * Returns the owner of this dashboard box.
	 * 
	 * @return	\wcf\data\package\Package
	 */
	public function getPackage() {
		return PackageCache::getInstance()->getPackage($this->packageID);
	}
}
