<?php
namespace wcf\acp\page;
use wcf\data\style\StyleList;
use wcf\page\MultipleLinkPage;

/**
 * Shows the style list page.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category	Community Framework
 * 
 * @property	StyleList	$objectList
 */
class StyleListPage extends MultipleLinkPage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.style.list';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.style.canManageStyle'];
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = StyleList::class;
	
	/**
	 * @inheritDoc
	 */
	public $sortField = 'style.isDefault DESC, style.styleName';
	
	/**
	 * @inheritDoc
	 */
	public $sortOrder = 'ASC';
	
	/**
	 * @inheritDoc
	 */
	public function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->sqlSelects = "(SELECT COUNT(*) FROM wcf".WCF_N."_user WHERE styleID = style.styleID) AS users";
	}
}
