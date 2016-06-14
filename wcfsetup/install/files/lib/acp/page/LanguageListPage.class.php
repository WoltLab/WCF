<?php
namespace wcf\acp\page;
use wcf\data\language\LanguageList;
use wcf\page\SortablePage;
use wcf\system\WCF;

/**
 * Shows a list of all installed languages.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 * 
 * @property	LanguageList	$objectList
 */
class LanguageListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.language.list';
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'languageName';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.language.canManageLanguage'];
	
	/**
	 * @inheritDoc
	 */
	public $objectListClassName = LanguageList::class;
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['languageID', 'languageCode', 'languageName', 'users', 'variables', 'customVariables'];
	
	/**
	 * @inheritDoc
	 */
	public function initObjectList() {
		parent::initObjectList();
		
		$this->objectList->sqlSelects = "(SELECT COUNT(*) FROM wcf".WCF_N."_user user WHERE languageID = language.languageID) AS users,";
		$this->objectList->sqlSelects .= "(SELECT COUNT(*) FROM wcf".WCF_N."_language_item WHERE languageID = language.languageID) AS variables,";
		$this->objectList->sqlSelects .= "(SELECT COUNT(*) FROM wcf".WCF_N."_language_item WHERE languageID = language.languageID AND languageCustomItemValue IS NOT NULL) AS customVariables";
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'languages' => $this->objectList->getObjects()
		]);
	}
}
