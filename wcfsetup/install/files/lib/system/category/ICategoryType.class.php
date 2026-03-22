<?php

namespace wcf\system\category;

use wcf\data\category\CategoryEditor;

/**
 * Every category type has to implement this interface.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface ICategoryType
{
    /**
     * Is called right after the given category is deleted.
     *
     * @return void
     */
    public function afterDeletion(CategoryEditor $categoryEditor);

    /**
     * Is called before the given category is deleted.
     *
     * @since 3.1
     * @return void
     */
    public function beforeDeletion(CategoryEditor $categoryEditor);

    /**
     * Returns true if the active user can add a category of this type.
     *
     * @return bool
     */
    public function canAddCategory();

    /**
     * Returns true if the active user can delete a category of this type.
     *
     * @return bool
     */
    public function canDeleteCategory();

    /**
     * Returns true if the active user can edit a category of this type.
     *
     * @return bool
     */
    public function canEditCategory();

    /**
     * Is called after categories were assigned different parent categories.
     *
     * @param array<int, array{
     *  newParentCategoryID: int,
     *  oldParentCategoryID: ?int,
     * }> $categoryData
     * @return void
     */
    public function changedParentCategories(array $categoryData);

    /**
     * Returns true if a category of this type may have no empty description.
     *
     * @return bool
     */
    public function forceDescription();

    /**
     * Returns abbreviation of the application this category type belongs to.
     *
     * @return string
     */
    public function getApplication();

    /**
     * Returns the name of the object type of the definition with the given
     * name for categories of this type or `null` if no such object type exists.
     *
     * @return ?string
     */
    public function getObjectTypeName(string $definitionName);

    /**
     * Returns the language variable category for the description language
     * variables of categories of this type.
     *
     * @return string
     */
    public function getDescriptionLangVarCategory();

    /**
     * Returns the prefix used for language variables of i18n values.
     *
     * @return string
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
     * @return string
     */
    public function getLanguageVariable(string $name, bool $optional = false);

    /**
     * Returns the maximum category nesting level for this type. "-1" means
     * that there is no maximum.
     *
     * @return int
     */
    public function getMaximumNestingLevel();

    /**
     * Returns the language variable category for the title language variables
     * of categories of this type.
     *
     * @return string
     */
    public function getTitleLangVarCategory();

    /**
     * Returns true if categories of this type have descriptions.
     *
     * @return bool
     */
    public function hasDescription();

    /**
     * Returns `true` if the descriptions of categories of this type support HTML.
     *
     * @return bool
     * @since 5.2
     */
    public function supportsHtmlDescription();
}
