<?php
namespace wcf\acp\page;
use wcf\page\MultipleLinkPage;

/**
 * Shows the style list page.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category	Community Framework
 */
class StyleListPage extends MultipleLinkPage {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.style.list';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.style.canManageStyle');
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$objectListClassName
	 */
	public $objectListClassName = 'wcf\data\style\StyleList';
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$sortField
	 */
	public $sortField = 'style.isDefault DESC, style.styleName';
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$sortOrder
	 */
	public $sortOrder = 'ASC';
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::initObjectList()
	 */
	public function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->sqlSelects = "(SELECT COUNT(*) FROM wcf".WCF_N."_user WHERE styleID = style.styleID) AS users";
	}
}
