<?php
namespace wcf\data\language;
use wcf\data\DatabaseObject;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Represents a language.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.language
 * @category	Community Framework
 */
class Language extends DatabaseObject {
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableName
	 */
	protected static $databaseTableName = 'language';
	
	/**
	 * @see	wcf\data\DatabaseObject::$databaseTableIndexName
	 */
	protected static $databaseTableIndexName = 'languageID';
	
	/**
	 * list of language items
	 * @var	array<string>
	 */
	protected $items = array();
	
	/**
	 * list of dynamic language items
	 * @var	array<string>
	 */
	protected $dynamicItems = array();
	
	/**
	 * instance of LanguageEditor
	 * @var	wcf\data\language\LanguageEditor
	 */
	private $editor = null;
	
	/**
	 * id of the active package
	 * @var	integer
	 */
	public $packageID = PACKAGE_ID;
	
	/**
	 * Returns the name of this language.
	 * 
	 * @return	string
	 */
	public function __toString() {
		return $this->languageName;
	}
	
	/**
	 * Returns the fixed language code of this language.
	 * 
	 * @return	string
	 */
	public function getFixedLanguageCode() {
		return LanguageFactory::fixLanguageCode($this->languageCode);
	}
	
	/**
	 * Returns the page direction of this language.
	 * 
	 * @return	string
	 */
	public function getPageDirection() {
		return $this->get('wcf.global.pageDirection');
	}
	
	/**
	 * Returns a single language variable.
	 * 
	 * @param	string		$item
	 * @param	boolean		$optional
	 * @return	string
	 */
	public function get($item, $optional = false) {
		if (!isset($this->items[$item])) {
			// load category file
			$explodedItem = explode('.', $item);
			if (count($explodedItem) < 2) {
				return $item;
			}
			
			if (count($explodedItem) < 4 || !$this->loadCategory($explodedItem[0].'.'.$explodedItem[1].'.'.$explodedItem[2].'.'.$explodedItem[3])) {
				if (count($explodedItem) < 3 || !$this->loadCategory($explodedItem[0].'.'.$explodedItem[1].'.'.$explodedItem[2])) {
					$this->loadCategory($explodedItem[0].'.'.$explodedItem[1]);
				}
			}
		}
		
		// return language variable
		if (isset($this->items[$item])) {
			return $this->items[$item];
		}
		
		// return plain variable
		if ($optional) return '';
		return $item;
	}
	
	/**
	 * Executes template scripting in a language variable.
	 * 
	 * @param	string		$item
	 * @param	array		$variables
	 * @param	boolean		$optional
	 * @return	string		result
	 */
	public function getDynamicVariable($item, array $variables = array(), $optional = false) {
		$staticItem = $this->get($item, $optional);
		if (!$staticItem) return '';
		
		if (isset($this->dynamicItems[$item])) {
			return WCF::getTPL()->fetchString($this->dynamicItems[$item], $variables);
		}
		
		return $staticItem;
	}
	
	/**
	 * Loads category files.
	 * 
	 * @param	string		$category
	 * @return	boolean
	 */
	protected function loadCategory($category) {
		if (!LanguageFactory::getInstance()->isValidCategory($category)) {
			return false;
		}
		
		// search language file
		$filename = WCF_DIR.'language/'.$this->languageID.'_'.$category.'.php';
		if (!@file_exists($filename)) { 
			if ($this->editor === null) {
				$this->editor = new LanguageEditor($this);
			}
			
			// rebuild language file
			$languageCategory = LanguageFactory::getInstance()->getCategory($category);
			$this->editor->updateCategory(array($languageCategory->languageCategoryID));
		}
		
		// include language file
		@include_once($filename);
		return true;
	}
	
	/**
	 * Returns true if given items includes template scripting.
	 * 
	 * @param	string		$item
	 * @return	boolean
	 */
	public function isDynamicItem($item) {
		if (isset($this->dynamicItems[$item])) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Sets the local language.
	 * Recall this function after language changed.
	 * 
	 * @param	integer		$languageID
	 */
	public function setLocale() {
		// set locale for string comparison, character classification and conversion
		setlocale(LC_COLLATE, $this->get('wcf.global.locale.unix').'.UTF-8', $this->get('wcf.global.locale.unix'), $this->get('wcf.global.locale.win'));
		setlocale(LC_CTYPE, $this->get('wcf.global.locale.unix').'.UTF-8', $this->get('wcf.global.locale.unix'), $this->get('wcf.global.locale.win'));
	}
	
	/**
	 * Returns language icon path.
	 * 
	 * @return	string
	 */
	public function getIconPath() {
		return WCF::getPath() . 'icon/flag/'.$this->countryCode.'.svg';
	}
	
	/**
	 * Returns a list of available languages.
	 * 
	 * @return	array<wcf\data\language\Language>
	 */
	public function getLanguages() {
		return LanguageFactory::getInstance()->getLanguages();
	}
	
	/**
	 * Sets the package id when a language object is unserialized.
	 */
	public function __wakeup() {
		$this->packageID = PACKAGE_ID;
	}
}
