<?php

namespace wcf\system\category;

use wcf\data\category\Category;
use wcf\data\category\CategoryEditor;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\request\LinkHandler;
use wcf\system\user\object\watch\UserObjectWatchHandler;
use wcf\system\WCF;

/**
 * Abstract implementation of a category type.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class AbstractCategoryType implements ICategoryType
{
    /**
     * indicates if categories of this type may have no empty description
     * @var bool
     */
    protected $forceDescription = true;

    /**
     * indicates if categories of this type have descriptions
     * @var bool
     */
    protected $hasDescription = true;

    /**
     * language category which contains the language variables of i18n values
     * @var string
     */
    protected $i18nLangVarCategory = 'wcf.category';

    /**
     * prefix used for language variables in templates
     * @var string
     */
    protected $langVarPrefix = '';

    /**
     * permission prefix for the add/delete/edit permissions
     * @var string
     */
    protected $permissionPrefix = '';

    /**
     * maximum category nesting label
     * @var int
     */
    protected $maximumNestingLevel = -1;

    /**
     * name of the object types associated with categories of this type (the
     * key is the definition name and value the object type name)
     * @var string[]
     */
    protected $objectTypes = [];

    #[\Override]
    public function afterDeletion(CategoryEditor $categoryEditor)
    {
        $categoryIDs = \array_keys(CategoryHandler::getInstance()->getChildCategories($categoryEditor->categoryID));

        if ($categoryIDs !== []) {
            // move child categories to parent category
            $conditionBuilder = new PreparedStatementConditionBuilder();
            $conditionBuilder->add("categoryID IN (?)", [$categoryIDs]);
            $sql = "UPDATE  wcf1_category
                    SET     parentCategoryID = ?
                    " . $conditionBuilder;
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute(\array_merge([$categoryEditor->parentCategoryID], $conditionBuilder->getParameters()));
        }

        $objectWatchObjectType = $this->getObjectTypeName('com.woltlab.wcf.user.objectWatch');
        if ($objectWatchObjectType !== null) {
            UserObjectWatchHandler::getInstance()
                ->deleteObjects($objectWatchObjectType, [$categoryEditor->categoryID]);
        }
    }

    #[\Override]
    public function beforeDeletion(CategoryEditor $categoryEditor)
    {
        // does nothing
    }

    #[\Override]
    public function canAddCategory()
    {
        return WCF::getSession()->hasPermission($this->permissionPrefix . '.canAddCategory');
    }

    #[\Override]
    public function canDeleteCategory()
    {
        return WCF::getSession()->hasPermission($this->permissionPrefix . '.canDeleteCategory');
    }

    #[\Override]
    public function canEditCategory()
    {
        return WCF::getSession()->hasPermission($this->permissionPrefix . '.canEditCategory');
    }

    #[\Override]
    public function changedParentCategories(array $categoryData)
    {
        // does nothing
    }

    #[\Override]
    public function forceDescription()
    {
        return $this->hasDescription() && $this->forceDescription;
    }

    #[\Override]
    public function getApplication()
    {
        $classParts = \explode('\\', static::class);

        return $classParts[0];
    }

    #[\Override]
    public function getObjectTypeName(string $definitionName)
    {
        return $this->objectTypes[$definitionName] ?? null;
    }

    #[\Override]
    public function getDescriptionLangVarCategory()
    {
        return $this->i18nLangVarCategory;
    }

    #[\Override]
    public function getI18nLangVarPrefix()
    {
        return $this->i18nLangVarCategory . '.category';
    }

    #[\Override]
    public function getLanguageVariable(string $name, bool $optional = false)
    {
        if ($this->langVarPrefix !== '') {
            $value = WCF::getLanguage()->getDynamicVariable($this->langVarPrefix . '.' . $name, [], true);
            if ($value !== '') {
                return $value;
            }
        }

        return WCF::getLanguage()->getDynamicVariable('wcf.category.' . $name, [], $optional);
    }

    #[\Override]
    public function getMaximumNestingLevel()
    {
        return $this->maximumNestingLevel;
    }

    #[\Override]
    public function getTitleLangVarCategory()
    {
        return $this->i18nLangVarCategory;
    }

    #[\Override]
    public function hasDescription()
    {
        return $this->hasDescription;
    }

    /**
     * @since   5.2
     */
    #[\Override]
    public function supportsHtmlDescription()
    {
        return false;
    }

    #[\Override]
    public function getEditFormLink(Category $category): string
    {
        $controllerClass = $this->getEditControllerClass();
        if ($controllerClass === '') {
            return '';
        }

        return LinkHandler::getInstance()->getControllerLink(
            $controllerClass,
            ['object' => $category]
        );
    }

    #[\Override]
    public function getAddFormLink(?Category $parentCategory = null): string
    {
        $controllerClass = $this->getAddControllerClass();
        if ($controllerClass === '') {
            return '';
        }

        $parameters = [];
        if ($parentCategory !== null) {
            $parameters['parentCategoryID'] = $parentCategory->categoryID;
        }

        return LinkHandler::getInstance()->getControllerLink(
            $controllerClass,
            $parameters
        );
    }

    /**
     * Returns the name of the controller class used to edit categories of this type.
     *
     * @since 6.3
     */
    public function getEditControllerClass(): string
    {
        return '';
    }

    /**
     * Returns the name of the controller class used to add categories of this type.
     *
     * @since 6.3
     */
    public function getAddControllerClass(): string
    {
        return '';
    }
}
