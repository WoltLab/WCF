<?php
namespace wcf\system\language;
use wcf\data\package\PackageCache;

/**
 * Represents an i18n value for use with `AbstractAcpForm`.
 * 
 * @author	Alexander Ebert
 * @copyright	2001-2018 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Language
 * @since       3.1
 */
class I18nValue {
	/**
	 * field name
	 * @var string
	 */
	protected $fieldName = '';
	
	/**
	 * bit-mask to alter validation rules
	 * @var integer
	 */
	protected $flags = 0;
	
	/**
	 * language item template, placeholder or id will be appended
	 * @var string
	 */
	protected $languageItem = '';
	
	/**
	 * language item category
	 * @var string
	 */
	protected $languageItemCategory = '';
	
	/**
	 * package name used for the `packageID` reference
	 * @var string
	 */
	protected $languageItemPackage = '';
	
	/**
	 * allow an empty value, that includes providing no value at all
	 */
	const ALLOW_EMPTY = 1;
	
	/**
	 * require localized values, disallowing plain values
	 */
	const REQUIRE_I18N = 2;
	
	/**
	 * I18nValue constructor.
	 * 
	 * @param       string          $fieldName
	 */
	public function __construct($fieldName) {
		$this->fieldName = $fieldName;
	}
	
	/**
	 * Sets the language item configuration.
	 * 
	 * @param       string          $item
	 * @param       string          $category
	 * @param       string          $package
	 */
	public function setLanguageItem($item, $category, $package) {
		$this->languageItem = $item;
		$this->languageItemCategory = $category;
		$this->languageItemPackage = $package;
	}
	
	/**
	 * Sets bit flags.
	 * 
	 * @param       integer         $flags
	 */
	public function setFlags($flags) {
		$this->flags = $flags;
	}
	
	/**
	 * Returns true if given flag is set.
	 * 
	 * @param       integer         $flag
	 * @return      boolean
	 */
	public function getFlag($flag) {
		return (($this->flags & $flag) === $flag);
	}
	
	/**
	 * Returns the field identifier.
	 * 
	 * @return      string
	 */
	public function getFieldName() {
		return $this->fieldName;
	}
	
	/**
	 * Returns the language item template.
	 * 
	 * @return      string
	 */
	public function getLanguageItem() {
		return $this->languageItem;
	}
	
	/**
	 * Returns the language category.
	 * 
	 * @return      string
	 */
	public function getLanguageCategory() {
		return $this->languageItemCategory;
	}
	
	/**
	 * Returns the package id.
	 * 
	 * @return      string
	 */
	public function getPackageID() {
		return PackageCache::getInstance()->getPackageID($this->languageItemPackage);
	}
	
	/**
	 * @inheritDoc
	 */
	public function __toString() {
		return $this->getFieldName();
	}
}
