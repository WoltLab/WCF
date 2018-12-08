<?php
namespace wcf\data\language;
use wcf\data\language\category\LanguageCategory;
use wcf\data\language\category\LanguageCategoryEditor;
use wcf\data\language\item\LanguageItemEditor;
use wcf\data\language\item\LanguageItemList;
use wcf\data\page\PageEditor;
use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\system\cache\builder\LanguageCacheBuilder;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\io\AtomicWriter;
use wcf\system\language\LanguageFactory;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\DirectoryUtil;
use wcf\util\FileUtil;
use wcf\util\StringUtil;
use wcf\util\XML;

/**
 * Provides functions to edit languages.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Data\Language
 * 
 * @method static	Language	create(array $parameters = [])
 * @method		Language	getDecoratedObject()
 * @mixin		Language
 */
class LanguageEditor extends DatabaseObjectEditor implements IEditableCachedObject {
	/**
	 * @inheritDoc
	 */
	protected static $baseClass = Language::class;
	
	/**
	 * @inheritDoc
	 */
	public function delete() {
		parent::delete();
		
		self::deleteLanguageFiles($this->languageID);
	}
	
	/**
	 * Updates the language files for the given category.
	 * 
	 * @param	LanguageCategory	$languageCategory
	 */
	public function updateCategory(LanguageCategory $languageCategory) {
		$this->writeLanguageFiles([$languageCategory->languageCategoryID]);
	}
	
	/**
	 * Write the languages files.
	 * 
	 * @param	integer[]		$languageCategoryIDs
	 */
	protected function writeLanguageFiles(array $languageCategoryIDs) {
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("languageID = ?", [$this->languageID]);
		$conditions->add("languageCategoryID IN (?)", [$languageCategoryIDs]);
		
		// get language items
		$sql = "SELECT	languageItem, languageItemValue, languageCustomItemValue,
				languageUseCustomValue, languageCategoryID
			FROM	wcf".WCF_N."_language_item
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		$items = [];
		while ($row = $statement->fetchArray()) {
			$languageCategoryID = $row['languageCategoryID'];
			if (!isset($items[$languageCategoryID])) {
				$items[$languageCategoryID] = [];
			}
			
			$items[$languageCategoryID][$row['languageItem']] = $row['languageUseCustomValue'] ? $row['languageCustomItemValue'] : $row['languageItemValue'];
		}
		
		foreach ($items as $languageCategoryID => $languageItems) {
			$category = LanguageFactory::getInstance()->getCategoryByID($languageCategoryID);
			if ($category === null) {
				continue;
			}
			
			$filename = WCF_DIR.'language/'.$this->languageID.'_'.$category->languageCategory.'.php';
			$writer = new AtomicWriter($filename);
			
			$writer->write("<?php\n/**\n* WoltLab Suite\n* language: ".$this->languageCode."\n* encoding: UTF-8\n* category: ".$category->languageCategory."\n* generated at ".gmdate("r")."\n* \n* DO NOT EDIT THIS FILE\n*/\n");
			foreach ($languageItems as $languageItem => $languageItemValue) {
				$writer->write("\$this->items['".$languageItem."'] = '".str_replace(["\\", "'"], ["\\\\", "\'"], $languageItemValue)."';\n");
				
				// compile dynamic language variables
				if ($category->languageCategory != 'wcf.global' && strpos($languageItemValue, '{') !== false) {
					try {
						$output = LanguageFactory::getInstance()->getScriptingCompiler()->compileString($languageItem, $languageItemValue);
					}
					catch (SystemException $e) {
						continue;
					} // ignore compiler errors
					
					$writer->write("\$this->dynamicItems['".$languageItem."'] = '");
					$writer->write(str_replace(["\\", "'"], ["\\\\", "\'"], $output['template']));
					$writer->write("';\n");
				}
			}
			
			$writer->flush();
			$writer->close();
			FileUtil::makeWritable($filename);
		}
	}
	
