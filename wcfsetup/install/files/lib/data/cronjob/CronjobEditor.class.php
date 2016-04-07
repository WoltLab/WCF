<?php
namespace wcf\data\cronjob;
use wcf\data\language\LanguageList;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\CronjobCacheBuilder;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Provides functions to edit cronjobs.
 * 
 * @author	Alexander Ebert, Matthias Schmidt
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	data.cronjob
 * @category	Community Framework
 */
class CronjobEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * @see	\wcf\data\DatabaseObjectDecorator::$baseClass
	 */
	protected static $baseClass = 'wcf\data\cronjob\Cronjob';
	
	/**
	 * @see	\wcf\data\IEditableObject::create()
	 */
	public static function create(array $parameters = array()) {
		$descriptions = array();
		if (isset($parameters['description']) && is_array($parameters['description'])) {
			if (count($parameters['description']) > 1) {
				$descriptions = $parameters['description'];
				$parameters['description'] = '';
			}
			else {
				$parameters['description'] = reset($parameters['description']);
			}
		}
		
		$cronjob = parent::create($parameters);
		
		// save cronjob description
		if (!empty($descriptions)) {
			$cronjobEditor = new self($cronjob);
			$cronjobEditor->saveDescriptions($descriptions, false);
		}
		
		return $cronjob;
	}
	
	/**
	 * Saves the descriptions of the cronjob in language items.
	 * 
	 * @param	string[]		$descriptions
	 * @param	boolean			$deleteOldDescriptions
	 * @since	2.2
	 */
	protected function saveDescriptions(array $descriptions, $deleteOldDescriptions = true) {
		// set default value
		if (isset($descriptions[''])) {
			$defaultValue = $descriptions[''];
		}
		else if (isset($descriptions['en'])) {
			// fallback to English
			$defaultValue = $descriptions['en'];
		}
		else if (isset($descriptions[WCF::getLanguage()->getFixedLanguageCode()])) {
			// fallback to the language of the current user
			$defaultValue = $descriptions[WCF::getLanguage()->getFixedLanguageCode()];
		}
		else {
			// fallback to first description
			$defaultValue = reset($descriptions);
		}
		
		// fetch data directly from database during framework installation
		if (!PACKAGE_ID) {
			$sql = "SELECT	*
				FROM	wcf".WCF_N."_language_category
				WHERE	languageCategory = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array('wcf.acp.cronjob'));
			$languageCategory = $statement->fetchObject('wcf\data\language\category\LanguageCategory');
			
			$languages = new LanguageList();
			$languages->readObjects();
		}
		else {
			$languages = LanguageFactory::getInstance()->getLanguages();
			$languageCategory = LanguageFactory::getInstance()->getCategory('wcf.acp.cronjob');
		}
		
		// delete old descriptions first
		if ($deleteOldDescriptions) {
			$sql = "DELETE FROM	wcf".WCF_N."_language_item
				WHERE		languageItem = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(['wcf.acp.cronjob.description.cronjob'.$this->cronjobID]);
		}
		
		// save new descriptions
		$sql = "INSERT INTO	wcf".WCF_N."_language_item
					(languageID, languageItem, languageItemValue, languageCategoryID, packageID)
			VALUES		(?, ?, ?, ?, ?)";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		foreach ($languages as $language) {
			$value = $defaultValue;
			if (isset($descriptions[$language->languageCode])) {
				$value = $descriptions[$language->languageCode];
			}
			
			$statement->execute(array(
				$language->languageID,
				'wcf.acp.cronjob.description.cronjob'.$this->cronjobID,
				$value,
				$languageCategory->languageCategoryID,
				$this->packageID
			));
		}
		
		// update cronjob
		$this->update(array(
			'description' => 'wcf.acp.cronjob.description.cronjob'.$this->cronjobID
		));
	}
	
	/**
	 * @see	\wcf\data\IEditableObject::update()
	 */
	public function update(array $parameters = array()) {
		$descriptions = array();
		if (isset($parameters['description']) && is_array($parameters['description'])) {
			if (count($parameters['description']) > 1) {
				$descriptions = $parameters['description'];
				$parameters['description'] = '';
			}
			else {
				$parameters['description'] = reset($parameters['description']);
			}
		}
		
		parent::update($parameters);
		
		// save cronjob description
		if (!empty($descriptions)) {
			$this->saveDescriptions($descriptions);
		}
	}
	
	/**
	 * @see	\wcf\data\IEditableCachedObject::resetCache()
	 */
	public static function resetCache() {
		CronjobCacheBuilder::getInstance()->reset();
	}
}
