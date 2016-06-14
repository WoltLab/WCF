<?php
namespace wcf\system\category;
use wcf\data\category\CategoryEditor;

/**
 * Every category type has to implement this interface.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2016 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Category
 */
interface ICategoryType {
	/**
	 * Is called right after the given category is deleted.
	 * 
	 * @param	\wcf\data\category\CategoryEditor	$categoryEditor
	 */
	public function afterDeletion(CategoryEditor $categoryEditor);
	
	/**
	 * Returns true if the active user can add a category of this type.
	 * 
	 * @return	boolean
	 */
	public function canAddCategory();
	
	/**
	 * Returns true if the active user can delete a category of this type.
	 * 
	 * @return	boolean
	 */
	public function canDeleteCategory();
	
	/**
	 * Returns true if the active user can edit a category of this type.
	 * 
	 * @return	boolean
	 */
	public function canEditCategory();
	
	/**
	 * Is called after categories were assigned different parent categories.
	 * 
	 * Array structure:
	 * [
	 * 	categoryID => [
	 * 		oldParentCategoryID => 1,
	 * 		newParentCategoryID => 2
	 * 	],
	 * 	categoryID => [
	 * 		oldParentCategoryID => null,
	 * 		newParentCategoryID => 2
	 * 	],
	 * ]
	 * 
	 * @param	array		$categoryData
	 */
	public function changedParentCategories(array $categoryData);
	
	/**
	 * Returns true if a category of this type may have no empty description.
	 * 
	 * @return	boolean
	 */
	public function forceDescription();
	
	/**
	 * Returns abbreviation of the application this category type belongs to.
	 * 
	 * @return	string
	 */
	public function getApplication();
	
	/**
	 * Returns the name of the object type of the definition with the given
	 * name for categories of this type. If categories of this type are no
	 * object of the relevant type, null is returned.
	 * 
	 * @param	string		$definitionName
	 * @return	string
	 */
	public function getObjectTypeName($definitionName);
	
	/**
	 * Returns the language variable category for the description language
	 * variables of categories of this type.
	 * 
	 * @return	string
	 */
	public function getDescriptionLangVarCategory();
	
	/**
	 * Returns the prefix used for language variables of i18n values.
	 * 
	 * @return	string
	 */
	public function getI18nLangVarPrefix();
	
	/**
	 * Returns the language variable value with the given name. The given name
	 * may not contain the language category prefix.
	 * 
	 * If "{your.language.category}.list" is wanted, $name has to be "list".
	 * If the specific language variable for this category type doesn't exist,
	 * a fallback to the default variables (in this example "wcf.category.list")
	 * is used.
	 * 
	 * @param	string		$name
	 * @param	boolean		$optional
	 * @return	string
	 */
	public function getLanguageVariable($name, $optional = false);
	
	/**
	 * Returns the maximum category nesting level for this type. "-1" means
	 * that there is no maximum.
	 * 
	 * @return	integer
	 */
	public function getMaximumNestingLevel();
	
	/**
	 * Returns the language variable category for the title language variables
	 * of categories of this type.
	 * 
	 * @return	string
	 */
	public function getTitleLangVarCategory();
	
	/**
	 * Returns true if categories of this type have descriptions.
	 * 
	 * @return	boolean
	 */
	public function hasDescription();
}
