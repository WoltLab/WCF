<?php
namespace wcf\acp\page;
use wcf\data\trophy\TrophyList;
use wcf\page\MultipleLinkPage;

/**
 * Trophy list page.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 * @since	3.1
 */
class TrophyListPage extends MultipleLinkPage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.trophy.list';
	
	/**
	 * @inheritDoc
	 */
	public $neededModules = ['MODULE_TROPHY'];
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.trophy.canManageTrophy'];
	
	/**
	 * @inheritDoc
	 */
	public $sortField = 'trophy.showOrder';
	
	/**
	 * @inheritDoc
	 */
	public $sortOrder = 'ASC';
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = TrophyList::class;
}
