<?php
namespace wcf\system\language;
use wcf\data\language\Language;
use wcf\data\language\LanguageEditor;
use wcf\data\DatabaseObject;
use wcf\system\cache\CacheHandler;
use wcf\system\template\TemplateScriptingCompiler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles language related functions.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.language
 * @category	Community Framework
 */
class LanguageFactory extends SingletonFactory {
	/**
	 * language cache
	 * @var	array<array>
	 */
	protected $cache = null;
	
	/**
	 * initialized languages
	 * @var	array<wcf\data\language\Language>
	 */
	protected $languages = array();
	
	/**
	 * active template scripting compiler
	 * @var	wcf\system\template\TemplateScriptingCompiler
	 */
	protected $scriptingCompiler = null;
	
	/**
	 * @see	wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		$this->loadCache();
	}
	
	/**
	 * Returns a Language object for the language with the given id.
	 * 
	 * @param	integer		$languageID
	 * @return	wcf\data\language\Language
	 */
	public function getLanguage($languageID) {
		if (!isset($this->languages[$languageID])) {
			if (!isset($this->cache['languages'][$languageID])) {
				return null;
			}
			
			$this->languages[$languageID] = $this->cache['languages'][$languageID];
		}
		
		return $this->languages[$languageID];
	}
	
	/**
	 * Gets the preferred language of the current user.
	 * 
	 * @param	integer		$languageID
	 * @return	wcf\data\language\Language
	 */
	public function getUserLanguage($languageID = 0) {
		if ($languageID) {
			$language = $this->getLanguage($languageID);
			if ($language !== null) return $language;
		}
		
		$languageID = $this->findPreferredLanguage();
		return $this->getLanguage($languageID);
	}
	
	/**
	 * Returns the language with the given language code or null if no such
	 * language exists.
	 * 
	 * @param	string		$languageCode
	 * @return	wcf\data\language\Language
	 */
	public function getLanguageByCode($languageCode) {
		// called within WCFSetup
		if ($this->cache === false || empty($this->cache['codes'])) {
			$sql = "SELECT	languageID
				FROM	wcf".WCF_N."_language
				WHERE	languageCode = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($languageCode));
			$row = $statement->fetchArray();
			if (isset($row['languageID'])) return new Language($row['languageID']);
		}
		else if (isset($this->cache['codes'][$languageCode])) {
			return $this->getLanguage($this->cache['codes'][$languageCode]);
		}
		
