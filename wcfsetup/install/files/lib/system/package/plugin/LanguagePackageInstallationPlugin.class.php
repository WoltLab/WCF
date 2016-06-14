<?php
namespace wcf\system\package\plugin;
use wcf\data\language\LanguageEditor;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\language\LanguageFactory;
use wcf\system\package\PackageArchive;
use wcf\system\WCF;
use wcf\util\XML;

/**
 * Installs, updates and deletes languages, their categories and items.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Package\Plugin
 */
class LanguagePackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	/**
	 * @inheritDoc
	 */
	public $tableName = 'language_item';
	
	/**
	 * @inheritDoc
	 */
	public function install() {
		AbstractPackageInstallationPlugin::install();
		
		// get language files
		$languageFiles = [];
		$multipleFiles = false;
		$filename = $this->instruction['value'];
		if (strpos($filename, '*') !== false) {
			// wildcard syntax; import multiple files
			$multipleFiles = true;
			$files = $this->installation->getArchive()->getTar()->getContentList();
			$pattern = str_replace("\*", ".*", preg_quote($filename));
			
			foreach ($files as $file) {
				if (preg_match('!'.$pattern.'!i', $file['filename'])) {
					if (preg_match('~([a-z-]+)\.xml$~i', $file['filename'], $match)) {
						$languageFiles[$match[1]] = $file['filename'];
					}
					else {
						throw new SystemException("Cannot determine language code of language file '".$file['filename']."'");
					}
				}
			}
		}
		else {
			if (!empty($this->instruction['attributes']['languagecode'])) {
				$languageCode = $this->instruction['attributes']['languagecode'];
			}
			else if (!empty($this->instruction['attributes']['language'])) {
				$languageCode = $this->instruction['attributes']['language'];
			}
			else if (preg_match('~([a-z-]+)\.xml$~i', $filename, $match)) {
				$languageCode = $match[1];
			}
			else {
				throw new SystemException("Cannot determine language code of language file '".$filename."'");
			}
			
			$languageFiles[$languageCode] = $filename;
		}
		
		// get installed languages
		$installedLanguages = [];
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_language
			ORDER BY	isDefault DESC";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		while ($row = $statement->fetchArray()) {
			$installedLanguages[] = $row;
		}
		
		// install language
		foreach ($installedLanguages as $installedLanguage) {
			$languageFile = null;
			$updateExistingItems = true;
			if (isset($languageFiles[$installedLanguage['languageCode']])) {
				$languageFile = $languageFiles[$installedLanguage['languageCode']];
			}
			else if ($multipleFiles) {
				// do not update existing items, only add new ones
				$updateExistingItems = false;
				
				// use default language
				if (isset($languageFiles[$installedLanguages[0]['languageCode']])) {
					$languageFile = $languageFiles[$installedLanguages[0]['languageCode']];
				}
				
				// use english (if installed)
				else if (isset($languageFiles['en'])) {
					foreach ($installedLanguages as $installedLanguage2) {
						if ($installedLanguage2['languageCode'] == 'en') {
							$languageFile = $languageFiles['en'];
							break;
						}
					}
				}
				
				// use any installed language
				if ($languageFile === null) {
					foreach ($installedLanguages as $installedLanguage2) {
						if (isset($languageFiles[$installedLanguage2['languageCode']])) {
							$languageFile = $languageFiles[$installedLanguage2['languageCode']];
							break;
						}
					}
				}
				
				// use first delivered language
				if ($languageFile === null) {
					foreach ($languageFiles as $languageFile) break;
				}
			}
			
			// save language
			if ($languageFile !== null) {
				if ($xml = $this->readLanguage($languageFile)) {
					// get language object
					$language = LanguageFactory::getInstance()->getLanguageByCode($installedLanguage['languageCode']);
					$languageEditor = new LanguageEditor($language);
					
					// import xml
					// don't update language files if package is an application
					$languageEditor->updateFromXML($xml, $this->installation->getPackageID(), !$this->installation->getPackage()->isApplication, $updateExistingItems);
				}
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function uninstall() {
		parent::uninstall();
		
		// delete language items
		// Get all items and their categories
		// which where installed from this package.
		$sql = "SELECT	languageItemID, languageCategoryID, languageID
			FROM	wcf".WCF_N."_language_item
			WHERE	packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$this->installation->getPackageID()]);
		$itemIDs = [];
		$categoryIDs = [];
		while ($row = $statement->fetchArray()) {
			$itemIDs[] = $row['languageItemID'];
			
			// Store categories
			$categoryIDs[$row['languageCategoryID']] = true;
		}
		
		if (!empty($itemIDs)) {
			$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
				WHERE		languageItemID = ?
						AND packageID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($itemIDs as $itemID) {
				$statement->execute([
					$itemID,
					$this->installation->getPackageID()
				]);
			}
			
			$this->deleteEmptyCategories(array_keys($categoryIDs), $this->installation->getPackageID());
		}
	}
	
	/**
	 * Extracts the language file and parses it. If the specified language file
	 * was not found, an exception message is thrown.
	 * 
	 * @param	string		$filename
	 * @return	XML
	 * @throws	SystemException
	 */
	protected function readLanguage($filename) {
		// search language files in package archive
		// throw error message if not found
		if (($fileIndex = $this->installation->getArchive()->getTar()->getIndexByFilename($filename)) === false) {
			throw new SystemException("language file '".$filename."' not found.");
		}
		
		// extract language file and parse with DOMDocument
		$xml = new XML();
		$xml->loadXML($filename, $this->installation->getArchive()->getTar()->extractToString($fileIndex));
		return $xml;
	}
	
	/**
	 * Deletes categories which where changed by an update or deinstallation
	 * in case they are now empty.
	 * 
	 * @param	array		$categoryIDs
	 * @param	integer		$packageID
	 */
	protected function deleteEmptyCategories(array $categoryIDs, $packageID) {
		// Get empty categories which where changed by this package.
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("language_category.languageCategoryID IN (?)", [$categoryIDs]);
		
		$sql = "SELECT		COUNT(item.languageItemID) AS count,
					language_category.languageCategoryID,
					language_category.languageCategory
			FROM		wcf".WCF_N."_language_category language_category
			LEFT JOIN	wcf".WCF_N."_language_item item
			ON		(item.languageCategoryID = language_category.languageCategoryID)
			".$conditions."
			GROUP BY	language_category.languageCategoryID ASC,
					language_category.languageCategory ASC";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		$categoriesToDelete = [];
		while ($row = $statement->fetchArray()) {
			if ($row['count'] == 0) {
				$categoriesToDelete[$row['languageCategoryID']] = $row['languageCategory'];
			}
		}
		
		// Delete categories from DB.
		if (!empty($categoriesToDelete)) {
			$sql = "DELETE FROM	wcf".WCF_N."_language_category
				WHERE		languageCategory = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($categoriesToDelete as $category) {
				$statement->execute([$category]);
			}
		}
	}
	
	/**
	 * @inheritDoc
	 */
	protected function handleDelete(array $items) { }
	
	/**
	 * @inheritDoc
	 */
	protected function prepareImport(array $data) { }
	
	/**
	 * @inheritDoc
	 */
	protected function findExistingItem(array $data) { }
	
	/**
	 * @see	\wcf\system\package\plugin\IPackageInstallationPlugin::getDefaultFilename()
	 * @since	3.0
	 */
	public static function getDefaultFilename() {
		return 'language/*.xml';
	}
	/**
	 * @inheritDoc
	 */
	public static function isValid(PackageArchive $archive, $instruction) {
		return true;
	}
}
