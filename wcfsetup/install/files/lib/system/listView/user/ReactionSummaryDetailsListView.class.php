<?php

namespace wcf\system\listView\user;

use wcf\data\DatabaseObject;
use wcf\data\like\ILikeObjectTypeProvider;
use wcf\data\like\IRestrictedLikeObjectTypeProvider;
use wcf\data\like\object\ILikeObject;
use wcf\data\like\ViewableLike;
use wcf\data\like\ViewableLikeList;
use wcf\event\listView\user\ReactionSummaryDetailsListViewInitialized;
use wcf\system\interaction\user\UserProfileInteractions;
use wcf\system\listView\AbstractListView;
use wcf\system\listView\ListViewSortField;
use wcf\system\reaction\ReactionHandler;
use wcf\system\WCF;

/**
 * List view for the reaction summary details dialog.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends AbstractListView<ViewableLike, ViewableLikeList>
 */
class ReactionSummaryDetailsListView extends AbstractListView
{
    public function __construct(
        public readonly string $objectType,
        public readonly int $objectID
    ) {
        $this->addAvailableSortFields([
            new ListViewSortField(
                'username',
                'wcf.user.username',
                '(SELECT username FROM wcf1_user WHERE userID = like_table.userID)'
            ),
        ]);

        $this->setAllowSorting(false);
        $this->setInteractionProvider(new UserProfileInteractions());
        $this->setDefaultSortField('username');
        $this->setItemsPerPage(100);
        $this->setCssClassName('simpleUserList');
        $this->setContainerCssClassName('simpleUserList__container');
    }

    #[\Override]
    protected function createObjectList(): ViewableLikeList
    {
        $likeList = new ViewableLikeList();
        $likeList->getConditionBuilder()->add('objectTypeID = ?', [
            ReactionHandler::getInstance()->getObjectType($this->objectType)->objectTypeID
        ]);
        $likeList->getConditionBuilder()->add('objectID = ?', [$this->objectID]);

        return $likeList;
    }

    #[\Override]
    public function isAccessible(): bool
    {
        if (!WCF::getSession()->hasPermission('user.like.canViewLike')) {
            return false;
        }

        $objectType = ReactionHandler::getInstance()->getObjectType($this->objectType);
        if ($objectType === null) {
            return false;
        }

        $objectTypeProvider = $objectType->getProcessor();
        \assert($objectTypeProvider instanceof ILikeObjectTypeProvider);

        $likeableObject = $objectTypeProvider->getObjectByID($this->objectID);
        \assert($likeableObject instanceof ILikeObject);
        $likeableObject->setObjectType($objectType);

        if ($objectTypeProvider instanceof IRestrictedLikeObjectTypeProvider) {
            if (!$objectTypeProvider->canViewLikes($likeableObject)) {
                return false;
            }
        } elseif (!$objectTypeProvider->checkPermissions($likeableObject)) {
            return false;
        }

        return true;
    }

    #[\Override]
    public function renderItems(): string
    {
        return WCF::getTPL()->render('wcf', 'reactionSummaryDetailsListItems', ['view' => $this]);
    }

    #[\Override]
    public function renderInteractionContextMenuButton(DatabaseObject $item): string
    {
        if (!$this->hasInteractions()) {
            return '';
        }

        \assert($item instanceof ViewableLike);

        return $this->getInteractionContextMenuComponent()->renderButton($item->getUserProfile());
    }

    #[\Override]
    protected function getInitializedEvent(): ReactionSummaryDetailsListViewInitialized
    {
        return new ReactionSummaryDetailsListViewInitialized($this);
    }
}
