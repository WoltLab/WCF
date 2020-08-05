<?php
namespace wcf\data\cronjob;
use wcf\data\language\category\LanguageCategory;
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
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Cronjob
 * 
 * @method	Cronjob		getDecoratedObject()
 * @mixin	Cronjob
 */
class CronjobEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Cronjob::class;
	
	/**
	 * @inheritDoc
	 * @return	Cronjob
	 */
	public static function create(array $parameters = []) {
		$descriptions = [];
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
			$cronjobEditor->saveDescriptions($descriptions);
		}
		
		/** @noinspection PhpIncompatibleReturnTypeInspection */
		return $cronjob;
	}
	
	/**
	 * Saves the descriptions of the cronjob in language items.
	 * 
	 * @param	string[]		$descriptions
	 * @since	3.0
	 */
	protected function saveDescriptions(array $descriptions) {
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
			$statement->execute(['wcf.acp.cronjob']);
			$languageCategory = $statement->fetchObject(LanguageCategory::class);
			
			$languages = new LanguageList();
			$languages->readObjects();
		}
		else {
			$languages = LanguageFactory::getInstance()->getLanguages();
			$languageCategory = LanguageFactory::getInstance()->getCategory('wcf.acp.cronjob');
		}
		
		// save new descriptions
		$sql = "INSERT INTO             wcf".WCF_N."_language_item
						(languageID, languageItem, languageItemValue, languageCategoryID, packageID)
			VALUES                  (?, ?, ?, ?, ?)
			ON DUPLICATE KEY UPDATE languageItemValue = VALUES(languageItemValue),
						languageCategoryID = VALUES(languageCategoryID)";
		$statement = WCF::getDB()->prepareStatement($sql);
		
		foreach ($languages as $language) {
			$value = $defaultValue;
			if (isset($descriptions[$language->languageCode])) {
				$value = $descriptions[$language->languageCode];
			}
			
			$statement->execute([
				$language->languageID,
				'wcf.acp.cronjob.description.cronjob'.$this->cronjobID,
				$value,
				$languageCategory->languageCategoryID,
				$this->packageID
			]);
		}
		
		// update cronjob
		$this->update(['description' => 'wcf.acp.cronjob.description.cronjob'.$this->cronjobID]);
	}
	
	/**
	 * @inheritDoc
	 */
	public function update(array $parameters = []) {
		$descriptions = [];
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
	 * @inheritDoc
	 */
	public static function deleteAll(array $objectIDs = []) {
		// delete language items
		if (!empty($objectIDs)) {
			$sql = "DELETE FROM	wcf".WCF_N."_language_item
				WHERE		languageItem = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			WCF::getDB()->beginTransaction();
			foreach ($objectIDs as $cronjobID) {
				$statement->execute(['wcf.acp.cronjob.description.cronjob' . $cronjobID]);
			}
			WCF::getDB()->commitTransaction();
		}
		
		return parent::deleteAll($objectIDs);
	}
	
	/**
	 * @inheritDoc
	 */
	public static function resetCache() {
		CronjobCacheBuilder::getInstance()->reset();
		LanguageFactory::getInstance()->clearCache();
		LanguageFactory::getInstance()->deleteLanguageCache();
	}
}
