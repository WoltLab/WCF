<?php
namespace wcf\system\cache\builder;
use wcf\data\language\category\LanguageCategoryList;
use wcf\data\language\LanguageList;
use wcf\system\WCF;

/**
 * Caches languages, language to packages relation, package to languages relation
 * and the id of the default language. 
 * 
 * @author	Marcel Werk
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.cache.builder
 * @category	Community Framework
 */
class LanguageCacheBuilder implements ICacheBuilder {
	/**
	 * @see	wcf\system\cache\ICacheBuilder::getData()
	 */
	public function getData(array $cacheResource) {
		$data = array(
			'codes' => array(),
			'countryCodes' => array(),
			'languages' => array(), 
			'packages' => array(),
			'default' => 0,
			'categories' => array()
		);
		
		// get language to packages
		$sql = "SELECT		package.languageID, package.packageID
			FROM		wcf".WCF_N."_language_to_package package
			LEFT JOIN	wcf".WCF_N."_language language
			ON		(language.languageID = package.languageID)
			ORDER BY	language.isDefault DESC,
					language.languageCode ASC";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		while ($row = $statement->fetchArray()) {
			// package to languages
			if (!isset($data['packages'][$row['packageID']])) {
				$data['packages'][$row['packageID']] = array();
			}
			$data['packages'][$row['packageID']][] = $row['languageID'];
		}
		
		// get languages
		$languageList = new LanguageList();
		$languageList->sqlLimit = 0;
		$languageList->readObjects();
		$data['languages'] = $languageList->getObjects();
		foreach ($languageList->getObjects() as $language) {
			// default language
			if ($language->isDefault) {
				$data['default'] = $language->languageID;
			}
			
			// language code to language id
			$data['codes'][$language->languageCode] = $language->languageID;
			
			// country code to language id
			$data['countryCode'][$language->languageID] = $language->countryCode;
		}
		
		// get language categories
		$languageCategoryList = new LanguageCategoryList();
		$languageCategoryList->sqlLimit = 0;
		$languageCategoryList->readObjects();
		foreach ($languageCategoryList->getObjects() as $languageCategory) {
			$data['categories'][$languageCategory->languageCategory] = $languageCategory;
		}

		return $data;
	}
}
