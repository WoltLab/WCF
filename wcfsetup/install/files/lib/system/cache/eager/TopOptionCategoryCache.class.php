<?php

namespace wcf\system\cache\eager;

use wcf\data\option\category\OptionCategory;
use wcf\data\option\category\OptionCategoryList;
use wcf\data\option\OptionList;

/**
 * Returns the list with top option categories which contain options.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 *
 * @extends AbstractEagerCache<array<int, OptionCategory>>
 */
final class TopOptionCategoryCache extends AbstractEagerCache
{
    /**
     * list of option categories which directly contain options
     * @var string[]
     */
    private array $categoriesWithOptions = [];

    /**
     * list of option categories grouped by the name of their parent category
     * @var array<string, OptionCategory[]>
     */
    private array $categoryStructure = [];

    /**
     * @return array<int, OptionCategory>
     */
    #[\Override]
    protected function getCacheData(): array
    {
        $optionCategoryList = new OptionCategoryList();
        $optionCategoryList->readObjects();
        $optionCategories = $optionCategoryList->getObjects();

        // build category structure
        $this->categoryStructure = [];
        foreach ($optionCategories as $optionCategory) {
            if (!isset($this->categoryStructure[$optionCategory->parentCategoryName])) {
                $this->categoryStructure[$optionCategory->parentCategoryName] = [];
            }

            $this->categoryStructure[$optionCategory->parentCategoryName][] = $optionCategory;
        }

        $optionList = new OptionList();
        $optionList->readObjects();

        // collect names of categories which contain options
        foreach ($optionList as $option) {
            if (!isset($this->categoriesWithOptions[$option->categoryName])) {
                $this->categoriesWithOptions[$option->categoryName] = $option->categoryName;
            }
        }

        // collect top categories which contain options
        $topCategories = [];
        foreach ($this->categoryStructure[""] as $topCategory) {
            if ($this->containsOptions($topCategory)) {
                $topCategories[$topCategory->categoryID] = $topCategory;
            }
        }

        return $topCategories;
    }

    /**
     * Returns true if the given category or one of its child categories contains
     * options.
     */
    private function containsOptions(OptionCategory $topCategory): bool
    {
        // check if category directly contains options
        if (isset($this->categoriesWithOptions[$topCategory->categoryName])) {
            return true;
        }

        if (!isset($this->categoryStructure[$topCategory->categoryName])) {
            // if category directly contains no options and has no child
            // categories, it contains no options at all
            return false;
        }

        // check child categories
        foreach ($this->categoryStructure[$topCategory->categoryName] as $category) {
            if ($this->containsOptions($category)) {
                return true;
            }
        }

        return false;
    }
}
