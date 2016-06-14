<?php
namespace wcf\system\language;
use wcf\data\language\Language;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\Regex;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Provides internationalization support for input fields.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Language
 */
class I18nHandler extends SingletonFactory {
	/**
	 * list of available languages
	 * @var	Language[]
	 */
	protected $availableLanguages = [];
	
	/**
	 * list of element ids
	 * @var	string[]
	 */
	protected $elementIDs = [];
	
	/**
	 * list of plain values for elements
	 * @var	string[]
	 */
	protected $plainValues = [];
	
	/**
	 * i18n values for elements
	 * @var	string[][]
	 */
	protected $i18nValues = [];
	
	/**
	 * element options
	 * @var	mixed[][]
	 */
	protected $elementOptions = [];
	
	/**
	 * language variable regex object
	 * @var	Regex
	 */
	protected $regex = null;
	
	/**
	 * @inheritDoc
	 */
	protected function init() {
		$this->availableLanguages = LanguageFactory::getInstance()->getLanguages();
	}
	
	/**
	 * Registers a new element id, returns false if element id is already set.
	 * 
	 * @param	string		$elementID
	 * @return	boolean
	 */
	public function register($elementID) {
		if (in_array($elementID, $this->elementIDs)) {
			return false;
		}
		
		$this->elementIDs[] = $elementID;
		return true;
	}
	
	/**
	 * Reads plain and i18n values from request data.
	 */
	public function readValues() {
		foreach ($this->elementIDs as $elementID) {
			if (isset($_POST[$elementID])) {
				// you should trim the string before using it; prevents unwanted newlines
				$this->plainValues[$elementID] = StringUtil::unifyNewlines(StringUtil::trim($_POST[$elementID]));
				continue;
			}
			
			$i18nElementID = $elementID . '_i18n';
			if (isset($_POST[$i18nElementID]) && is_array($_POST[$i18nElementID])) {
				$this->i18nValues[$elementID] = [];
				
				foreach ($_POST[$i18nElementID] as $languageID => $value) {
					$this->i18nValues[$elementID][$languageID] = StringUtil::unifyNewlines(StringUtil::trim($value));
				}
				
				continue;
			}
			
			throw new SystemException("Missing expected value for element id '".$elementID."'");
		}
	}
	
