<?php
namespace wcf\system\language;
use wcf\data\language\Language;
use wcf\system\cache\CacheHandler;
use wcf\system\template\TemplateScriptingCompiler;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Handles language related functions.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.language
 * @category 	Community Framework
 */
abstract class LanguageFactory {
	/**
	 * Language cache.
	 *
	 * @var	array<array>
	 */
	private static $cache = null;
	
	/**
	 * Initialized languages.
	 *
	 * @var	array<Language>
	 */
	private static $languages = array();
	
	/**
	 * Active template scripting compiler
	 *
	 * @var	TemplateScriptingCompiler
	 */
	private static $scriptingCompiler = null;
	
	/**
	 * Returns a Language-object for each requested language id.
	 *
	 * @param	integer		$languageID
	 * @return	Language
	 */
	public static function getLanguage($languageID) {
		if (self::$cache === null) self::loadCache();
		
		if (!isset(self::$languages[$languageID])) {
			$language = new Language($languageID);
			
			if (!$language->languageID) {
				$languageID = self::findPreferredLanguage();
				$language = new Language($languageID);
			}
			
			self::$languages[$language->languageID] = $language;
			self::setLocale($languageID);
		}
		
		return self::$languages[$languageID];
	}
	
	/**
	 * Validates if given category is known.
	 *
	 * @param	string		$category
	 * @return	boolean
	 */
	public static function isValidCategory($category) {
		self::loadCache();
		
		return (isset(self::$cache['categories'][$category])) ? true : false;
	}
	
	/**
	 * Returns data for a specific category.
	 *
	 * @param	string		$category
	 * @return	array
	 */
	public static function getCategory($category) {
		if (isset(self::$cache['categories'][$category])) {
			return self::$cache['categories'][$category];
		}
		
		return array();
	}
	
	/**
	 * Returns a list of available language categories.
	 * 
	 * @return	array
	 */	
	public static function getCategories() {
		$categories = array();
		foreach (self::$cache['categories'] as $categoryName => $category) {
			$categories[$category['languageCategoryID']] = $categoryName;
		}
		
		return $categories;
	}
	
	/**
	 * Searches the preferred language of the current user.
	 */
	private static function findPreferredLanguage() {
		// get available language codes
		$availableLanguageCodes = array();
		foreach (self::getAvailableLanguages(PACKAGE_ID) as $language) {
			$availableLanguageCodes[] = $language['languageCode'];
		}
		
		// get default language
		$defaultLanguageCode = self::$cache['languages'][self::$cache['default']]['languageCode'];
		
		// get preferred language
		$languageCode = self::getPreferredLanguage($availableLanguageCodes, $defaultLanguageCode);
		
		// get language id of preferred language
		foreach (self::$cache['languages'] as $key => $language) {
			if ($language['languageCode'] == $languageCode) {
				return $key;
			}
		}
	}
	
	/**
	 * Determines the preferred language of the current user.
	 *
	 * @param	array		$availableLanguages
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
	 * Returns all available languages for given package
	 *
	 * @param 	integer		$packageID
	 * @return	array		$availableLanguages 	infos about each language (code, id, encoding, etc)
	 */
	public static function getAvailableLanguages($packageID = PACKAGE_ID) {
		// get list of all available languages
		$availableLanguages = array();
		if (isset(self::$cache['packages'][$packageID])) {
			foreach (self::$cache['packages'][$packageID] as $availableLanguageID) {
				$availableLanguages[] = self::$cache['languages'][$availableLanguageID];
			}
		}
		return $availableLanguages;
	}
	
	/**
	 * Returns an instance of Language or NULL for a given language code.
	 *
	 * @param	string		$languageCode
	 * @return	Language
	 */
	public static function getLanguageByCode($languageCode) {
		if (self::$cache === null) self::loadCache();
		
		// called within WCFSetup
		if (self::$cache === false || !count(self::$cache['codes'])) {
			$sql = "SELECT	languageID
				FROM	wcf".WCF_N."_language
				WHERE	languageCode = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array($languageCode));
			$row = $statement->fetchArray();
			if (isset($row['languageID'])) return new Language($row['languageID']);
		}
		else if (isset(self::$cache['codes'][$languageCode])) {
			return self::getLanguage(self::$cache['codes'][$languageCode]);
		}
		
