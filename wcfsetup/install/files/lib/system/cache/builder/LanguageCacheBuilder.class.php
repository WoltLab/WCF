<?php
namespace wcf\system\cache\builder;
use wcf\data\language\category\LanguageCategoryList;
use wcf\data\language\LanguageList;
use wcf\data\DatabaseObject;

/**
 * Caches languages and the id of the default language. 
 * 
 * @author	Marcel Werk
 * @copyright	2001-2015 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class LanguageCacheBuilder extends AbstractCacheBuilder {
	/**
	 * @see	\wcf\system\cache\builder\AbstractCacheBuilder::rebuild()
	 */
	public function rebuild(array $parameters) {
		$data = array(
			'codes' => array(),
			'countryCodes' => array(),
			'languages' => array(),
			'default' => 0,
			'categories' => array(),
			'categoryIDs' => array(),
			'multilingualismEnabled' => false
		);
		
		// get languages
		$languageList = new LanguageList();
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
