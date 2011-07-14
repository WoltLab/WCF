<?php
namespace wcf\acp\page;
use wcf\page\SortablePage;
use wcf\system\menu\acp\ACPMenu;
use wcf\system\WCF;

/**
 * Shows information about available language servers.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category 	Community Framework
 */
class LanguageServerListPage extends SortablePage {
	// system
	public $templateName = 'languageServerList';
	public $neededPermissions = array('admin.language.canEditServer');
	public $defaultSortField = 'serverURL';
	
	/**
	 * @see	wcf\page\MultipleLinkPage::$objectListClassName
	 */	
	public $objectListClassName = 'wcf\data\language\server\LanguageServerList';
	
	/**
	 * @see wcf\page\Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'languageServers' => $this->objectList->getObjects()
		));
	}
	
	/**
	 * @see wcf\page\Page::show()
	 */
	public function show() {
		// enable menu item
		ACPMenu::getInstance()->setActiveMenuItem('wcf.acp.menu.link.language.server.list');
		
		parent::show();
	}
	
	/**
	 * @see wcf\page\SortablePage::validateSortField()
	 */
	public function validateSortField() {
		parent::validateSortField();
		
		switch ($this->sortField) {
			case 'languageServerID':
			case 'serverURL': break;
			default: $this->sortField = $this->defaultSortField;
		}
	}
}
?>
