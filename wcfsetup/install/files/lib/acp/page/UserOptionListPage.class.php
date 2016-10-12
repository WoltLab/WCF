<?php
namespace wcf\acp\page;
use wcf\data\user\option\UserOptionList;
use wcf\page\SortablePage;

/**
 * Shows a list of installed user options.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 * 
 * @property	UserOptionList		$objectList
 */
class UserOptionListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.user.option.list';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.user.canManageUserOption'];
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'showOrder';
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = UserOptionList::class;
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['optionID', 'optionName', 'categoryName', 'optionType', 'showOrder'];
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->getConditionBuilder()->add("option_table.categoryName IN (SELECT categoryName FROM wcf".WCF_N."_user_option_category WHERE parentCategoryName = ?)", ['profile']);
	}
}