	/**
	 * Exports this language.
	 * 
	 * @param	integer[]	$packageIDArray
	 * @param	boolean		$exportCustomValues
	 */
	public function export($packageIDArray = [], $exportCustomValues = false) {
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("language_item.languageID = ?", [$this->languageID]);
		
		// bom
		echo "\xEF\xBB\xBF";
		
		// header
		echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<language xmlns=\"http://www.woltlab.com\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://www.woltlab.com http://www.woltlab.com/XSD/vortex/language.xsd\" languagecode=\"".$this->languageCode."\" languagename=\"".$this->languageName."\" countrycode=\"".$this->countryCode."\">\n";
		
		// get items
		$items = [];
		if (!empty($packageIDArray)) {
			$conditions->add("language_item.packageID IN (?)", [$packageIDArray]);
		}
		
		$sql = "SELECT		languageItem, " . ($exportCustomValues ? "CASE WHEN languageUseCustomValue > 0 THEN languageCustomItemValue ELSE languageItemValue END AS languageItemValue" : "languageItemValue") . ", languageCategory
			FROM		wcf".WCF_N."_language_item language_item
			LEFT JOIN	wcf".WCF_N."_language_category language_category
			ON		(language_category.languageCategoryID = language_item.languageCategoryID)
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		while ($row = $statement->fetchArray()) {
			$items[$row['languageCategory']][$row['languageItem']] = $row['languageItemValue'];
		}
		
		// sort categories
		ksort($items);
		
		foreach ($items as $category => $categoryItems) {
			// sort items
			ksort($categoryItems);
			
			// category header
			echo "\t<category name=\"".$category."\">\n";
			
			// items
			foreach ($categoryItems as $item => $value) {
				echo "\t\t<item name=\"".$item."\"><![CDATA[".StringUtil::escapeCDATA($value)."]]></item>\n";
			}
			
			// category footer
			echo "\t</category>\n";
		}
		
		// add shadow values (pages)
		$pages = [];
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("page_content.pageID = page.pageID");
		$conditions->add("page_content.languageID = ?", [$this->languageID]);
		if (!empty($packageIDArray)) $conditions->add("page.packageID IN (?)", [$packageIDArray]);
		$conditions->add("page.originIsSystem = ?", [1]);
		$sql = "SELECT          page.identifier, page_content.title, page_content.content
			FROM            wcf" . WCF_N . "_page page,
					wcf" . WCF_N . "_page_content page_content
			" . $conditions ."
			ORDER BY        page.identifier";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		while ($row = $statement->fetchArray()) {
			$pages[] = $row;
		}
		
		if (!empty($pages)) {
			echo "\t<category name=\"shadow.invalid.page\">\n";
			
			foreach ($pages as $page) {
				if ($page['title']) echo "\t\t<item name=\"shadow.invalid.page.".$page['identifier'].".title\"><![CDATA[".StringUtil::escapeCDATA($page['title'])."]]></item>\n";
				if ($page['content']) echo "\t\t<item name=\"shadow.invalid.page.".$page['identifier'].".content\"><![CDATA[".StringUtil::escapeCDATA($page['content'])."]]></item>\n";
			}
			
			echo "\t</category>\n";
		}
		
