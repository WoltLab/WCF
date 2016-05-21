<?php
namespace wcf\acp\page;
use wcf\page\SortablePage;

/**
 * Lists available label groups.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category	Community Framework
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
	public $objectListClassName = 'wcf\data\label\group\LabelGroupList';
}
