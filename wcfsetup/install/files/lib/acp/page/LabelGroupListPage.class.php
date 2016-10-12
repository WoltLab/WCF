<?php
namespace wcf\acp\page;
use wcf\data\label\group\LabelGroupList;
use wcf\page\SortablePage;

/**
 * Lists available label groups.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 * 
 * @property	LabelGroupList		$objectList
 */
class LabelGroupListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.label.group.list';
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'showOrder';
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['groupID', 'groupName', 'groupDescription', 'showOrder'];
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.content.label.canManageLabel'];
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = LabelGroupList::class;
}
