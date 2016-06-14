<?php
namespace wcf\system\category;
use wcf\data\category\CategoryEditor;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Abstract implementation of a category type.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Category
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
	protected $i18nLangVarCategory = 'wcf.category';
	
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
	 * @var	string[]
	 */
	protected $objectTypes = [];
	
	/**
	 * @inheritDoc
	 */
	public function afterDeletion(CategoryEditor $categoryEditor) {
		$categoryIDs = array_keys(CategoryHandler::getInstance()->getChildCategories($categoryEditor->categoryID));
		
		if (!empty($categoryIDs)) {
			// move child categories to parent category
			$conditionBuilder = new PreparedStatementConditionBuilder();
			$conditionBuilder->add("categoryID IN (?)", [$categoryIDs]);
			$sql = "UPDATE	wcf".WCF_N."_category
				SET	parentCategoryID = ?
				".$conditionBuilder;
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array_merge([$categoryEditor->parentCategoryID], $conditionBuilder->getParameters()));
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function canAddCategory() {
		return WCF::getSession()->getPermission($this->permissionPrefix.'.canAddCategory');
	}
	
	/**
	 * @inheritDoc
	 */
	public function canDeleteCategory() {
		return WCF::getSession()->getPermission($this->permissionPrefix.'.canDeleteCategory');
	}
	
	/**
	 * @inheritDoc
	 */
	public function canEditCategory() {
		return WCF::getSession()->getPermission($this->permissionPrefix.'.canEditCategory');
	}
	
	/**
	 * @inheritDoc
	 */
	public function changedParentCategories(array $categoryData) {
		// does nothing
	}
	
	/**
	 * @inheritDoc
	 */
	public function forceDescription() {
		return $this->hasDescription() && $this->forceDescription;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getApplication() {
		$classParts = explode('\\', get_called_class());
		return $classParts[0];
	}
	
	/**
	 * @inheritDoc
	 */
	public function getObjectTypeName($definitionName) {
		if (isset($this->objectTypes[$definitionName])) {
			return $this->objectTypes[$definitionName];
		}
		
		return null;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getDescriptionLangVarCategory() {
		return $this->i18nLangVarCategory;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getI18nLangVarPrefix() {
		return $this->i18nLangVarCategory.'.category';
	}
	
	/**
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	public function getMaximumNestingLevel() {
		return $this->maximumNestingLevel;
	}
	
	/**
	 * @inheritDoc
	 */
	public function getTitleLangVarCategory() {
		return $this->i18nLangVarCategory;
	}
	
	/**
	 * @inheritDoc
	 */
	public function hasDescription() {
		return $this->hasDescription;
	}
}
