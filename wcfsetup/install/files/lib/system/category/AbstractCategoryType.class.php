<?php
namespace wcf\system\category;
use wcf\data\category\CategoryEditor;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Abstract implementation of a category type.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.category
 * @category 	Community Framework
 */
abstract class AbstractCategoryType extends SingletonFactory implements ICategoryType {
	/**
	 * indicates if categories of this type may have no empty description
	 * @var	boolean
	 */
	protected $forceDescription = true;
	
	/**
	 * indicates if categories of this type have descriptions
	 * @var	boolean
	 */
	protected $hasDescription = true;
	
	/**
	 * language category which contains the language variables of i18n values
	 * @var	string
	 */
	protected $i18nLangVarCategory = '';
	
	/**
	 * prefix used for language variables in templates
	 * @var	string
	 */
	protected $langVarPrefix = '';
	
	/**
	 * permission prefix for the add/delete/edit permissions
	 * @var	string
	 */
	protected $permissionPrefix = '';
	
	/**
	 * maximum category nesting lebel
	 * @var	integer
	 */
	protected $maximumNestingLevel = -1;
	
	/**
	 * name of the object types associated with categories of this type (the
	 * key is the definition name and value the object type name)
	 * @var	array<string>
	 */
	protected $objectTypes = array();
	
	/**
	 * @see	wcf\system\category\ICategoryType::afterDeletion()
	 */
	public function afterDeletion(CategoryEditor $categoryEditor) {
		// move child categories to parent category
		foreach (CategoryHandler::getInstance()->getChildCategories($categoryEditor->getDecoratedObject()) as $category) {
			$__categoryEditor = new CategoryEditor($category);
			$__categoryEditor->update(array(
				'parentCategoryID' => $categoryEditor->parentCategoryID
			));
		}
	}
	
	/**
	 * @see	wcf\system\category\ICategoryType::canAddCategory()
	 */
	public function canAddCategory() {
		return WCF::getSession()->getPermission($this->permissionPrefix.'.canAddCategory');
	}
	
	/**
	 * @see	wcf\system\category\ICategoryType::canDeleteCategory()
	 */
	public function canDeleteCategory() {
		return WCF::getSession()->getPermission($this->permissionPrefix.'.canDeleteCategory');
	}
	
	/**
	 * @see	wcf\system\category\ICategoryType::canEditCategory()
	 */
	public function canEditCategory() {
		return WCF::getSession()->getPermission($this->permissionPrefix.'.canEditCategory');
	}
	
	/**
	 * @see	wcf\system\category\ICategoryType::forceDescription()
	 */
	public function forceDescription() {
		return $this->hasDescription() && $this->forceDescription;
	}
	
	/**
	 * @see	wcf\system\category\ICategoryType::getObjectTypeName()
	 */
	public function getObjectTypeName($definitionName) {
		if (isset($this->objectTypes[$definitionName])) {
			return $this->objectTypes[$definitionName];
		}
		
		return null;
	}
	
	/**
	 * @see	wcf\system\category\ICategoryType::getDescriptionLangVarCategory()
	 */
	public function getDescriptionLangVarCategory() {
		return $this->i18nLangVarCategory;
	}
	
	/**
	 * @see	wcf\system\category\ICategoryType::getI18nLangVarPrefix()
	 */
	public function getI18nLangVarPrefix() {
		return $this->i18nLangVarCategory.'.category';
	}
	
	/**
	 * @see	wcf\system\category\ICategoryType::getLanguageVariable()
	 */
	public function getLanguageVariable($name, $optional = false) {
		if ($this->langVarPrefix) {
			$value = WCF::getLanguage()->get($this->langVarPrefix.'.'.$name, true);
			if ($value) {
				return $value;
			}
		}
		
		return WCF::getLanguage()->get('wcf.category.'.$name, $optional);
	}
	
	/**
	 * @see	wcf\system\category\ICategoryType::getMaximumNestingLevel()
	 */
	public function getMaximumNestingLevel() {
		return $this->maximumNestingLevel;
	}
	
	/**
	 * @see	wcf\system\category\ICategoryType::getTitleLangVarCategory()
	 */
	public function getTitleLangVarCategory() {
		return $this->i18nLangVarCategory;
	}
	
	/**
	 * @see	wcf\system\category\ICategoryType::hasDescription()
	 */
	public function hasDescription() {
		return $this->hasDescription;
	}
}
