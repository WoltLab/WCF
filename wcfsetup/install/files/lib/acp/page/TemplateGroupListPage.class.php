<?php
namespace wcf\acp\page;
use wcf\page\SortablePage;

/**
 * Shows a list of installed template groups.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category	Community Framework
 */
class TemplateGroupListPage extends SortablePage {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.template.group.list';
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.template.canManageTemplate');
	
	/**
	 * @see	\wcf\page\SortablePage::$defaultSortField
	 */
	public $defaultSortField = 'templateGroupName';
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::$objectListClassName
	 */
	public $objectListClassName = 'wcf\data\template\group\TemplateGroupList';
	
	/**
	 * @see	\wcf\page\SortablePage::$defaultSortField
	 */
	public $validSortFields = array('templateGroupID', 'templateGroupName', 'templateGroupFolderName', 'templates');
	
	/**
	 * @see	\wcf\page\MultipleLinkPage::initObjectList()
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->sqlSelects = "(SELECT COUNT(*) FROM wcf".WCF_N."_template WHERE templateGroupID = template_group.templateGroupID) AS templates";
	}
}
