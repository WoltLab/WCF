<?php

namespace wcf\system\listView\user;

use wcf\data\user\trophy\UserTrophy;
use wcf\data\user\trophy\UserTrophyList;
use wcf\event\listView\user\UserTrophyListViewInitialized;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\listView\AbstractListView;
use wcf\system\listView\ListViewSortField;
use wcf\system\WCF;

/**
 * List view that shows the trophies of a specific user.
 *
 * @author      Joshua Ruesweg, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends AbstractListView<UserTrophy, UserTrophyList>
 */
class UserTrophyListView extends AbstractListView
{
    public function __construct(
        public readonly int $userID
    ) {
        $this->addAvailableSortFields([
            new ListViewSortField(
                'time',
                'wcf.global.date',
                defaultSortOrder: 'DESC',
            ),
        ]);

        $this->setAllowSorting(false);
        $this->setDefaultSortField('time');
        $this->setDefaultSortOrder('DESC');
        $this->setCssClassName('userTrophyList');
        $this->setContainerCssClassName('userTrophyList__container');
    }

    #[\Override]
    protected function createObjectList(): UserTrophyList
    {
        $list = new UserTrophyList();
        if ($list->sqlConditionJoins !== '') {
            $list->sqlConditionJoins .= ' ';
        }
        $list->sqlConditionJoins .= '
            LEFT JOIN   wcf1_trophy trophy
            ON          user_trophy.trophyID = trophy.trophyID
            LEFT JOIN   wcf1_category category
            ON          trophy.categoryID = category.categoryID';
        if ($list->sqlJoins !== '') {
            $list->sqlJoins .= ' ';
        }
        $list->sqlJoins .= '
            LEFT JOIN   wcf1_trophy trophy
            ON          user_trophy.trophyID = trophy.trophyID
            LEFT JOIN   wcf1_category category
            ON          trophy.categoryID = category.categoryID';

        $list->getConditionBuilder()->add('user_trophy.userID = ?', [$this->userID]);
        $list->getConditionBuilder()->add('trophy.isDisabled = ?', [0]);
        $list->getConditionBuilder()->add('category.isDisabled = ?', [0]);

        return $list;
    }

    #[\Override]
    public function isAccessible(): bool
    {
        if (\MODULE_TROPHY === 0) {
            return false;
        }

        if (!WCF::getSession()->hasPermission('user.profile.trophy.canSeeTrophies')) {
            return false;
        }

        $userProfile = UserProfileRuntimeCache::getInstance()->getObject($this->userID);
        if ($userProfile === null) {
            return false;
        }

        return $userProfile->isAccessible('canViewTrophies') || $userProfile->userID === WCF::getSession()->userID;
    }

    #[\Override]
    public function renderItems(): string
    {
        return WCF::getTPL()->render('wcf', 'userTrophyListItems', ['view' => $this]);
    }

    #[\Override]
    protected function getInitializedEvent(): UserTrophyListViewInitialized
    {
        return new UserTrophyListViewInitialized($this);
    }

    #[\Override]
    public function getParameters(): array
    {
        return [
            'userID' => $this->userID,
        ];
    }
}
