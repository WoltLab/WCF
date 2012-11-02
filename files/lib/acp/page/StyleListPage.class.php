<?php
namespace wcf\acp\page;
use wcf\page\MultipleLinkPage;
use wcf\system\menu\acp\ACPMenu;

/**
 * Shows the style list page.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf.acp.style
 * @subpackage	acp.page
 * @category 	Community Framework
 */
class StyleListPage extends MultipleLinkPage {
	/**
	 * @see wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.style.canEditStyle', 'admin.style.canDeleteStyle');
	
	/**
	 * @see	wcf\page\MultipleLinkPage::$objectListClassName
	 */
	public $objectListClassName = 'wcf\data\style\StyleList';
	
	/**
	 * @see	wcf\page\MultipleLinkPage::$sortField
	 */
	public $sortField = 'style.styleName';
	
	/**
	 * @see	wcf\page\MultipleLinkPage::$sortOrder
	 */
	public $sortOrder = 'ASC';
	
	/**
	 * @see wcf\page\MultipleLinkPage::initObjectList()
	 */
	public function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->sqlSelects = "(SELECT COUNT(*) FROM wcf".WCF_N."_user WHERE styleID = style.styleID) AS users";
	}
	
	/**
	 * @see wcf\page\IPage::show()
	 */
	public function show() {
		// set active menu item.
		ACPMenu::getInstance()->setActiveMenuItem('wcf.acp.menu.link.style.list');
	
		parent::show();
	}
}
