<?php
namespace wcf\acp\page;
use wcf\data\language\category\LanguageCategoryList;
use wcf\data\language\item\LanguageItemList;
use wcf\page\SortablePage;
use wcf\system\language\LanguageFactory;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows a list of language items.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Acp\Page
 */
class LanguageItemListPage extends SortablePage {
	/**
	 * @inheritDoc
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.language.item.list';
	
	/**
	 * @inheritDoc
	 */
	public $neededPermissions = ['admin.language.canManageLanguage'];
	
	/**
	 * @inheritDoc
	 */
	public $itemsPerPage = 100;
	
	/**
	 * @inheritDoc
	 */
	public $validSortFields = ['languageItem'];
	
	/**
	 * @inheritDoc
	 */
	public $defaultSortField = 'languageItem';
	
	/**
	 * language id
	 * @var	integer
	 */
	public $languageID = 0;
	
	/**
	 * language category id
	 * @var	integer
	 */
	public $languageCategoryID = 0;
	
	/**
	 * language item name
	 * @var	string
	 */
	public $languageItem = '';
	
	/**
	 * language item value
	 * @var	string
	 */
	public $languageItemValue = '';
	
	/**
	 * search for custom values
	 * @var	boolean
	 */
	public $hasCustomValue = 0;
	
	/**
	 * search for disabled custom values
	 * @var	boolean
	 */
	public $hasDisabledCustomValue = 0;
	
	/**
	 * search for disabled custom values that have been automatically disabled in the past 7 days
	 * @var	boolean
	 */
	public $hasRecentlyDisabledCustomValue = 0;
	
	/**
	 * is `1` if only custom language items (created via ACP) should be shown
	 * @var	integer
	 * @since	3.2
	 */
	public $isCustomLanguageItem = 0;
	
	/**
	 * available languages
	 * @var	array
	 */
	public $availableLanguages = [];
	
	/**
	 * available language categories
	 * @var	array
	 */
	public $availableLanguageCategories = [];
	
	/**
	 * @inheritDoc
	 * @since	3.2
	 */
	public $forceCanonicalURL = true;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['languageID'])) $this->languageID = intval($_REQUEST['languageID']);
		if (isset($_REQUEST['languageCategoryID'])) $this->languageCategoryID = intval($_REQUEST['languageCategoryID']);
		if (isset($_REQUEST['languageItem'])) $this->languageItem = StringUtil::trim($_REQUEST['languageItem']);
		if (isset($_REQUEST['languageItemValue'])) $this->languageItemValue = $_REQUEST['languageItemValue'];
		if (!empty($_REQUEST['hasCustomValue'])) $this->hasCustomValue = 1;
		if (!empty($_REQUEST['hasDisabledCustomValue'])) $this->hasDisabledCustomValue = 1;
		if (!empty($_REQUEST['hasRecentlyDisabledCustomValue'])) $this->hasRecentlyDisabledCustomValue = 1;
		if (!empty($_REQUEST['isCustomLanguageItem'])) $this->isCustomLanguageItem = 1;
		
		$parameters = [];
		if ($this->languageID) $parameters['languageID'] = $this->languageID;
		if ($this->languageCategoryID) $parameters['languageCategoryID'] = $this->languageCategoryID;
		if ($this->languageItem) $parameters['languageItem'] = $this->languageItem;
		if ($this->languageItemValue) $parameters['languageItemValue'] = $this->languageItemValue;
		if ($this->hasCustomValue) $parameters['hasCustomValue'] = $this->hasCustomValue;
		if ($this->hasDisabledCustomValue) $parameters['hasDisabledCustomValue'] = $this->hasDisabledCustomValue;
		if ($this->hasRecentlyDisabledCustomValue) $parameters['hasRecentlyDisabledCustomValue'] = $this->hasRecentlyDisabledCustomValue;
		if ($this->isCustomLanguageItem) $parameters['isCustomLanguageItem'] = $this->isCustomLanguageItem;
		
		$this->canonicalURL = LinkHandler::getInstance()->getLink('LanguageItemList', $parameters);
	}
	
	/**
	 * @inheritDoc
	 */
	protected function initObjectList() {
		$this->objectList = new LanguageItemList();
		$this->objectList->getConditionBuilder()->add('languageID = ?', [$this->languageID]);
		if ($this->languageCategoryID) $this->objectList->getConditionBuilder()->add('languageCategoryID = ?', [$this->languageCategoryID]);
		if ($this->languageItem) $this->objectList->getConditionBuilder()->add('languageItem LIKE ?', ['%'.$this->languageItem.'%']);
		if ($this->languageItemValue) $this->objectList->getConditionBuilder()->add('((languageUseCustomValue = 0 AND languageItemValue LIKE ?) OR languageCustomItemValue LIKE ?)', ['%'.$this->languageItemValue.'%', '%'.$this->languageItemValue.'%']);
		if ($this->hasCustomValue || $this->hasDisabledCustomValue || $this->hasRecentlyDisabledCustomValue) $this->objectList->getConditionBuilder()->add("languageCustomItemValue IS NOT NULL");
		if ($this->hasDisabledCustomValue || $this->hasRecentlyDisabledCustomValue) $this->objectList->getConditionBuilder()->add("languageUseCustomValue = ?", [0]);
		if ($this->hasRecentlyDisabledCustomValue) $this->objectList->getConditionBuilder()->add("languageCustomItemDisableTime >= ?", [TIME_NOW - 86400 * 7]);
		if ($this->isCustomLanguageItem) $this->objectList->getConditionBuilder()->add("isCustomLanguageItem = ?", [1]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readData() {
		// get languages
		$this->availableLanguages = LanguageFactory::getInstance()->getLanguages();
		
		// get categories
		$languageCategoryList = new LanguageCategoryList();
		$languageCategoryList->readObjects();
		$this->availableLanguageCategories = $languageCategoryList->getObjects();
		
		// check parameters
		if (!isset($this->availableLanguages[$this->languageID])) {
			$this->languageID = key($this->availableLanguages);
		}
		if ($this->languageCategoryID && !isset($this->availableLanguageCategories[$this->languageCategoryID])) {
			$this->languageCategoryID = 0;
		}
		
		parent::readData();
	}
	
	/**
	 * @inheritDoc
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign([
			'languageID' => $this->languageID,
			'languageCategoryID' => $this->languageCategoryID,
			'languageItem' => $this->languageItem,
			'languageItemValue' => $this->languageItemValue,
			'hasCustomValue' => $this->hasCustomValue,
			'hasDisabledCustomValue' => $this->hasDisabledCustomValue,
			'hasRecentlyDisabledCustomValue' => $this->hasRecentlyDisabledCustomValue,
			'isCustomLanguageItem' => $this->isCustomLanguageItem,
			'availableLanguages' => $this->availableLanguages,
			'availableLanguageCategories' => $this->availableLanguageCategories
		]);
	}
}
