<?php

namespace wcf\system\search\acp;

use wcf\data\user\group\option\category\UserGroupOptionCategoryList;
use wcf\data\user\group\option\UserGroupOption;
use wcf\system\cache\builder\UserGroupOptionCacheBuilder;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;

/**
 * ACP search provider implementation for user group options.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserGroupOptionACPSearchResultProvider extends AbstractCategorizedACPSearchResultProvider implements
    IACPSearchResultProvider
{
    /**
     * @inheritDoc
     */
    protected $listClassName = UserGroupOptionCategoryList::class;

    private array $restrictedOptionNames = [
        'admin.configuration.package.canUpdatePackage',
        'admin.configuration.package.canEditServer',
        'admin.user.canMailUser',
        'admin.management.canManageCronjob',
        'admin.management.canRebuildData',
        'admin.management.canImportData',
    ];

    /**
     * @inheritDoc
     */
    public function search($query)
    {
        if (!WCF::getSession()->getPermission('admin.user.canEditGroup')) {
            return [];
        }

        $results = [];

        // search by language item
        $conditions = new PreparedStatementConditionBuilder();
        $conditions->add("languageID = ?", [WCF::getLanguage()->languageID]);
        $conditions->add("languageItem LIKE ?", ['wcf.acp.group.option.%']);
        $conditions->add("languageItemValue LIKE ?", ['%' . $query . '%']);

        $sql = "SELECT      languageItem
                FROM        wcf" . WCF_N . "_language_item
                " . $conditions . "
                ORDER BY    languageItemValue ASC";
        $statement = WCF::getDB()->prepareStatement($sql); // don't use a limit here
        $statement->execute($conditions->getParameters());
        $languageItems = [];
        while ($languageItem = $statement->fetchColumn()) {
            // ignore descriptions
            if (\substr($languageItem, -12) == '.description') {
                continue;
            }

            $itemName = \preg_replace('~^([a-z]+)\.acp\.group\.option\.~', '', $languageItem);
            $languageItems[$itemName] = $languageItem;
        }

        if (empty($languageItems) && !(ENABLE_DEBUG_MODE && ENABLE_DEVELOPER_TOOLS)) {
            return [];
        }

        $conditions = new PreparedStatementConditionBuilder(true, 'OR');
        if (!empty($languageItems)) {
            $conditions->add("optionName IN (?)", [\array_keys($languageItems)]);
        }
        if (ENABLE_DEBUG_MODE && ENABLE_DEVELOPER_TOOLS) {
            $conditions->add('optionName LIKE ?', ['%' . $query . '%']);
        }

        $sql = "SELECT  optionID, optionName, categoryName, permissions, options
                FROM    wcf" . WCF_N . "_user_group_option
                " . $conditions;
        $statement = WCF::getDB()->prepareStatement($sql); // don't use a limit here
        $statement->execute($conditions->getParameters());

        $optionCategories = UserGroupOptionCacheBuilder::getInstance()->getData([], 'categories');

        while ($userGroupOption = $statement->fetchObject(UserGroupOption::class)) {
            // category is not accessible
            if (!$this->isValid($userGroupOption->categoryName)) {
                continue;
            }

            // option is not accessible
            if (!$this->validate($userGroupOption)) {
                continue;
            }

            if ($this->isUnavailableOption($userGroupOption)) {
                continue;
            }

            $link = LinkHandler::getInstance()->getLink('UserGroupOption', ['id' => $userGroupOption->optionID]);
            $categoryName = $userGroupOption->categoryName;
            $parentCategories = [];
            while (isset($optionCategories[$categoryName])) {
                \array_unshift(
                    $parentCategories,
                    'wcf.acp.group.option.category.' . $optionCategories[$categoryName]->categoryName
                );

                $categoryName = $optionCategories[$categoryName]->parentCategoryName;
            }

            if (isset($languageItems[$userGroupOption->optionName])) {
                $languageItem = $languageItems[$userGroupOption->optionName];
            } else {
                $languageItem = 'wcf.acp.group.option.' . $userGroupOption->optionName;
            }

            $results[] = new ACPSearchResult(
                WCF::getLanguage()->getDynamicVariable($languageItem),
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
     * @since 6.0
     */
    private function isUnavailableOption(UserGroupOption $userGroupOption): bool
    {
        if (!\defined('ENABLE_ENTERPRISE_MODE') || !\ENABLE_ENTERPRISE_MODE) {
            return false;
        }

        if (!\in_array($userGroupOption->optionName, $this->restrictedOptionNames, true)) {
            return false;
        }

        if (WCF::getUser()->hasOwnerAccess()) {
            return false;
        }

        // Allow the option to appear if the user has this permission.
        if (WCF::getSession()->getPermission($userGroupOption->optionName)) {
            return false;
        }

        return true;
    }
}
