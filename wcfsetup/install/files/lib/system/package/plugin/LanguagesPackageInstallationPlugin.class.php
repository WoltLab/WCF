<?php
namespace wcf\system\package\plugin;
use wcf\data\language\LanguageEditor;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;
use wcf\util\XML;

/**
 * This PIP installs, updates or deletes language and their categories and items.
 *
 * @author 	Benjamin Kunz
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.package.plugin
 * @category 	Community Framework
 */
class LanguagesPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	/**
	 * @see	wcf\system\package\plugin\AbstractPackageInstallationPlugin::$tableName
	 */	
	public $tableName = 'language_item';
	
	/**
	 * @see	wcf\system\package\plugin\IPackageInstallationPlugin::install()
	 */
	public function install() {
		AbstractPackageInstallationPlugin::install();
		
		// get language files
		$languageFiles = array();
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
						throw new SystemException("Can not determine language code of language file '".$file."'");
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
				throw new SystemException("Can not determine language code of language file '".$filename."'");
			}
			
			$languageFiles[$languageCode] = $filename;
		}
		
		// get installed languages
		$installedLanguages = array();
		$sql = "SELECT		*
			FROM		wcf".WCF_N."_language
			ORDER BY	isDefault DESC";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute();
		while ($row = $statement->fetchArray()) {
			$installedLanguages[] = $row;
		}
		
		// install language
		$addedLanguageIDArray = array();
		foreach ($installedLanguages as $installedLanguage) {
			$languageFile = null;
			if (isset($languageFiles[$installedLanguage['languageCode']])) {
				$languageFile = $languageFiles[$installedLanguage['languageCode']];
			}
			else if ($multipleFiles) {
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
					$language = LanguageFactory::getLanguageByCode($installedLanguage['languageCode']);
					$languageEditor = new LanguageEditor($language);
					
					// import xml
					// don't update language files if package is standalone
					$languageEditor->updateFromXML($xml, $this->installation->getPackageID(), !$this->installation->getPackage()->standalone);
					
					// add language to this package
					$addedLanguageIDArray[] = $language->languageID;
				}
			}
		}
		
		// save package to language
		if (count($addedLanguageIDArray)) {
			$condition = '';
			$statementParameters = array($this->installation->getPackageID());
			foreach ($addedLanguageIDArray as $languageID) {
				if (!empty($condition)) $condition .= ',';
				$condition .= '?';
				$statementParameters[] = $languageID;
			}
			$statementParameters[] = $this->installation->getPackageID();
			
			$sql = "INSERT INTO	wcf".WCF_N."_language_to_package
						(languageID, packageID)
				SELECT		languageID, ?
				FROM		wcf".WCF_N."_language
				WHERE		languageID IN (".$condition.")
						AND languageID NOT IN (
							SELECT	languageID
							FROM	wcf".WCF_N."_language_to_package
							WHERE	packageID = ?
						)";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($statementParameters);
		}
	}
	
	/**
	 * Returns true if the uninstalling package got to uninstall languages, categories or items.
	 *
	 * @return 	boolean 			hasUnistall
	 */
	public function hasUninstall() {
		if (parent::hasUninstall()) return true;
		
		$sql = "SELECT	COUNT(languageID) AS count
			FROM	wcf".WCF_N."_language_to_package
			WHERE	packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->installation->getPackageID()));
		$languageCount = $statement->fetchArray();
		return $languageCount['count'] > 0;
	}
	
	/**
	 * Deletes languages, categories or items which where installed by the package.
	 */
	public function uninstall() {
		parent::uninstall();
		
		// delete language to package relation
		$sql = "DELETE FROM	wcf".WCF_N."_language_to_package
			WHERE		packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->installation->getPackageID()));
		
		// delete language items
		// Get all items and their categories
		// which where installed from this package.
		$sql = "SELECT	languageItemID, languageCategoryID, languageID
			FROM	wcf".WCF_N."_language_item
			WHERE	packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($this->installation->getPackageID()));
		$itemIDs = array();
		$categoryIDs = array();
		while ($row = $statement->fetchArray()) {
			$itemIDs[] = $row['languageItemID'];
			
			// Store categories
			$categoryIDs[$row['languageCategoryID']] = true;
		}
		
		if (count($itemIDs) > 0) {
			$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
				WHERE		languageItemID = ?
						AND packageID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($itemIDs as $itemID) {
				$statement->execute(array(
					$itemID,
					$this->installation->getPackageID()
				));
			}
			
			$this->deleteEmptyCategories(array_keys($categoryIDs), $this->installation->getPackageID());
		}
	}
	
	/**
	 * Extracts the language file and parses it with
     	 * SimpleXML. If the specified language file
	 * was not found, an error message is thrown.
	 *
	 * @param	string		$filename
	 * @return 	wcf\util\XML	xml
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
	 * Deletes categories which where changed by an update or deinstallation in case they are now empty.
	 *
	 * @param	array		$categoryIDs
	 * @param 	integer		$packageID
	 */
	protected function deleteEmptyCategories(array $categoryIDs, $packageID) {
		// Get empty categories which where changed by this package.
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("language_category.languageCategoryID IN (?)", array($categoryIDs));
		
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
		$categoriesToDelete = array();
		while ($row = $statement->fetchArray()) {
			if ($row['count'] == 0) {
				$categoriesToDelete[$row['languageCategoryID']] = $row['languageCategory'];
			}
		}
		
		// Delete categories from DB.
		if (count($categoriesToDelete) > 0) {
			$sql = "DELETE FROM	wcf".WCF_N."_language_category
				WHERE		languageCategory = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($categoriesToDelete as $category) {
				$statement->execute(array($category));
			}
		}
	}
	
	/**
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::handleDelete()
	 */
	protected function handleDelete(array $items) { }
	
	/**
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::prepareImport()
	 */
	protected function prepareImport(array $data) { }
	
	/**
	 * @see	wcf\system\package\plugin\AbstractXMLPackageInstallationPlugin::findExistingItem()
	 */
	protected function findExistingItem(array $data) { }
}
