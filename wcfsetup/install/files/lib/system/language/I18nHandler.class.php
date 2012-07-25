<?php
namespace wcf\system\language;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\language\LanguageFactory;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Provides internationalization support for input fields.
 *
 * @author	Alexander Ebert
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.language
 * @category 	Community Framework
 */
class I18nHandler extends SingletonFactory {
	/**
	 * indicates if value variables are assigned in assignVariables()
	 * @var	boolean
	 */
	protected $assignValueVariablesDisabled = false;
	
	/**
	 * list of available languages
	 * @var	array<wcf\data\language\Language>
	 */
	protected $availableLanguages = array();
	
	/**
	 * list of element ids
	 * @var	array<string>
	 */
	protected $elementIDs = array();
	
	/**
	 * list of plain values for elements
	 * @var	array<string>
	 */
	protected $plainValues = array();
	
	/**
	 * i18n values for elements
	 * @var	array<array>
	 */
	protected $i18nValues = array();
	
	/**
	 * element options
	 * @var	array<array>
	 */
	protected $elementOptions = array();
	
	/**
	 * @see wcf\system\SingletonFactory::init()
	 */
	protected function init() {
		 $this->availableLanguages = LanguageFactory::getInstance()->getLanguages();
	}

	/**
	 * Registers a new element id, returns false if element id is already set.
	 * 
	 * @param	string		elementID
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
				$this->plainValues[$elementID] = $_POST[$elementID];
				continue;
			}
			
			$i18nElementID = $elementID . '_i18n';
			if (isset($_POST[$i18nElementID]) && is_array($_POST[$i18nElementID])) {
				$this->i18nValues[$elementID] = array();
				
				foreach ($_POST[$i18nElementID] as $languageID => $value) {
					$this->i18nValues[$elementID][$languageID] = $value;
				}
				
				continue;
			}
			
			throw new SystemException("Missing expected value for element id '".$elementID."'");
		}
	}
	
	/**
	 * Returns true, if given element has disabled i18n functionality.
	 * 
	 * @param	string		elementID
	 * @return	boolean
	 */
	public function isPlainValue($elementID) {
		if (isset($this->plainValues[$elementID])) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Returns true, if given element has enabled i18n functionality.
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
	 * @param	string		elementID
	 * @return	string
	 * @see		wcf\system\language\I18nHandler::isPlainValue()
	 */
	public function getValue($elementID) {
		return $this->plainValues[$elementID];
	}
	
	/**
	 * Returns the values for the given element. If the element is multilingual,
	 * the multilingual values are returned, otherweise the plain value is
	 * returned for each language id.
	 * 
	 * @param	string		elementID
	 * @return	array<string>
	 */
	public function getValues($elementID) {
		if ($this->hasI18nValues($elementID)) {
			return $this->i18nValues[$elementID];
		}
		
		$plainValue = $this->getValue($elementID);
		
		$values = array();
		foreach ($this->availableLanguages as $language) {
			$values[$language->languageID] = $plainValue;
		}
		
		return $values;
	}
	
	/**
	 * Sets the value for the given element. If the element is multilingual,
	 * the given value is set for every available language.
	 * 
	 * @param 	integer 	$elementID
	 * @param 	string 		$plainValue
	 * 
	 * @throws  SystemException - if $plainValue is not of type string
	 */
	public function setValue($elementID, $plainValue) {
		if (!is_string($plainValue)) {
			throw new SystemException('Invalid argument for parameter $plainValue', 0, 'Expected string. '.ucfirst(gettype($plainValue)).' given.');
		}
		if (!$this->isPlainValue($elementID)) {
			$i18nValues = array();
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
	 * use {@link I18nHandler::setValue()} instead.
	 * 
	 * @param 	integer 	 $elementID
	 * @param 	array<array> $i18nValues
	 * 
	 * @throws  SystemException if $i18nValues doesn't have any elements
	 */
	public function setValues($elementID, array $i18nValues) {
		if (!count($i18nValues)) {
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
	 * Returns false, if element value is not empty.
	 * 
	 * @param	string		$elementID
	 * @param	boolean		$requireI18n
	 * @param	boolean		$permitEmptyValue
	 * @return	boolean
	 */
	public function validateValue($elementID, $requireI18n = false, $permitEmptyValue = false) {
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
				
				if (empty($this->i18nValues[$elementID][$language->languageID])) {
					return false;
				}
			}
		}
		
		return true;
	}
	
	/**
	 * Saves language variable for i18n. Given package id must match the associated
	 * packages, using PACKAGE_ID is highly discouraged as this breaks the ability
	 * to delete unused language items on package uninstallation using foreign keys.
	 * 
	 * @param	string		$elementID
	 * @param	string		$languageVariable
	 * @param	string		$languageCategory
	 * @param	integer		$packageID
	 */
	public function save($elementID, $languageVariable, $languageCategory, $packageID) {
		// get language category id
		$sql = "SELECT	languageCategoryID
			FROM	wcf".WCF_N."_language_category
			WHERE	languageCategory = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array($languageCategory));
		$row = $statement->fetchArray();
		$languageCategoryID = $row['languageCategoryID'];
		
		$languageIDs = array_keys($this->i18nValues[$elementID]);
		
		$conditions = new PreparedStatementConditionBuilder();
		$conditions->add("languageID IN (?)", array($languageIDs));
		$conditions->add("languageItem = ?", array($languageVariable));
		$conditions->add("packageID = ?", array($packageID));
		
		$sql = "SELECT	languageItemID, languageID
			FROM	wcf".WCF_N."_language_item
			".$conditions;
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute($conditions->getParameters());
		$languageItemIDs = array();
		while ($row = $statement->fetchArray()) {
			$languageItemIDs[$row['languageID']] = $row['languageItemID'];
		}
		
		$insertLanguageIDs = $updateLanguageIDs = array();
		foreach ($languageIDs as $languageID) {
			if (isset($languageItemIDs[$languageID])) {
				$updateLanguageIDs[] = $languageID;
			}
			else {
				$insertLanguageIDs[] = $languageID;
			}
		}
		
		// insert language items
		if (count($insertLanguageIDs)) {
			$sql = "INSERT INTO	wcf".WCF_N."_language_item
						(languageID, languageItem, languageItemValue, languageItemOriginIsSystem, languageCategoryID, packageID)
				VALUES		(?, ?, ?, ?, ?, ?)";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($insertLanguageIDs as $languageID) {
				$statement->execute(array(
					$languageID,
					$languageVariable,
					$this->i18nValues[$elementID][$languageID],
					0,
					$languageCategoryID,
					$packageID
				));
			}
		}
		
		// update language items
		if (count($updateLanguageIDs)) {
			$sql = "UPDATE	wcf".WCF_N."_language_item
				SET	languageItemValue = ?
				WHERE	languageItemID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			
			foreach ($updateLanguageIDs as $languageID) {
				$statement->execute(array(
					$this->i18nValues[$elementID][$languageID],
					$languageItemIDs[$languageID]
				));
			}
		}
		
		// reset language cache
		LanguageFactory::getInstance()->deleteLanguageCache();
	}
	
	/**
	 * Removes previously created i18n language variables.
	 * 
	 * @param	string		$languageVariable
	 * @param	integer		$packageID
	 */
	public function remove($languageVariable, $packageID) {
		$sql = "DELETE FROM	wcf".WCF_N."_language_item
			WHERE		languageItem = ?
					AND packageID = ?";
		$statement = WCF::getDB()->prepareStatement($sql);
		$statement->execute(array(
			$languageVariable,
			$packageID
		));
		
		// reset language cache
		LanguageFactory::getInstance()->deleteLanguageCache();
	}
	
	/**
	 * Sets additional options for elements, required if updating values.
	 * 
	 * @param	integer		$elementID
	 * @param	string		$value
	 * @param	string		$pattern
	 */
	public function setOptions($elementID, $packageID, $value, $pattern) {
		$this->elementOptions[$elementID] = array(
			'packageID' => $packageID,
			'pattern' => $pattern,
			'value' => $value
		);
	}
	
	/**
	 * Assigns element values to template. Using request data once reading
	 * initial database data is explicitly disallowed.
	 * 
	 * @param	boolean		$useRequestData
	 */
	public function assignVariables($useRequestData = true) {
		$elementValues = array();
		$elementValuesI18n = array();
		
		foreach ($this->elementIDs as $elementID) {
			$value = '';
			$i18nValues = array();
			
			if (!$this->assignValueVariablesDisabled) {
				// use POST values instead of querying database
				if ($useRequestData) {
					if ($this->isPlainValue($elementID)) {
						$value = $this->getValue($elementID);
					}
					else {
						if ($this->hasI18nValues($elementID)) {
							$i18nValues = $this->i18nValues[$elementID];
						}
						else {
							$i18nValues = array();
						}
					}
				}
				else {
					if (preg_match('~^'.$this->elementOptions[$elementID]['pattern'].'$~', $this->elementOptions[$elementID]['value'])) {
						// use i18n values from language items
						$sql = "SELECT	languageID, languageItemValue
							FROM	wcf".WCF_N."_language_item
							WHERE	languageItem = ?
								AND packageID = ?";
						$statement = WCF::getDB()->prepareStatement($sql);
						$statement->execute(array(
							$this->elementOptions[$elementID]['value'],
							$this->elementOptions[$elementID]['packageID']
						));
						while ($row = $statement->fetchArray()) {
							$i18nValues[$row['languageID']] = $row['languageItemValue'];
						}
					}
					else {
						// use data provided by setOptions()
						$value = $this->elementOptions[$elementID]['value'];
					}
				}
			}
			
			$elementValues[$elementID] = $value;
			$elementValuesI18n[$elementID] = $i18nValues;
		}
		
		WCF::getTPL()->assign(array(
			'availableLanguages' => $this->availableLanguages,
			'i18nPlainValues' => $elementValues,
			'i18nValues' => $elementValuesI18n
		));
	}
	
	/**
	 * Disables assignment of value variables in assignVariables().
	 */
	public function disableAssignValueVariables() {
		$this->assignValueVariablesDisabled = true;
	}
	
	/**
	 * Enables assignment of value variables in assignVariables().
	 */
	public function enableAssignValueVariables() {
		$this->assignValueVariablesDisabled = false;
	}
}
