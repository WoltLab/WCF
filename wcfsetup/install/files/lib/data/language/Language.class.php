<?php
namespace wcf\data\language;
use wcf\data\DatabaseObject;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Represents a language.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Language
 * 
 * @property-read	integer		$languageID		unique id of the language
 * @property-read	string		$languageCode		code of the language according to ISO 639-1
 * @property-read	string		$languageName		name of the language within the language itself
 * @property-read	string		$countryCode		code of the country using the language according to ISO 3166-1, used to determine the language's country flag  
 * @property-read	integer		$isDefault		is `1` if the language is the default language, otherwise `0`
 * @property-read	integer		$hasContent		is `1` if the language can be selected when creating language-specific content, otherwise `0`
 * @property-read	integer		$isDisabled		is `1` if the language is disabled and thus not selectable, otherwise `0`
 */
class Language extends DatabaseObject {
	/**
	 * list of language items
	 * @var	string[]
	 */
	protected $items = [];
	
	/**
	 * list of dynamic language items
	 * @var	string[]
	 */
	protected $dynamicItems = [];
	
	/**
	 * instance of LanguageEditor
	 * @var	LanguageEditor
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
			if (count($explodedItem) < 3) {
				return $item;
			}
			
			// attempt to load the most specific category
			$this->loadCategory($explodedItem[0].'.'.$explodedItem[1].'.'.$explodedItem[2]);
			if (!isset($this->items[$item])) {
				$this->loadCategory($explodedItem[0].'.'.$explodedItem[1]);
			}
		}
		
		// return language variable
		if (isset($this->items[$item])) {
			return $this->items[$item];
		}
		
		// do not output value if there was no match and the item looks like a valid language item
		if ($optional && preg_match('~^([a-zA-Z0-9-_]+\.)+[a-zA-Z0-9-_]+$~', $item)) {
			return '';
		}
		
		// return plain input
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
	public function getDynamicVariable($item, array $variables = [], $optional = false) {
		$staticItem = $this->get($item, $optional);
		if (!$staticItem) return '';
		
		if (isset($this->dynamicItems[$item])) {
			// assign active language
			$variables['__language'] = $this;
			
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
			if ($languageCategory === null) {
				return false;
			}
			
			$this->editor->updateCategory($languageCategory);
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
	 * @return	Language[]
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