		// add shadow values (boxes)
		$boxes = [];
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("box_content.boxID = box.boxID");
		$conditions->add("box_content.languageID = ?", [$this->languageID]);
		if (!empty($packageIDArray)) $conditions->add("box.packageID IN (?)", [$packageIDArray]);
		$conditions->add("box.originIsSystem = ?", [1]);
		$sql = "SELECT          box.identifier, box_content.title, box_content.content
			FROM            wcf" . WCF_N . "_box box,
					wcf" . WCF_N . "_box_content box_content
			" . $conditions ."
			ORDER BY        box.identifier";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		while ($row = $statement->fetchArray()) {
			$boxes[] = $row;
		}
		
		if (!empty($pages)) {
			echo "\t<category name=\"shadow.invalid.box\">\n";
			
			foreach ($boxes as $box) {
				if ($box['title']) echo "\t\t<item name=\"shadow.invalid.box.".$box['identifier'].".title\"><![CDATA[".StringUtil::escapeCDATA($box['title'])."]]></item>\n";
				if ($box['content']) echo "\t\t<item name=\"shadow.invalid.box.".$box['identifier'].".content\"><![CDATA[".StringUtil::escapeCDATA($box['content'])."]]></item>\n";
			}
			
			echo "\t</category>\n";
		}
		
		// footer
		echo "</language>";
	}
	
	/**
	 * Imports language items from an XML file into this language.
	 * Updates the relevant language files automatically.
	 * 
	 * @param	XML		$xml
	 * @param	integer		$packageID
	 * @param	boolean		$updateFiles
	 * @param	boolean		$updateExistingItems
	 */
	public function updateFromXML(XML $xml, $packageID, $updateFiles = true, $updateExistingItems = true) {
		$xpath = $xml->xpath();
		$usedCategories = [];
		
		// fetch categories
		$categories = $xpath->query('/ns:language/ns:category');
		
		/** @var \DOMElement $category */
		foreach ($categories as $category) {
			$usedCategories[$category->getAttribute('name')] = 0;
		}
		
		if (empty($usedCategories)) return;
		
		// select existing categories
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("languageCategory IN (?)", [array_keys($usedCategories)]);
		
		$sql = "SELECT	languageCategoryID, languageCategory
			FROM	wcf".WCF_N."_language_category
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		while ($row = $statement->fetchArray()) {
			$usedCategories[$row['languageCategory']] = $row['languageCategoryID'];
		}
		
		// create new categories
		foreach ($usedCategories as $categoryName => $categoryID) {
			if ($categoryID) continue;
			if (strpos($categoryName, 'shadow.invalid') === 0) continue; // ignore shadow items
			
			/** @var LanguageCategory $category */
			$category = LanguageCategoryEditor::create([
				'languageCategory' => $categoryName
			]);
			$usedCategories[$categoryName] = $category->languageCategoryID;
		}
		
		// loop through categories to import items
		$itemData = $pageContents = $boxContents = [];
		$languageItemValues = [];
		
		/** @var \DOMElement $category */
		foreach ($categories as $category) {
			$categoryName = $category->getAttribute('name');
			$elements = $xpath->query('child::*', $category);
			
			if ($categoryName == 'shadow.invalid.page') {
				/** @var \DOMElement $element */
				foreach ($elements as $element) {
					if (preg_match('/^shadow\.invalid\.page\.(.*)\.(title|content)/', $element->getAttribute('name'), $match)) {
						if (!isset($pageContents[$match[1]])) $pageContents[$match[1]] = [];
						$pageContents[$match[1]][$match[2]] = $element->nodeValue;
					}
				}
			}
			else if ($categoryName == 'shadow.invalid.box') {
				/** @var \DOMElement $element */
				foreach ($elements as $element) {
					if (preg_match('/^shadow\.invalid\.box\.(.*)\.(title|content)/', $element->getAttribute('name'), $match)) {
						if (!isset($boxContents[$match[1]])) $boxContents[$match[1]] = [];
						$boxContents[$match[1]][$match[2]] = $element->nodeValue;
					}
				}
			}
			else {
				$categoryID = $usedCategories[$categoryName];
				
				/** @var \DOMElement $element */
				foreach ($elements as $element) {
					$itemName = $element->getAttribute('name');
					$itemValue = $element->nodeValue;
					
					$itemData[] = $this->languageID;
					$itemData[] = $itemName;
					$itemData[] = $itemValue;
					$itemData[] = $categoryID;
					if ($packageID) $itemData[] = ($packageID == -1) ? PACKAGE_ID : $packageID;
					
					if ($updateExistingItems) {
						$languageItemValues[$itemName] = $itemValue;
					}
				}
			}
		}
		
		// save items
		if (!empty($itemData)) {
			// select phrases that have custom versions that might get disabled during the update
			if ($updateExistingItems) {
				$conditions = new PreparedStatementConditionBuilder();
				$conditions->add("languageItem IN (?)", [array_keys($languageItemValues)]);
				$conditions->add("languageID = ?", [$this->languageID]);
				if ($packageID > 0) $conditions->add("packageID = ?", [$packageID]);
				$conditions->add("languageUseCustomValue = ?", [1]);
				$conditions->add("languageItemOriginIsSystem = ?", [1]);
				
				$sql = "SELECT  languageItemID, languageItem, languageItemValue
					FROM    wcf".WCF_N."_language_item
					".$conditions;
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute($conditions->getParameters());
				$updateValues = [];
				while ($row = $statement->fetchArray()) {
					if ($row['languageItemValue'] != $languageItemValues[$row['languageItem']]) {
						$updateValues[] = $row['languageItemID'];
					}
				}
				
				if (!empty($updateValues)) {
					$sql = "UPDATE  wcf".WCF_N."_language_item
						SET     languageItemOldValue = languageItemValue,
							languageCustomItemDisableTime = ?,
							languageUseCustomValue = ?
						WHERE   languageItemID = ?";
					$statement = WCF::getDB()->prepareStatement($sql);
					
					WCF::getDB()->beginTransaction();
					foreach ($updateValues as $languageItemID) {
						$statement->execute([
							TIME_NOW,
							0,
							$languageItemID
						]);
					}
					WCF::getDB()->commitTransaction();
				}
			}
			
			// insert/update a maximum of 50 items per run (prevents issues with max_allowed_packet)
			$step = $packageID ? 5 : 4;
			WCF::getDB()->beginTransaction();
			for ($i = 0, $length = count($itemData); $i < $length; $i += 50 * $step) {
				$parameters = array_slice($itemData, $i, 50 * $step);
				$repeat = count($parameters) / $step;
				
				$sql = "INSERT".(!$updateExistingItems ? " IGNORE" : "")." INTO		wcf".WCF_N."_language_item
								(languageID, languageItem, languageItemValue, languageCategoryID". ($packageID ? ", packageID" : "") . ")
					VALUES			".substr(str_repeat('(?, ?, ?, ?'. ($packageID ? ', ?' : '') .'), ', $repeat), 0, -2);
				
				if ($updateExistingItems) {
					if ($packageID > 0) {
						// do not update anything if language item is owned by a different package
						$sql .= "	ON DUPLICATE KEY
								UPDATE			languageItemValue = IF(packageID = ".$packageID.", IF(languageItemOriginIsSystem = 0, languageItemValue, VALUES(languageItemValue)), languageItemValue),
											languageCategoryID = IF(packageID = ".$packageID.", VALUES(languageCategoryID), languageCategoryID)";
					}
					else {
						// skip package id check during WCFSetup (packageID = 0) or if using the ACP form (packageID = -1)
						$sql .= "	ON DUPLICATE KEY
								UPDATE			languageItemValue = IF(languageItemOriginIsSystem = 0, languageItemValue, VALUES(languageItemValue)),
											languageCategoryID = VALUES(languageCategoryID)";
					}
				}
				
				$statement = WCF::getDB()->prepareStatement($sql);
				$statement->execute($parameters);
			}
			WCF::getDB()->commitTransaction();
		}
		
		// save page content
		if (!empty($pageContents)) {
			// get page ids
			$pageIDs = [];
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("identifier IN (?)", [array_keys($pageContents)]);
			$sql = "SELECT  pageID, identifier
				FROM    wcf".WCF_N."_page
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			while ($row = $statement->fetchArray()) {
				$pageIDs[$row['identifier']] = $row['pageID'];
			}
			
			$sql = "INSERT IGNORE INTO      wcf".WCF_N."_page_content
							(pageID, languageID)
				VALUES                  (?, ?)";
			$createLanguageVersionStatement = WCF::getDB()->prepareStatement($sql);
			$sql = "UPDATE  wcf".WCF_N."_page_content
				SET     title = ?
				WHERE   pageID = ?
					AND languageID = ?";
			$updateTitleStatement = WCF::getDB()->prepareStatement($sql);
			$sql = "UPDATE  wcf".WCF_N."_page_content
				SET     content = ?
				WHERE   pageID = ?
					AND languageID = ?";
			$updateContentStatement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($pageContents as $identifier => $pageContent) {
				if (!isset($pageIDs[$identifier])) continue; // unknown page
				
				$createLanguageVersionStatement->execute([$pageIDs[$identifier], $this->languageID]);
				if (isset($pageContent['title'])) {
					$updateTitleStatement->execute([$pageContent['title'], $pageIDs[$identifier], $this->languageID]);
				}
				if (isset($pageContent['content'])) {
					$updateContentStatement->execute([$pageContent['content'], $pageIDs[$identifier], $this->languageID]);
				}
			}
		}
		
		// save box content
		if (!empty($boxContents)) {
			// get box ids
			$boxIDs = [];
			$conditions = new PreparedStatementConditionBuilder();
			$conditions->add("identifier IN (?)", [array_keys($boxContents)]);
			$sql = "SELECT  boxID, identifier
				FROM    wcf".WCF_N."_box
				".$conditions;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute($conditions->getParameters());
			while ($row = $statement->fetchArray()) {
				$boxIDs[$row['identifier']] = $row['boxID'];
			}
			
			$sql = "INSERT IGNORE INTO      wcf".WCF_N."_box_content
							(boxID, languageID)
				VALUES                  (?, ?)";
			$createLanguageVersionStatement = WCF::getDB()->prepareStatement($sql);
			$sql = "UPDATE  wcf".WCF_N."_box_content
				SET     title = ?
				WHERE   boxID = ?
					AND languageID = ?";
			$updateTitleStatement = WCF::getDB()->prepareStatement($sql);
			$sql = "UPDATE  wcf".WCF_N."_box_content
				SET     content = ?
				WHERE   boxID = ?
					AND languageID = ?";
			$updateContentStatement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($boxContents as $identifier => $boxContent) {
				if (!isset($boxIDs[$identifier])) continue; // unknown box
				
				$createLanguageVersionStatement->execute([$boxIDs[$identifier], $this->languageID]);
				if (isset($boxContent['title'])) {
					$updateTitleStatement->execute([$boxContent['title'], $boxIDs[$identifier], $this->languageID]);
				}
				if (isset($boxContent['content'])) {
					$updateContentStatement->execute([$boxContent['content'], $boxIDs[$identifier], $this->languageID]);
				}
			}
		}
		
		// update the relevant language files
		if ($updateFiles) {
			self::deleteLanguageFiles($this->languageID);
		}
		
		// delete relevant template compilations
		$this->deleteCompiledTemplates();
	}
	
	/**
	 * Deletes the language cache.
	 * 
	 * @param	string		$languageID
	 * @param	string		$category
	 */
	public static function deleteLanguageFiles($languageID = '.*', $category = '.*') {
		if ($category != '.*') $category = preg_quote($category, '~');
		if ($languageID != '.*') $languageID = intval($languageID);
		
		DirectoryUtil::getInstance(WCF_DIR.'language/')->removePattern(new Regex($languageID.'_'.$category.'\.php$'));
	}
	
	/**
	 * Deletes relevant template compilations.
	 */
	public function deleteCompiledTemplates() {
		// templates
		DirectoryUtil::getInstance(WCF_DIR.'templates/compiled/')->removePattern(new Regex('.*_'.$this->languageID.'_.*\.php$'));
		// acp templates
		DirectoryUtil::getInstance(WCF_DIR.'acp/templates/compiled/')->removePattern(new Regex('.*_'.$this->languageID.'_.*\.php$'));
	}
	
	/**
	 * Updates all language files of the given package id.
	 */
	public static function updateAll() {
		self::deleteLanguageFiles();
	}
	
	/**
	 * Takes an XML object and returns the specific language code.
	 * 
	 * @param	XML		$xml
	 * @return	string
	 * @throws	SystemException
	 */
	public static function readLanguageCodeFromXML(XML $xml) {
		$rootNode = $xml->xpath()->query('/ns:language')->item(0);
		$attributes = $xml->xpath()->query('attribute::*', $rootNode);
		foreach ($attributes as $attribute) {
			if ($attribute->name == 'languagecode') {
				return $attribute->value;
			}
		}
		
		throw new SystemException("missing attribute 'languagecode' in language file");
	}
	
	/**
	 * Takes an XML object and returns the specific language name.
	 * 
	 * @param	XML		$xml
	 * @return	string		language name
	 * @throws	SystemException
	 */
	public static function readLanguageNameFromXML(XML $xml) {
		$rootNode = $xml->xpath()->query('/ns:language')->item(0);
		$attributes = $xml->xpath()->query('attribute::*', $rootNode);
		foreach ($attributes as $attribute) {
			if ($attribute->name == 'languagename') {
				return $attribute->value;
			}
		}
		
		throw new SystemException("missing attribute 'languagename' in language file");
	}
	
	/**
	 * Takes an XML object and returns the specific country code.
	 * 
	 * @param	XML		$xml
	 * @return	string		country code
	 * @throws	SystemException
	 */
	public static function readCountryCodeFromXML(XML $xml) {
		$rootNode = $xml->xpath()->query('/ns:language')->item(0);
		$attributes = $xml->xpath()->query('attribute::*', $rootNode);
		foreach ($attributes as $attribute) {
			if ($attribute->name == 'countrycode') {
				return $attribute->value;
			}
		}
		
		throw new SystemException("missing attribute 'countrycode' in language file");
	}
	
	/**
	 * Imports language items from an XML file into a new or a current language.
	 * Updates the relevant language files automatically.
	 * 
	 * @param	XML	        $xml
	 * @param	integer		$packageID
	 * @param       Language        $source
	 * @return	LanguageEditor
	 */
	public static function importFromXML(XML $xml, $packageID, Language $source = null) {
		$languageCode = self::readLanguageCodeFromXML($xml);
		
		// try to find an existing language with the given language code
		$language = LanguageFactory::getInstance()->getLanguageByCode($languageCode);
		
		// create new language
		if ($language === null) {
			$countryCode = self::readCountryCodeFromXML($xml);
			$languageName = self::readLanguageNameFromXML($xml);
			$language = self::create([
				'countryCode' => $countryCode,
				'languageCode' => $languageCode,
				'languageName' => $languageName
			]);
			
			if ($source) {
				$sourceEditor = new LanguageEditor($source);
				$sourceEditor->copy($language);
			}
		}
		
		// import xml
		$languageEditor = new LanguageEditor($language);
		$languageEditor->updateFromXML($xml, $packageID);
		
		// return language object
		return $languageEditor;
	}
	
	/**
	 * Copies all language variables from current language to language specified as $destination.
	 * Caution: This method expects that target language does not have any items!
	 * 
	 * @param	Language	$destination
	 */
	public function copy(Language $destination) {
		$sql = "INSERT INTO	wcf".WCF_N."_language_item
					(languageID, languageItem, languageItemValue, languageItemOriginIsSystem, languageCategoryID, packageID)
			SELECT		?, languageItem, languageItemValue, languageItemOriginIsSystem, languageCategoryID, packageID
			FROM		wcf".WCF_N."_language_item
			WHERE		languageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([
			$destination->languageID,
			$this->languageID
		]);
	}
	
	/**
	 * Updates the language items of a language category.
	 * 
	 * @param	array			$items
	 * @param	LanguageCategory	$category
	 * @param	integer			$packageID
	 * @param	array			$useCustom
	 */
	public function updateItems(array $items, LanguageCategory $category, $packageID = PACKAGE_ID, array $useCustom = []) {
		if (empty($items)) return;
		
		// find existing language items
		$languageItemList = new LanguageItemList();
		$languageItemList->getConditionBuilder()->add("language_item.languageItem IN (?)", [array_keys($items)]);
		$languageItemList->getConditionBuilder()->add("languageID = ?", [$this->languageID]);
		$languageItemList->readObjects();
		
		foreach ($languageItemList->getObjects() as $languageItem) {
			$languageItemEditor = new LanguageItemEditor($languageItem);
			$languageItemEditor->update([
				'languageCustomItemValue' => $items[$languageItem->languageItem],
				'languageUseCustomValue' => isset($useCustom[$languageItem->languageItem]) ? 1 : 0
			]);
			
			// remove updated items, leaving items to be created within
			unset($items[$languageItem->languageItem]);
		}
		
		// create remaining items
		if (!empty($items)) {
			// bypass LanguageItemEditor::create() for performance reasons
			$sql = "INSERT INTO	wcf".WCF_N."_language_item
				(languageID, languageItem, languageItemValue, languageItemOriginIsSystem, languageCategoryID, packageID)
				VALUES		(?, ?, ?, ?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($items as $itemName => $itemValue) {
				$statement->execute([
					$this->languageID,
					$itemName,
					$itemValue,
					0,
					$category->languageCategoryID,
					$packageID
				]);
			}
		}
		
		// update the relevant language files
		self::deleteLanguageFiles($this->languageID, $category->languageCategory);
		
		// delete relevant template compilations
		$this->deleteCompiledTemplates();
	}
	
	/**
	 * Sets current language as default language.
	 */
	public function setAsDefault() {
		// remove default flag from all languages
		$sql = "UPDATE	wcf".WCF_N."_language
			SET	isDefault = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([0]);
		
		// set current language as default language
		$this->update(['isDefault' => 1]);
		
		$this->clearCache();
	}
	
	/**
	 * Clears language cache.
	 */
	public function clearCache() {
		LanguageCacheBuilder::getInstance()->reset();
	}
	
	/**
	 * Enables the multilingualism feature for given languages.
	 * 
	 * @param	array		$languageIDs
	 */
	public static function enableMultilingualism(array $languageIDs = []) {
		$sql = "UPDATE	wcf".WCF_N."_language
			SET	hasContent = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([0]);
		
		if (!empty($languageIDs)) {
			$sql = '';
			$statementParameters = [];
			foreach ($languageIDs as $languageID) {
				if (!empty($sql)) $sql .= ',';
				$sql .= '?';
				$statementParameters[] = $languageID;
			}
			
			$sql = "UPDATE	wcf".WCF_N."_language
				SET	hasContent = ?
				WHERE	languageID IN (".$sql.")";
			$statement = WCF::getDB()->prepareStatement($sql);
			array_unshift($statementParameters, 1);
			$statement->execute($statementParameters);
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public static function resetCache() {
		LanguageFactory::getInstance()->clearCache();
	}
	
	/**
	 * Copies all cms contents (article, box, media, page) from given source language to language specified as $destinationLanguageID.
	 *
	 * @param	integer         $sourceLanguageID
	 * @param       integer         $destinationLanguageID
	 */
	public static function copyLanguageContent($sourceLanguageID, $destinationLanguageID) {
		// article content
		$sql = "INSERT IGNORE INTO      wcf".WCF_N."_article_content
						(articleID, languageID, title, teaser, content, imageID, hasEmbeddedObjects)
			SELECT                  articleID, ?, title, teaser, content, imageID, hasEmbeddedObjects
			FROM                    wcf".WCF_N."_article_content
			WHERE                   languageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$destinationLanguageID, $sourceLanguageID]);
		
		// box content
		$sql = "INSERT IGNORE INTO      wcf".WCF_N."_box_content
						(boxID, languageID, title, content, imageID, hasEmbeddedObjects)
			SELECT                  boxID, ?, title, content, imageID, hasEmbeddedObjects
			FROM                    wcf".WCF_N."_box_content
			WHERE                   languageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$destinationLanguageID, $sourceLanguageID]);
		
		// create tpl files
		$sql = "SELECT  *
			FROM    wcf".WCF_N."_box_content
			WHERE   boxID IN (SELECT boxID FROM wcf".WCF_N."_box WHERE boxType = ?)
				AND languageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(['tpl', $destinationLanguageID]);
		while ($row = $statement->fetchArray()) {
			file_put_contents(WCF_DIR . 'templates/__cms_box_' . $row['boxID'] . '_' . $destinationLanguageID . '.tpl', $row['content']);
		}
		
		// media content
		$sql = "INSERT IGNORE INTO      wcf".WCF_N."_media_content
						(mediaID, languageID, title, caption, altText)
			SELECT                  mediaID, ?, title, caption, altText
			FROM                    wcf".WCF_N."_media_content
			WHERE                   languageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$destinationLanguageID, $sourceLanguageID]);
		
		// page content
		$sql = "INSERT IGNORE INTO      wcf".WCF_N."_page_content
						(pageID, languageID, title, content, metaDescription, metaKeywords, customURL, hasEmbeddedObjects)
			SELECT                  pageID, ?, title, content, metaDescription, metaKeywords, CASE WHEN customURL <> '' THEN CONCAT(customURL, '_', ?) ELSE '' END, hasEmbeddedObjects
			FROM                    wcf".WCF_N."_page_content
			WHERE                   languageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$destinationLanguageID, $destinationLanguageID, $sourceLanguageID]);
		
		// create tpl files
		$sql = "SELECT  *
			FROM    wcf".WCF_N."_page_content
			WHERE   pageID IN (SELECT pageID FROM wcf".WCF_N."_page WHERE pageType = ?)
				AND languageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(['tpl', $destinationLanguageID]);
		while ($row = $statement->fetchArray()) {
			file_put_contents(WCF_DIR . 'templates/__cms_page_' . $row['pageID'] . '_' . $destinationLanguageID . '.tpl', $row['content']);
		}
		
		PageEditor::resetCache();
	}
}
