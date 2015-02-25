<?php
namespace wcf\acp\page;
use wcf\data\language\category\LanguageCategoryList;
use wcf\data\language\item\LanguageItemList;
use wcf\page\AbstractPage;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Shows a list of language items.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	acp.page
 * @category	Community Framework
 */
class LanguageItemListPage extends AbstractPage {
	/**
	 * @see	\wcf\page\AbstractPage::$activeMenuItem
	 */
	public $activeMenuItem = 'wcf.acp.menu.link.language.item.list';
	
	/**
	 * number of matching phrases
	 * @var	integer
	 */
	public $count = 0;
	
	/**
	 * @see	\wcf\page\AbstractPage::$neededPermissions
	 */
	public $neededPermissions = array('admin.language.canManageLanguage');
	
	/**
	 * language item list
	 * @var	\wcf\data\language\item\LanguageItemList
	 */
	public $languageItemList = null;
	
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
	 * available languages
	 * @var	array
	 */
	public $availableLanguages = array();
	
	/**
	 * available language categories
	 * @var	array
	 */
	public $availableLanguageCategories = array();
	
	/**
	 * current page no
	 * @var	integer
	 */
	public $pageNo = 1;
	
	/**
	 * @see	\wcf\page\IPage::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_REQUEST['id'])) $this->languageID = intval($_REQUEST['id']);
		if (isset($_REQUEST['languageCategoryID'])) $this->languageCategoryID = intval($_REQUEST['languageCategoryID']);
		if (isset($_REQUEST['languageItem'])) $this->languageItem = StringUtil::trim($_REQUEST['languageItem']);
		if (isset($_REQUEST['languageItemValue'])) $this->languageItemValue = $_REQUEST['languageItemValue'];
		if (!empty($_REQUEST['hasCustomValue'])) $this->hasCustomValue = 1;
		if (isset($_REQUEST['pageNo'])) $this->pageNo = intval($_REQUEST['pageNo']);
	}
	
	/**
	 * @see	\wcf\page\IPage::readData()
	 */
	public function readData() {
		parent::readData();
		
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
		
		// get items
		$this->languageItemList = new LanguageItemList();
		$this->languageItemList->getConditionBuilder()->add('languageID = ?', array($this->languageID));
		if ($this->languageCategoryID) $this->languageItemList->getConditionBuilder()->add('languageCategoryID = ?', array($this->languageCategoryID));
		if ($this->languageItem) $this->languageItemList->getConditionBuilder()->add('languageItem LIKE ?', array('%'.$this->languageItem.'%'));
		if ($this->languageItemValue) $this->languageItemList->getConditionBuilder()->add('((languageUseCustomValue = 0 AND languageItemValue LIKE ?) OR languageCustomItemValue LIKE ?)', array('%'.$this->languageItemValue.'%', '%'.$this->languageItemValue.'%'));
		if ($this->hasCustomValue) $this->languageItemList->getConditionBuilder()->add("languageCustomItemValue IS NOT NULL");
		$this->languageItemList->sqlLimit = 100;
		
		if (!empty($_POST)) {
			$this->count = $this->languageItemList->countObjects();
			$maxPages = ceil($this->count / 100);
			$this->pageNo = max(min($this->pageNo, $maxPages), 1);
			
			if ($this->pageNo > 1) {
				$this->languageItemList->sqlOffset = ($this->pageNo - 1) * 100;
			}
		}
		
		$this->languageItemList->readObjects();
	}
	
	/**
	 * @see	\wcf\page\IPage::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(
			'objects' => $this->languageItemList,
			'count' => $this->count,
			'pageNo' => $this->pageNo,
			'languageID' => $this->languageID,
			'languageCategoryID' => $this->languageCategoryID,
			'languageItem' => $this->languageItem,
			'languageItemValue' => $this->languageItemValue,
			'hasCustomValue' => $this->hasCustomValue,
			'availableLanguages' => $this->availableLanguages,
			'availableLanguageCategories' => $this->availableLanguageCategories
		));
	}
}