		return null;
	}
	
	/**
	 * Returns the active scripting compiler object.
	 *
	 * @return	TemplateScriptingCompiler
	 */
	public static function getScriptingCompiler() {
		if (self::$scriptingCompiler === null) {
			self::$scriptingCompiler = new TemplateScriptingCompiler(WCF::getTPL());
		}
		
		return self::$scriptingCompiler;
	}
	
	/**
	 * Loads the language cache.
	 */
	private static function loadCache() {
		if (self::$cache === null) {
			CacheHandler::getInstance()->addResource(
				'languages',
				WCF_DIR.'cache/cache.languages.php',
				'wcf\system\cache\CacheBuilderLanguage'
			);
			
			self::$cache = CacheHandler::getInstance()->get('languages');
		}
	}
	
	/**
	 * Clears languages cache.
	 */
	public static function clearCache() {
		self::$cache = null;
		
		CacheHandler::getInstance()->clear(WCF_DIR.'cache/', 'cache.languages.php');
	}
	
	/**
	 * Sets the local language.
	 * Recall this function after language changed.
	 *
	 * @param	integer		$languageID
	 */
	private static function setLocale($languageID) {
		// set locale for
		// string comparison
		// character classification and conversion
		// date and time formatting
		setlocale(LC_COLLATE, self::$languages[$languageID]->get('wcf.global.locale.unix').'.UTF-8', self::$languages[$languageID]->get('wcf.global.locale.unix'), self::$languages[$languageID]->get('wcf.global.locale.win'));
		setlocale(LC_CTYPE, self::$languages[$languageID]->get('wcf.global.locale.unix').'.UTF-8', self::$languages[$languageID]->get('wcf.global.locale.unix'), self::$languages[$languageID]->get('wcf.global.locale.win'));
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
	public static function getDefaultLanguageID() {
		return self::$cache['default'];
	}
	
	/**
	 * Returns all available content languages for given package.
	 *
	 * @param 	integer		$packageID
	 * @return	array		$availableLanguages 	infos about each language (code, id, encoding, etc)
	 */
	public static function getAvailableContentLanguages($packageID = PACKAGE_ID) {
		$availableLanguages = array();
		if (isset(self::$cache['packages'][$packageID])) {
			foreach (self::$cache['packages'][$packageID] as $availableLanguageID) {
				if (self::$cache['languages'][$availableLanguageID]['hasContent']) {
					$availableLanguages[$availableLanguageID] = self::$cache['languages'][$availableLanguageID];
				}
			}
		}
		return $availableLanguages;
	}
	
	/**
	 * Makes given language the default language.
	 *
	 * @param	integer		$languageID
	 */
	public static function makeDefault($languageID) {
		// remove old default language
		$sql = "UPDATE	wcf".WCF_N."_language
			SET	isDefault = 0
			WHERE 	isDefault = 1";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		
		// make this language to default
		$sql = "UPDATE	wcf".WCF_N."_language
			SET	isDefault = 1
			WHERE 	languageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($languageID));
		
		// rebuild language cache
		self::clearCache();
	}
	
	/**
	 * Returns an ordered list of all installed languages.
	 * 
	 * @return	array
	 */	
	public static function getLanguages() {
		$languages = array();
		foreach (self::$cache['codes'] as $languageCode => $languageID) {
			$languages[$languageID] = WCF::getLanguage()->getDynamicVariable('wcf.global.language.'.$languageCode);
		}
		
		StringUtil::sort($languages);
		
		return $languages;
	}
	
	/**
	 * Returns a sorted list of all installed language codes.
	 * 
	 * @return	array
	 */
	public static function getLanguageCodes() {
		$languages = array();
		foreach (self::$cache['codes'] as $languageCode => $languageID) {
			$languages[$languageID] = $languageCode;
		}
		
		StringUtil::sort($languages);
		return $languages;
	}
}
