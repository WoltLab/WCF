<?php
namespace wcf\acp\page;
use wcf\data\devtools\missing\language\item\DevtoolsMissingLanguageItemList;
use wcf\page\SortablePage;

/**
 * Shows the list of missing language item log entries.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 * @since	5.3
 */
class DevtoolsMissingLanguageItemListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.devtools.missingLanguageItem.list';
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'lastTime';
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortOrder = 'DESC';
	
	/**
	 * @inheritDoc
	 */
	public $itemsPerPage = 50;
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = DevtoolsMissingLanguageItemList::class;
	
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
	public $validSortFields = ['itemID', 'languageID', 'languageItem', 'lastTime'];
}