	/**
	 * Returns true if given element has disabled i18n functionality.
	 * 
	 * @param	string		$elementID
	 * @return	boolean
	 */
	public function isPlainValue($elementID) {
		if (isset($this->plainValues[$elementID])) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Returns true if given element has enabled i18n functionality.
	 * 
	 * @param	string		$elementID
	 * @return	boolean
	 */
	public function hasI18nValues($elementID) {
		if (isset($this->i18nValues[$elementID])) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Returns the plain value for the given element.
	 * 
	 * @param	string		$elementID
	 * @return	string
	 * @see		\wcf\system\language\I18nHandler::isPlainValue()
	 */
	public function getValue($elementID) {
		return $this->plainValues[$elementID];
	}
	
	/**
	 * Returns the values for the given element. If the element is multilingual,
	 * the multilingual values are returned, otherweise the plain value is
	 * returned for each language id.
	 * 
	 * @param	string		$elementID
	 * @return	string[]
	 */
	public function getValues($elementID) {
		if ($this->hasI18nValues($elementID)) {
			return $this->i18nValues[$elementID];
		}
		
		$plainValue = $this->getValue($elementID);
		
		$values = [];
		foreach ($this->availableLanguages as $language) {
			$values[$language->languageID] = $plainValue;
		}
		
		return $values;
	}
	
	/**
	 * Sets the value for the given element. If the element is multilingual,
	 * the given value is set for every available language.
	 * 
	 * @param	string		$elementID
	 * @param	string		$plainValue
	 * @throws	SystemException
	 */
	public function setValue($elementID, $plainValue) {
		if (!is_string($plainValue)) {
			throw new SystemException('Invalid argument for parameter $plainValue', 0, 'Expected string. '.ucfirst(gettype($plainValue)).' given.');
		}
		if (!$this->isPlainValue($elementID)) {
			$i18nValues = [];
			foreach ($this->availableLanguages as $language) {
				$i18nValues[$language->languageID] = StringUtil::trim($plainValue);
			}
			$this->setValues($elementID, $i18nValues);
		}
		else {
			$this->plainValues[$elementID] = StringUtil::trim($plainValue);
		}
	}
	
	/**
	 * Sets the values for the given element. If the element is not multilingual,
	 * use I18nHandler::setValue() instead.
	 * 
	 * @param	string		$elementID
	 * @param	string[]	$i18nValues
	 * @throws	SystemException
	 */
	public function setValues($elementID, array $i18nValues) {
		if (empty($i18nValues)) {
			throw new SystemException('Invalid argument for parameter $i18nValues', 0, 'Expected filled array as second argument. Empty array given.');
		}
		if (!$this->isPlainValue($elementID)) {
			$this->i18nValues[$elementID] = $i18nValues;
		}
		else {
			$plainValue = array_shift($i18nValues);
			$this->setValue($elementID, $plainValue);
		}
	}
	
	/**
	 * Returns true if the value with the given id is valid.
	 * 
	 * @param	string		$elementID
	 * @param	boolean		$requireI18n
	 * @param	boolean		$permitEmptyValue
	 * @return	boolean
	 */
	public function validateValue($elementID, $requireI18n = false, $permitEmptyValue = false) {
		// do not force i18n if only one language is available
		if ($requireI18n && count($this->availableLanguages) == 1) {
			$requireI18n = false;
		}
		
		if ($this->isPlainValue($elementID)) {
			// plain values may be left empty
			if ($permitEmptyValue) {
				return true;
			}
			
			if ($requireI18n || $this->getValue($elementID) == '') {
				return false;
			}
		}
		else if ($requireI18n && (!isset($this->i18nValues[$elementID]) || empty($this->i18nValues[$elementID]))) {
			return false;
		}
		else {
			foreach ($this->availableLanguages as $language) {
				if (!isset($this->i18nValues[$elementID][$language->languageID])) {
					return false;
				}
				
				if (!$permitEmptyValue && empty($this->i18nValues[$elementID][$language->languageID])) {
					return false;
				}
			}
		}
		
		return true;
	}
	
	/**
	 * Saves language variable for i18n.
	 * 
	 * @param	string		$elementID
	 * @param	string		$languageVariable
	 * @param	string		$languageCategory
	 * @param	integer		$packageID
	 */
	public function save($elementID, $languageVariable, $languageCategory, $packageID = PACKAGE_ID) {
		// get language category id
		$sql = "SELECT	languageCategoryID
			FROM	wcf".WCF_N."_language_category
			WHERE	languageCategory = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$languageCategory]);
		$row = $statement->fetchArray();
		$languageCategoryID = $row['languageCategoryID'];
		
		if (count($this->availableLanguages) == 1) {
			$languageIDs = array_keys($this->availableLanguages);
		}
		else {
			$languageIDs = array_keys($this->i18nValues[$elementID]);
		}
		
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("languageID IN (?)", [$languageIDs]);
		$conditions->add("languageItem = ?", [$languageVariable]);
		
		$sql = "SELECT	languageItemID, languageID
			FROM	wcf".WCF_N."_language_item
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		$languageItemIDs = [];
		while ($row = $statement->fetchArray()) {
			$languageItemIDs[$row['languageID']] = $row['languageItemID'];
		}
		
		$insertLanguageIDs = $updateLanguageIDs = [];
		foreach ($languageIDs as $languageID) {
			if (isset($languageItemIDs[$languageID])) {
				$updateLanguageIDs[] = $languageID;
			}
			else {
				$insertLanguageIDs[] = $languageID;
			}
		}
		
		// insert language items
		if (!empty($insertLanguageIDs)) {
			$sql = "INSERT INTO	wcf".WCF_N."_language_item
						(languageID, languageItem, languageItemValue, languageItemOriginIsSystem, languageCategoryID, packageID)
				VALUES		(?, ?, ?, ?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($insertLanguageIDs as $languageID) {
				$statement->execute([
					$languageID,
					$languageVariable,
					(isset($this->i18nValues[$elementID]) ? $this->i18nValues[$elementID][$languageID] : $this->plainValues[$elementID]),
					0,
					$languageCategoryID,
					$packageID
				]);
			}
		}
		
		// update language items
		if (!empty($updateLanguageIDs)) {
			$sql = "UPDATE	wcf".WCF_N."_language_item
				SET	languageItemValue = ?,
					languageItemOriginIsSystem = ?
				WHERE	languageItemID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($updateLanguageIDs as $languageID) {
				$statement->execute([
					(isset($this->i18nValues[$elementID]) ? $this->i18nValues[$elementID][$languageID] : $this->plainValues[$elementID]),
					0,
					$languageItemIDs[$languageID]
				]);
			}
		}
		
		// reset language cache
		LanguageFactory::getInstance()->deleteLanguageCache();
	}
	
	/**
	 * Removes previously created i18n language variables.
	 * 
	 * @param	string		$languageVariable
	 */
	public function remove($languageVariable) {
		$sql = "DELETE FROM	wcf".WCF_N."_language_item
			WHERE		languageItem = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute([$languageVariable]);
		
		// reset language cache
		LanguageFactory::getInstance()->deleteLanguageCache();
	}
	
	/**
	 * Sets additional options for elements, required if updating values.
	 * 
	 * @param	integer		$elementID
	 * @param	integer		$packageID
	 * @param	string		$value
	 * @param	string		$pattern
	 */
	public function setOptions($elementID, $packageID, $value, $pattern) {
		$this->elementOptions[$elementID] = [
			'packageID' => $packageID,
			'pattern' => $pattern,
			'value' => $value
		];
	}
	
	/**
	 * Assigns element values to template. Using request data once reading
	 * initial database data is explicitly disallowed.
	 * 
	 * @param	boolean		$useRequestData
	 */
	public function assignVariables($useRequestData = true) {
		$elementValues = [];
		$elementValuesI18n = [];
		
		foreach ($this->elementIDs as $elementID) {
			$value = '';
			$i18nValues = [];
			
			// use POST values instead of querying database
			if ($useRequestData) {
				if ($this->isPlainValue($elementID)) {
					$value = $this->getValue($elementID);
				}
				else {
					if ($this->hasI18nValues($elementID)) {
						$i18nValues = $this->i18nValues[$elementID];
						// encoding the entries for javascript
						foreach ($i18nValues as $languageID => $value) {
							$i18nValues[$languageID] = StringUtil::encodeJS(StringUtil::unifyNewlines($value));
						}
					}
					else {
						$i18nValues = [];
					}
				}
			}
			else {
				$isI18n = Regex::compile('^'.$this->elementOptions[$elementID]['pattern'].'$')->match($this->elementOptions[$elementID]['value']);
				if (!$isI18n) {
					// check if it's a regular language variable
					$isI18n = Regex::compile('^([a-zA-Z0-9-_]+\.)+[a-zA-Z0-9-_]+$')->match($this->elementOptions[$elementID]['value']);
				}
				
				if ($isI18n) {
					// use i18n values from language items
					$sql = "SELECT	languageID, languageItemValue
						FROM	wcf".WCF_N."_language_item
						WHERE	languageItem = ?";
					$statement = WCF::getDB()->prepareStatement($sql);
					$statement->execute([
						$this->elementOptions[$elementID]['value']
					]);
					while ($row = $statement->fetchArray()) {
						$languageItemValue = StringUtil::unifyNewlines($row['languageItemValue']);
						$i18nValues[$row['languageID']] = StringUtil::encodeJS($languageItemValue);
						
						if ($row['languageID'] == LanguageFactory::getInstance()->getDefaultLanguageID()) {
							$value = $languageItemValue;
						}
					}
					
					// item appeared to be a language item but either is not or does not exist
					if (empty($i18nValues) && empty($value)) {
						$value = $this->elementOptions[$elementID]['value'];
					}
				}
				else {
					// use data provided by setOptions()
					$value = $this->elementOptions[$elementID]['value'];
				}
			}
			
			$elementValues[$elementID] = $value;
			$elementValuesI18n[$elementID] = $i18nValues;
		}
		
		WCF::getTPL()->assign([
			'availableLanguages' => $this->availableLanguages,
			'i18nPlainValues' => $elementValues,
			'i18nValues' => $elementValuesI18n
		]);
	}
	
	/**
	 * Resets internally stored data after creating a new object through a form.
	 */
	public function reset() {
		$this->i18nValues = $this->plainValues = [];
	}
	
	/**
	 * Returns true if given string equals a language variable.
	 * 
	 * @param	string		$string
	 * @return	boolean
	 */
	protected function isLanguageVariable($string) {
		if ($this->regex === null) {
			$this->regex = new Regex('^([a-zA-Z0-9-_]+\.)+[a-zA-Z0-9-_]+$');
		}
		
		return $this->regex->match($string);
	}
}
