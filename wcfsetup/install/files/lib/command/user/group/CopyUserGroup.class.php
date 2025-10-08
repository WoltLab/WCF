<?php

namespace wcf\command\user\group;

use wcf\data\user\group\UserGroup;
use wcf\data\user\group\UserGroupAction;
use wcf\data\user\group\UserGroupEditor;
use wcf\system\cache\CacheHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\WCF;

/**
 * Copies a user group.
 *
 * @author      Olaf Braun
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class CopyUserGroup
{
    public function __construct(
        public readonly UserGroup $userGroup,
        public readonly bool $copyUserGroupOptions,
        public readonly bool $copyMembers,
        public readonly bool $copyACLOptions,
    ) {}

    public function __invoke(): UserGroup
    {
        if ($this->copyUserGroupOptions) {
            $optionValues = $this->getOptionValues($this->userGroup);
        } else {
            $optionValues = $this->getDefaultOptionValues();
        }

        $group = $this->copyUserGroup($this->userGroup, $optionValues);
        $groupEditor = new UserGroupEditor($group);
        $groupName = $this->updateGroupName($this->userGroup, $group);
        $groupDescription = $this->updateGroupDescription($this->userGroup, $group);
        $groupEditor->update([
            'groupDescription' => $groupDescription,
            'groupName' => $groupName,
        ]);

        $this->copyMembers($this->userGroup, $group);
        $this->copyACLOptions($this->userGroup, $group);

        LanguageFactory::getInstance()->deleteLanguageCache();
        UserGroupEditor::resetCache();

        return $group;
    }

    private function updateGroupName(UserGroup $oldGroup, UserGroup $newGroup): string
    {
        if (\preg_match('~^wcf\.acp\.group\.group\d+$~', $oldGroup->groupName)) {
            $groupName = 'wcf.acp.group.group' . $newGroup->groupID;

            // create group name language item
            $sql = "INSERT INTO wcf1_language_item
                                (languageID, languageItem, languageItemValue, languageItemOriginIsSystem, languageCategoryID, packageID)
                    SELECT      languageID, '" . $groupName . "', CONCAT(languageItemValue, ' (2)'), 0, languageCategoryID, packageID
                    FROM        wcf1_language_item
                    WHERE       languageItem = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$oldGroup->groupName]);
        } else {
            $groupName = $oldGroup->groupName . ' (2)';
        }

        return $groupName;
    }

    private function updateGroupDescription(UserGroup $oldGroup, UserGroup $newGroup): string
    {
        if (\preg_match('~^wcf\.acp\.group\.groupDescription\d+$~', $oldGroup->groupDescription)) {
            $groupDescription = 'wcf.acp.group.groupDescription' . $newGroup->groupID;

            // create group name language item
            $sql = "INSERT INTO wcf1_language_item
                                (languageID, languageItem, languageItemValue, languageItemOriginIsSystem, languageCategoryID, packageID)
                    SELECT      languageID, '" . $groupDescription . "', languageItemValue, 0, languageCategoryID, packageID
                    FROM        wcf1_language_item
                    WHERE       languageItem = ?";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute([$oldGroup->groupDescription]);
        } else {
            $groupDescription = $oldGroup->groupDescription;
        }

        return $groupDescription;
    }

    private function copyMembers(UserGroup $oldGroup, UserGroup $newGroup): void
    {
        if (!$this->copyMembers) {
            return;
        }

        $sql = "INSERT INTO wcf1_user_to_group
                            (userID, groupID)
                SELECT      userID, " . $newGroup->groupID . "
                FROM        wcf1_user_to_group
                WHERE       groupID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$oldGroup->groupID]);
    }

    private function copyACLOptions(UserGroup $oldGroup, UserGroup $newGroup): void
    {
        if (!$this->copyACLOptions) {
            return;
        }

        $sql = "INSERT INTO wcf1_acl_option_to_group
                        (optionID, objectID, groupID, optionValue)
                SELECT      optionID, objectID, " . $newGroup->groupID . ", optionValue
                FROM        wcf1_acl_option_to_group
                WHERE       groupID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$oldGroup->groupID]);

        // it is likely that applications or plugins use caches
        // for acl option values like for the labels which have
        // to be renewed after copying the acl options; because
        // there is no other way to delete these caches, we simply
        // delete all caches
        CacheHandler::getInstance()->flushAll();
    }

    /**
     * @param array<int, mixed> $optionValues
     */
    private function copyUserGroup(UserGroup $group, array $optionValues): UserGroup
    {
        $groupType = $group->groupType;
        // When copying special user groups of which only one may exist,
        // change the group type to 'other'.
        if (\in_array($groupType, [UserGroup::EVERYONE, UserGroup::GUESTS, UserGroup::USERS, UserGroup::OWNER])) {
            $groupType = UserGroup::OTHER;
        }

        $group = (new UserGroupAction([], 'create', [
            'data' => [
                'groupName' => $group->groupName,
                'groupDescription' => $group->groupDescription,
                'priority' => $group->priority,
                'userOnlineMarking' => $group->userOnlineMarking,
                'showOnTeamPage' => $group->showOnTeamPage,
                'groupType' => $groupType,
            ],
            'options' => $optionValues,
        ]))->executeAction()['returnValues'];
        \assert($group instanceof UserGroup);

        return $group;
    }

    /**
     * @return array<int, mixed>
     */
    private function getOptionValues(UserGroup $group): array
    {
        $sql = "SELECT  optionID, optionValue
                FROM    wcf1_user_group_option_value
                WHERE   groupID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$group->groupID]);

        return $statement->fetchMap('optionID', 'optionValue');
    }

    /**
     * @return array<int, mixed>
     */
    private function getDefaultOptionValues(): array
    {
        $sql = "SELECT  optionID, defaultValue AS optionValue
                FROM    wcf1_user_group_option";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();

        return $statement->fetchMap('optionID', 'optionValue');
    }
}
