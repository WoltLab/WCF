<?php
namespace wcf\acp\page;
use wcf\page\SortablePage;

/**
 * Shows a list of installed user options.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category	Community Framework
 */
class UserOptionListPage extends SortablePage {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.user.option.list';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.user.canManageUserOption');
	
	/**
	 * @see	\wcf\page\SortablePage::$defaultSortField
	 */
	public $defaultSortField = 'showOrder';
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$objectListClassName
	 */
	public $objectListClassName = 'wcf\data\user\option\UserOptionList';
	
	/**
	 * @see	\wcf\page\SortablePage::$defaultSortField
	 */
	public $validSortFields = array('optionID', 'optionName', 'categoryName', 'optionType', 'showOrder');
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::initObjectList
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->getConditionBuilder()->add("option_table.categoryName IN (SELECT categoryName FROM wcf".WCF_N."_user_option_category WHERE parentCategoryName = ?)", array('profile'));
	}
}
