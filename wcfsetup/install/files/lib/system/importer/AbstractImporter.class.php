<?php
namespace wcf\system\importer;
use wcf\data\package\PackageCache;
use wcf\system\WCF;

/**
 * Basic implementation of IImporter.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Importer
 */
abstract class AbstractImporter implements IImporter {
	/**
	 * database object class name
	 * @var	string
	 */
	protected $className = '';
	
	/**
	 * @inheritDoc
	 */
	public function getClassName() {
		return $this->className;
	}
	
	/**
	 * Imports a list of language items.
	 * 
	 * @param       string[][]      $items
	 * @param       string          $languageCategory
	 * @param       string          $package
	 */
	protected function importI18nValues(array $items, $languageCategory, $package) {
		// get package id
		$packageID = PackageCache::getInstance()->getPackageID($package);
		
		$sql = "INSERT INTO             wcf".WCF_N."_language_item
						(languageID, languageItem, languageItemValue, languageCategoryID, packageID)
			VALUES                  (?, ?, ?, ?, ?)
			ON DUPLICATE KEY
			UPDATE                  languageItemValue = VALUES(languageItemValue)";
		$statement = WCF::getDB()->prepareStatement($sql);
		WCF::getDB()->beginTransaction();
		foreach ($items as $itemData) {
			$statement->execute([
				$itemData['languageID'],
				$itemData['languageItem'],
				$itemData['languageItemValue'],
				$this->getLanguageCategoryID($languageCategory),
				$packageID
			]);
		}
		WCF::getDB()->commitTransaction();
	}
	
	/**
	 * Returns the language category id.
	 * 
	 * @param       string          $languageCategory
	 * @return      integer
	 */
	protected function getLanguageCategoryID($languageCategory) {
		static $languageCategoryIDs = [];
		
		if (!isset($languageCategoryIDs[$languageCategory])) {
			// get language category id
			$sql = "SELECT  languageCategoryID
				FROM    wcf" . WCF_N . "_language_category
				WHERE   languageCategory = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([$languageCategory]);
			
			$languageCategoryIDs[$languageCategory] = $statement->fetchSingleColumn();
		}
		
		return $languageCategoryIDs[$languageCategory];
	}
}