		return null;
	}
	
	/**
	 * Returns true if the language category with the given name exists.
	 * 
	 * @param	string		$categoryName
	 * @return	boolean
	 */
	public function isValidCategory($categoryName) {
		return isset($this->cache['categories'][$categoryName]);
	}
	
	/**
	 * Returns the language category with the given name.
	 * 
	 * @param	string		$categoryName
	 * @return	wcf\data\language\category\LanguageCategory
	 */
	public function getCategory($categoryName) {
		if (isset($this->cache['categories'][$categoryName])) {
			return $this->cache['categories'][$categoryName];
		}
		
		return null;
	}
	
	/**
	 * Returns a list of available language categories.
	 * 
	 * @return	array<wcf\data\language\category\LanguageCategory>
	 */	
	public function getCategories() {
		return $this->cache['categories'];
	}
	
	/**
	 * Searches the preferred language of the current user.
	 */
	protected function findPreferredLanguage() {
		// get available language codes
		$availableLanguageCodes = array();
		foreach ($this->getLanguages(PACKAGE_ID) as $language) {
			$availableLanguageCodes[] = $language->languageCode;
		}
		
		// get default language
		$defaultLanguageCode = $this->cache['languages'][$this->cache['default']]->languageCode;
		
		// get preferred language
		$languageCode = self::getPreferredLanguage($availableLanguageCodes, $defaultLanguageCode);
		
		// get language id of preferred language
		foreach ($this->cache['languages'] as $key => $language) {
			if ($language->languageCode == $languageCode) {
				return $key;
			}
		}
	}
	
	/**
	 * Determines the preferred language of the current user.
	 * 
	 * @param	array		$availableLanguageCodes
	 * @param	string		$defaultLanguageCode
	 * @return	string
	 */
	public static function getPreferredLanguage($availableLanguageCodes, $defaultLanguageCode) {
		if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) && $_SERVER['HTTP_ACCEPT_LANGUAGE']) {
			$acceptedLanguages = explode(',', str_replace('_', '-', strtolower($_SERVER['HTTP_ACCEPT_LANGUAGE'])));
			foreach ($acceptedLanguages as $acceptedLanguage) {
				foreach ($availableLanguageCodes as $availableLanguageCode) {
					$fixedCode = strtolower(self::fixLanguageCode($availableLanguageCode));
					
					if ($fixedCode == $acceptedLanguage || $fixedCode == preg_replace('%^([a-z]{2}).*$%i', '$1', $acceptedLanguage)) {
						return $availableLanguageCode;
					}
				}
			}
		}
		
		return $defaultLanguageCode;
	}
	
	/**
	 * Returns the active scripting compiler object.
	 * 
	 * @return	wcf\system\template\TemplateScriptingCompiler
	 */
	public function getScriptingCompiler() {
		if ($this->scriptingCompiler === null) {
			$this->scriptingCompiler = new TemplateScriptingCompiler(WCF::getTPL());
		}
		
		return $this->scriptingCompiler;
	}
	
	/**
	 * Loads the language cache.
	 */
	protected function loadCache() {
		if (defined('WCF_N')) {
			CacheHandler::getInstance()->addResource(
				'languages',
				WCF_DIR.'cache/cache.languages.php',
				'wcf\system\cache\builder\LanguageCacheBuilder'
			);
			
			$this->cache = CacheHandler::getInstance()->get('languages');
		}
	}
	
	/**
	 * Clears languages cache.
	 */
	public function clearCache() {
		CacheHandler::getInstance()->clear(WCF_DIR.'cache/', 'cache.languages.php');
	}
	
	/**
	 * Removes additional language identifier from given language code.
	 * Converts e.g. 'de-informal' to 'de'.
	 * 
	 * @param	string		$languageCode
	 * @return	string		$languageCode
	 */
	public static function fixLanguageCode($languageCode) {
		return preg_replace('/-[a-z0-9]+/', '', $languageCode);
	}
	
	/**
	 * Returns the default language id
	 * 
	 * @return	integer
	 */
	public function getDefaultLanguageID() {
		return $this->cache['default'];
	}
	
	/**
	 * Returns all available languages for package with the given id.
	 * 
	 * @param	integer		$packageID
	 * @return	array<wcf\data\language\Language>
	 */
	public function getLanguages($packageID = PACKAGE_ID) {
		// get list of all available languages
		$availableLanguages = array();
		if (isset($this->cache['packages'][$packageID])) {
			foreach ($this->cache['packages'][$packageID] as $availableLanguageID) {
				$availableLanguages[$availableLanguageID] = $this->getLanguage($availableLanguageID);
			}
		}
		
		DatabaseObject::sort($availableLanguages, 'languageName');
		return $availableLanguages;
	}
	
	/**
	 * Returns all available content languages for given package.
	 * 
	 * @param	integer		$packageID
	 * @return	array<wcf\data\language\Language>
	 */
	public function getContentLanguages($packageID = PACKAGE_ID) {
		$availableLanguages = array();
		if (isset($this->cache['packages'][$packageID])) {
			foreach ($this->cache['packages'][$packageID] as $availableLanguageID) {
				if ($this->cache['languages'][$availableLanguageID]->hasContent) {
					$availableLanguages[$availableLanguageID] = $this->getLanguage($availableLanguageID);
				}
			}
		}
		
		DatabaseObject::sort($availableLanguages, 'languageName');
		return $availableLanguages;
	}
	
	/**
	 * Makes given language the default language.
	 * 
	 * @param	integer		$languageID
	 */
	public function makeDefault($languageID) {
		// remove old default language
		$sql = "UPDATE	wcf".WCF_N."_language
			SET	isDefault = 0
			WHERE	isDefault = 1";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		
		// make this language to default
		$sql = "UPDATE	wcf".WCF_N."_language
			SET	isDefault = 1
			WHERE	languageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($languageID));
		
		// rebuild language cache
		$this->clearCache();
	}
	
	/**
	 * Removes language cache and compiled templates.
	 */
	public function deleteLanguageCache() {
		LanguageEditor::deleteLanguageFiles();
		
		foreach ($this->cache['languages'] as $language) {
			$languageEditor = new LanguageEditor($language);
			$languageEditor->deleteCompiledTemplates();
		}
	}
}
