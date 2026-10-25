<?php

namespace wcf\system\listView\user;

use wcf\data\user\ignore\UserIgnore;
use wcf\data\user\UserProfile;
use wcf\data\user\UserProfileList;
use wcf\event\listView\user\IgnoredUserListViewInitialized;
use wcf\system\WCF;

/**
 * List view that shows the users the active user ignores.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
class IgnoredUserListView extends AbstractSimpleUserListView
{
    /**
     * @var array<int, int>
     */
    private array $ignoreTypes;

    #[\Override]
    protected function createObjectList(): UserProfileList
    {
        $list = new UserProfileList();
        $list->getConditionBuilder()->add(
            "user_table.userID IN (SELECT ignoreUserID FROM wcf1_user_ignore WHERE userID = ?)",
            [WCF::getUser()->userID]
        );

        return $list;
    }

    #[\Override]
    public function isAccessible(): bool
    {
        return !WCF::getUser()->isGuest();
    }

    #[\Override]
    protected function getInitializedEvent(): IgnoredUserListViewInitialized
    {
        return new IgnoredUserListViewInitialized($this);
    }

    #[\Override]
    public function getItemDescription(UserProfile $user): string
    {
        $this->loadIgnoreTypes();

        return match ($this->ignoreTypes[$user->userID] ?? UserIgnore::TYPE_NO_IGNORE) {
            UserIgnore::TYPE_BLOCK_DIRECT_CONTACT => WCF::getLanguage()->get('wcf.user.ignore.type.blockDirectContact'),
            UserIgnore::TYPE_HIDE_MESSAGES => WCF::getLanguage()->get('wcf.user.ignore.type.hideMessages'),
            default => '',
        };
    }

    private function loadIgnoreTypes(): void
    {
        if (isset($this->ignoreTypes)) {
            return;
        }

        $sql = "SELECT ignoreUserID, type FROM wcf1_user_ignore WHERE userID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([WCF::getUser()->userID]);
        $this->ignoreTypes = $statement->fetchMap('ignoreUserID', 'type');
    }
}
