<?php
namespace wcf\system\cache\builder;
use wcf\data\language\category\LanguageCategoryList;
use wcf\data\language\LanguageList;
use wcf\data\DatabaseObject;

/**
 * Caches languages and the id of the default language. 
 * 
 * @author	Marcel Werk
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Cache\Builder
 */
class LanguageCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @inheritDoc
	 */
	public function rebuild(array $parameters) {
		$data = [
			'codes' => [],
			'countryCodes' => [],
			'languages' => [],
			'default' => 0,
			'categories' => [],
			'categoryIDs' => [],
			'multilingualismEnabled' => false
		];
		
		// get languages
		$languageList = new LanguageList();
		$languageList->getConditionBuilder()->add('language.isDisabled = ?', [0]);
		$languageList->readObjects();
		$data['languages'] = $languageList->getObjects();
		foreach ($languageList->getObjects() as $language) {
			// default language
			if ($language->isDefault) {
				$data['default'] = $language->languageID;
			}
			
			// multilingualism
			if ($language->hasContent) {
				$data['multilingualismEnabled'] = true;
			}
			
			// language code to language id
			$data['codes'][$language->languageCode] = $language->languageID;
			
			// country code to language id
			$data['countryCode'][$language->languageID] = $language->countryCode;
		}
		
		DatabaseObject::sort($data['languages'], 'languageName');
		
		// get language categories
		$languageCategoryList = new LanguageCategoryList();
		$languageCategoryList->readObjects();
		foreach ($languageCategoryList->getObjects() as $languageCategory) {
			$data['categories'][$languageCategory->languageCategory] = $languageCategory;
			$data['categoryIDs'][$languageCategory->languageCategoryID] = $languageCategory->languageCategory;
		}
		
		return $data;
	}
}
