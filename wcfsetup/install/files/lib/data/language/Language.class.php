<?php
namespace wcf\data\language;
use wcf\data\DatabaseObject;
use wcf\system\WCF;
use wcf\system\language\LanguageFactory;

/**
 * Represents a language.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.language
 * @category 	Community Framework
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
	 * Returns the name of this language in the language of the active user.
	 * 
	 * @return	string
	 */
	public function __toString() {
		return WCF::getLanguage()->get('wcf.global.language.'.$this->languageCode);
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
	 * @return	string
	 */
	public function get($item) {
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
		return $item;
	}
	
	/**
	 * Executes template scripting in a language variable.
	 *
	 * @param 	string 		$item
	 * @param 	array 		$variables 
	 * @return 	string 		result
	 */
	public function getDynamicVariable($item, array $variables = array()) {
		$staticItem = $this->get($item);
		
		if (isset($this->dynamicItems[$this->languageID][$item])) {
			return WCF::getTPL()->fetchString($this->dynamicItems[$this->languageID][$item], $variables);
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
		$filename = WCF_DIR.'language/'.$this->packageID.'_'.$this->languageID.'_'.$category.'.php';
		if (!@file_exists($filename)) { 
			if ($this->editor === null) {
				$this->editor = new LanguageEditor($this);
			}
			
			// rebuild language file
			$languageCategory = LanguageFactory::getInstance()->getCategory($category);
			$this->editor->updateCategory(array($languageCategory->languageCategoryID), array($this->packageID));
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
}
