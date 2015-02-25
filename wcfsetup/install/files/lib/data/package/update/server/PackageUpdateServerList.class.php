<?php
namespace wcf\data\package\update\server;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of package update servers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.package.update.server
 * @category	Community Framework
 */
class PackageUpdateServerList extends DatabaseObjectList {
	/**
	 * @see	\wcf\data\DatabaseObjectList::$className
	 */
	public $className = 'wcf\data\package\update\server\PackageUpdateServer';
	
	/**
	 * @see	\wcf\data\DatabaseObjectList::readObjects()
	 */
	public function readObjects() {
		if (!empty($this->sqlSelects)) $this->sqlSelects .= ',';
		$this->sqlSelects .= "(SELECT COUNT(*) FROM wcf".WCF_N."_package_update WHERE packageUpdateServerID = ".$this->getDatabaseTableAlias().".packageUpdateServerID) AS packages";
		
		parent::readObjects();
	}
}
