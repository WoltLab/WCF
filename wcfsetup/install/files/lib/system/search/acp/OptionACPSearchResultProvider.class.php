<?php

namespace wcf\system\search\acp;

use wcf\data\option\category\OptionCategory;
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
     * Mirrors the blacklist of `OptionHandler`, keep both in sync.
     *
     * @var list<string>
     */
    private array $restrictedCategoryNames = [
        'general.cache',
        'general.mail.send',
        'general.page.seo',
        'general.system.cookie',
        'general.system.http',
        'general.system.image',
        'general.system.packageServer',
        'general.system.proxy',
        'general.system.search',
        'module.development',
        'security.general.secrets',
    ];

    /**
     * Mirrors the blacklist of `OptionHandler`, keep both in sync.
     *
     * @var list<string>
     */
    private array $restrictedOptionNames = [
        'mail_from_address',
    ];

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
                FROM        wcf" . WCF_N . "_language_item
                " . $conditions . "
                ORDER BY    languageItemValue ASC";
        $statement = WCF::getDB()->prepareStatement($sql); // don't use a limit here
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
                FROM    wcf" . WCF_N . "_option
                " . $conditions;
        $statement = WCF::getDB()->prepareStatement($sql); // don't use a limit here
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

            if ($this->isUnavailableOption($option, $optionCategories)) {
                continue;
            }

            $link = LinkHandler::getInstance()->getLink('Option', [
                'id' => $this->getCategoryID($this->getTopCategory($option->categoryName)->parentCategoryName),
            ], 'optionName=' . \rawurlencode($option->optionName)
                . '#category_' . \rawurlencode($this->getCategoryName($option->categoryName)));
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

    /**
     * @param array<string, OptionCategory> $optionCategories
     * @since 6.0
     */
    private function isUnavailableOption(Option $option, array $optionCategories): bool
    {
        if (!ENABLE_ENTERPRISE_MODE || WCF::getUser()->hasOwnerAccess()) {
            return false;
        }

        if (\in_array($option->optionName, $this->restrictedOptionNames, true)) {
            return true;
        }

        // A blacklisted category hides its child categories, too.
        $categoryName = $option->categoryName;
        while (isset($optionCategories[$categoryName])) {
            if (\in_array($categoryName, $this->restrictedCategoryNames, true)) {
                return true;
            }

            $categoryName = $optionCategories[$categoryName]->parentCategoryName;
        }

        return false;
    }
}
