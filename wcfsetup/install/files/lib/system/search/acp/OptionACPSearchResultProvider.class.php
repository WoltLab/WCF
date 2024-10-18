<?php

namespace wcf\system\search\acp;

use wcf\data\option\category\OptionCategoryList;
use wcf\data\option\Option;
use wcf\system\cache\builder\OptionCacheBuilder;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * ACP search provider implementation for options (and option categories).
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class OptionACPSearchResultProvider extends AbstractCategorizedACPSearchResultProvider implements
    IACPSearchResultProvider
{
    /**
     * @inheritDoc
     */
    protected $listClassName = OptionCategoryList::class;

    /**
     * @inheritDoc
     */
    public function search($query)
    {
        if (!WCF::getSession()->getPermission('admin.configuration.canEditOption')) {
            return [];
        }

        $results = [];

        // search by language item
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("languageID = ?", [WCF::getLanguage()->languageID]);
        $conditions->add("languageItem LIKE ?", ['wcf.acp.option.%']);
        $conditions->add("languageItemValue LIKE ?", ['%' . $query . '%']);

        $sql = "SELECT      languageItem
                FROM        wcf1_language_item
                " . $conditions . "
                ORDER BY    languageItemValue ASC";
        $statement = WCF::getDB()->prepare($sql); // don't use a limit here
        $statement->execute($conditions->getParameters());
        $optionNames = $categoryNames = [];
        while ($languageItem = $statement->fetchColumn()) {
            $optionName = \preg_replace('~^([a-z]+)\.acp\.option\.~', '', $languageItem);

            if (\strpos($optionName, 'category.') === 0) {
                // 9 = length of `category.`
                $categoryNames[] = \substr($optionName, 9);
            } else {
                $optionNames[] = $optionName;
            }
        }

        if (empty($optionNames) && empty($categoryNames) && !(ENABLE_DEBUG_MODE && ENABLE_DEVELOPER_TOOLS)) {
            return [];
        }

        $conditions = new PreparedStatementConditionBuilder(true, 'OR');
        if (!empty($categoryNames)) {
            $conditions->add('categoryName IN (?)', [$categoryNames]);
        }
        if (!empty($optionNames)) {
            $conditions->add('optionName IN (?)', [$optionNames]);
        }
        if (ENABLE_DEBUG_MODE && ENABLE_DEVELOPER_TOOLS) {
            $conditions->add('optionName LIKE ?', ['%' . $query . '%']);
        }

        $sql = "SELECT  optionName, categoryName, options, permissions, hidden
                FROM    wcf1_option
                " . $conditions;
        $statement = WCF::getDB()->prepare($sql); // don't use a limit here
        $statement->execute($conditions->getParameters());

        $optionCategories = OptionCacheBuilder::getInstance()->getData([], 'categories');

        /** @var Option $option */
        while ($option = $statement->fetchObject(Option::class)) {
            // category is not accessible
            if (!$this->isValid($option->categoryName)) {
                continue;
            }

            // option is not accessible
            if (!$this->validate($option) || $option->hidden) {
                continue;
            }

            $link = LinkHandler::getInstance()->getLink('Option', [
                'id' => $this->getCategoryID($this->getTopCategory($option->categoryName)->parentCategoryName),
            ], 'optionName=' . $option->optionName . '#category_' . $this->getCategoryName($option->categoryName));
            $categoryName = $option->categoryName;
            $parentCategories = [];
            while (isset($optionCategories[$categoryName])) {
                \array_unshift(
                    $parentCategories,
                    'wcf.acp.option.category.' . $optionCategories[$categoryName]->categoryName
                );

                $categoryName = $optionCategories[$categoryName]->parentCategoryName;
            }

            $results[] = new ACPSearchResult(
                WCF::getLanguage()->get('wcf.acp.option.' . $option->optionName),
                $link,
                WCF::getLanguage()->getDynamicVariable(
                    'wcf.acp.search.result.subtitle',
                    ['pieces' => $parentCategories]
                )
            );
        }

        return $results;
    }
}
