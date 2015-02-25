<?php
namespace wcf\acp\page;
use wcf\page\SortablePage;

/**
 * Shows a list of user option categories.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category	Community Framework
 */
class UserOptionCategoryListPage extends SortablePage {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.user.option.category.list';
	
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
	public $objectListClassName = 'wcf\data\user\option\category\UserOptionCategoryList';
	
	/**
	 * @see	\wcf\page\SortablePage::$defaultSortField
	 */
	public $validSortFields = array('categoryID', 'categoryName', 'showOrder', 'userOptions');
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::initObjectList
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->sqlSelects = "(SELECT COUNT(*) FROM wcf".WCF_N."_user_option WHERE categoryName = user_option_category.categoryName) AS userOptions";
		$this->objectList->getConditionBuilder()->add('user_option_category.parentCategoryName = ?', array('profile'));
	}
}
