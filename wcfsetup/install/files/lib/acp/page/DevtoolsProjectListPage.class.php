<?php
namespace wcf\acp\page;
use wcf\data\devtools\project\DevtoolsProjectList;
use wcf\page\SortablePage;

/**
 * Shows a list of devtools projects.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 * @since	3.1
 * 
 * @property	DevtoolsProjectList	$objectList
 */
class DevtoolsProjectListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.devtools.project.list';
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'name';
	
	/**
	 * @inheritDoc
	 */
	public $itemsPerPage = PHP_INT_MAX;
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = DevtoolsProjectList::class;
	
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['ENABLE_DEVELOPER_TOOLS'];
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.configuration.package.canInstallPackage'];
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['projectID', 'name', 'path'];
}
