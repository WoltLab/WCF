<?php
namespace wcf\acp\page;
use wcf\data\package\update\server\PackageUpdateServerList;
use wcf\page\SortablePage;

/**
 * Shows information about available update package servers.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 * 
 * @property	PackageUpdateServerList		$objectList
 */
class PackageUpdateServerListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.package.server.list';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.configuration.package.canEditServer'];
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'serverURL';
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['packageUpdateServerID', 'serverURL', 'status', 'errorMessage', 'lastUpdateTime', 'packages'];
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = PackageUpdateServerList::class;
	
	/**
	 * @inheritDoc
	 */
	public function readObjects() {
		$this->sqlOrderBy = ($this->sortField != 'packages' ? 'package_update_server.' : '') . $this->sortField.' '.$this->sortOrder;
		
		parent::readObjects();
	}
}
