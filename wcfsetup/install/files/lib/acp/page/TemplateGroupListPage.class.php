<?php
namespace wcf\acp\page;
use wcf\data\template\group\TemplateGroupList;
use wcf\page\SortablePage;

/**
 * Shows a list of installed template groups.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 * 
 * @property	TemplateGroupList	$objectList
 */
class TemplateGroupListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.template.group.list';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.template.canManageTemplate'];
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'templateGroupName';
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = TemplateGroupList::class;
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['templateGroupID', 'templateGroupName', 'templateGroupFolderName', 'templates'];
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->sqlSelects = "(SELECT COUNT(*) FROM wcf".WCF_N."_template WHERE templateGroupID = template_group.templateGroupID) AS templates";
	}
}
