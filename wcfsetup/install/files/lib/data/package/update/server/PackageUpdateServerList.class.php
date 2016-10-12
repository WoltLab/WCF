<?php
namespace wcf\data\package\update\server;
use wcf\data\DatabaseObjectList;

/**
 * Represents a list of package update servers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Package\Update\Server
 *
 * @method	PackageUpdateServer		current()
 * @method	PackageUpdateServer[]		getObjects()
 * @method	PackageUpdateServer|null	search($objectID)
 * @property	PackageUpdateServer[]		$objects
 */
class PackageUpdateServerList extends DatabaseObjectList {
	/**
	 * @inheritDoc
	 */
	public $className = PackageUpdateServer::class;
	
	/**
	 * @inheritDoc
	 */
	public function readObjects() {
		if (!empty($this->sqlSelects)) $this->sqlSelects .= ',';
		$this->sqlSelects .= "(SELECT COUNT(*) FROM wcf".WCF_N."_package_update WHERE packageUpdateServerID = ".$this->getDatabaseTableAlias().".packageUpdateServerID) AS packages";
		
		parent::readObjects();
	}
}
