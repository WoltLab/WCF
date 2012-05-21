<?php
namespace wcf\system\category;
use wcf\data\category\CategoryEditor;

/**
 * Every category type has to implement this interface.
 *
 * @author	Matthias Schmidt
 * @copyright	2001-2012 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.category
 * @category 	Community Framework
 */
interface ICategoryType {
	/**
	 * Is called right after the given category is deleted.
	 * 
	 * @param	wcf\data\category\CategoryEditor	$categoryEditor
	 */
	public function afterDeletion(CategoryEditor $categoryEditor);
	
	/**
	 * Returns true, if the active user can add a category of this type.
	 * 
	 * @return	boolean
	 */
	public function canAddCategory();
	
	/**
	 * Returns true, if the active user can delete a category of this type.
	 * 
	 * @return	boolean
	 */
	public function canDeleteCategory();
	
	/**
	 * Returns true, if the active user can edit a category of this type.
	 * 
	 * @return	boolean
	 */
	public function canEditCategory();
	
	/**
	 * Returns the name of the acl object type for categories of this type.
	 * Returns null if categories of this type don't support acl.
	 * 
	 * @return	string
	 */
	public function getACLObjectTypeName();
	
	/**
	 * Returns the name of the collapsible object type for categories of this
	 * type. Returns null if categories of this type don't support collapsing.
	 * 
	 * @return	string
	 */
	public function getCollapsibleObjectTypeName();
	
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
	 * Returns the prefix used for language variables in templates. If a custom
	 * prefix is used (not 'wcf.category'), a fallback to the default prefix
	 * ('wcf.category') is used if the relevant language variable doesn't exist
	 * in the custom category.
	 * 
	 * @return	string
	 */
	public function getLangVarPrefix();
	
	/**
	 * Returns the language variable category for the title language variables
	 * of categories of this type.
	 * 
	 * @return	string
	 */
	public function getTitleLangVarCategory();
}
