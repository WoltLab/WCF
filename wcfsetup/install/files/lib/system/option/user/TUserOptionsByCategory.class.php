<?php

namespace wcf\system\option\user;

use wcf\data\option\category\OptionCategory;
use wcf\data\user\option\UserOption;
use wcf\system\cache\builder\UserOptionCacheBuilder;

/**
 * Provides methods to read the user options grouped by their category.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
trait TUserOptionsByCategory
{
    /**
     * Returns the options of the child categories of the given category that
     * pass the given filter, grouped by the name of the child category. The
     * options of deeper categories are added to their child category.
     *
     * @param \Closure(UserOption): bool $filter
     * @return array<string, UserOption[]>
     */
    protected function getUserOptionsByCategory(string $parentCategoryName, \Closure $filter): array
    {
        $categories = UserOptionCacheBuilder::getInstance()->getData([], 'categories');
        $categoryStructure = UserOptionCacheBuilder::getInstance()->getData([], 'categoryStructure');

        $options = [];
        foreach ($categoryStructure[$parentCategoryName] ?? [] as $categoryName) {
            if (!$this->isAccessibleUserOptionCategory($categories[$categoryName])) {
                continue;
            }

            $categoryOptions = $this->getUserOptionsOfCategory($categoryName, $filter);
            if ($categoryOptions !== []) {
                $options[$categoryName] = $categoryOptions;
            }
        }

        return $options;
    }

    /**
     * Returns the options of the given category and its child categories that
     * pass the given filter.
     *
     * @param \Closure(UserOption): bool $filter
     * @return UserOption[]
     */
    private function getUserOptionsOfCategory(string $categoryName, \Closure $filter): array
    {
        $categories = UserOptionCacheBuilder::getInstance()->getData([], 'categories');
        $categoryStructure = UserOptionCacheBuilder::getInstance()->getData([], 'categoryStructure');
        $cachedOptions = UserOptionCacheBuilder::getInstance()->getData([], 'options');
        $optionToCategories = UserOptionCacheBuilder::getInstance()->getData([], 'optionToCategories');

        $options = [];
        foreach ($optionToCategories[$categoryName] ?? [] as $optionName) {
            $option = $cachedOptions[$optionName];
            if ($filter($option)) {
                $options[] = $option;
            }
        }

        foreach ($categoryStructure[$categoryName] ?? [] as $childCategoryName) {
            if ($this->isAccessibleUserOptionCategory($categories[$childCategoryName])) {
                $options = \array_merge($options, $this->getUserOptionsOfCategory($childCategoryName, $filter));
            }
        }

        return $options;
    }

    private function isAccessibleUserOptionCategory(OptionCategory $category): bool
    {
        return $category->categoryName !== 'hidden'
            && $category->validateOptions()
            && $category->validatePermissions();
    }
}
