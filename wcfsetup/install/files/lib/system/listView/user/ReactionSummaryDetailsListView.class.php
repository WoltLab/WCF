<?php

namespace wcf\system\listView\user;

use wcf\data\DatabaseObject;
use wcf\data\like\ILikeObjectTypeProvider;
use wcf\data\like\IRestrictedLikeObjectTypeProvider;
use wcf\data\like\object\ILikeObject;
use wcf\data\like\ViewableLike;
use wcf\data\like\ViewableLikeList;
use wcf\event\listView\user\ReactionSummaryDetailsListViewInitialized;
use wcf\system\interaction\user\UserProfileInteractionsWithFollow;
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
        public readonly int $objectID,
        public readonly ?int $reactionTypeID = null,
    ) {
        $this->addAvailableSortFields([
            new ListViewSortField(
                'username',
                'wcf.user.username',
                '(SELECT username FROM wcf1_user WHERE userID = like_table.userID)'
            ),
        ]);

        $this->setAllowSorting(false);
        $this->setInteractionProvider(new UserProfileInteractionsWithFollow());
        $this->setDefaultSortField('username');
        $this->setItemsPerPage(100);
        $this->setCssClassName('simpleUserList');
        $this->setContainerCssClassName('simpleUserList__container');
        $this->setAdditionalHeaderContent($this->getSimpleFilterButtons());
    }

    #[\Override]
    protected function createObjectList(): ViewableLikeList
    {
        $likeList = new ViewableLikeList();
        $likeList->getConditionBuilder()->add('objectTypeID = ?', [
            ReactionHandler::getInstance()->getObjectType($this->objectType)->objectTypeID
        ]);
        $likeList->getConditionBuilder()->add('objectID = ?', [$this->objectID]);
        if ($this->reactionTypeID !== null) {
            $likeList->getConditionBuilder()->add('reactionTypeID = ?', [$this->reactionTypeID]);
        }

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

        return $this->getInteractionContextMenuComponent()->renderButton($item->getUserProfile());
    }

    #[\Override]
    protected function getInitializedEvent(): ReactionSummaryDetailsListViewInitialized
    {
        return new ReactionSummaryDetailsListViewInitialized($this);
    }

    #[\Override]
    public function getParameters(): array
    {
        $parameters = [
            'objectType' => $this->objectType,
            'objectID' => $this->objectID,
        ];

        if ($this->reactionTypeID !== null) {
            $parameters['reactionTypeID'] = $this->reactionTypeID;
        }

        return $parameters;
    }

    private function getSimpleFilterButtons(): string
    {
        $objectType = ReactionHandler::getInstance()->getObjectType($this->objectType);
        if ($objectType === null) {
            return '';
        }

        $sql = "SELECT COUNT(*) AS count, reactionTypeID FROM wcf1_like WHERE objectTypeID = ? AND objectID = ? GROUP BY reactionTypeID";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$objectType->objectTypeID, $this->objectID]);
        $reactionCounts = $statement->fetchMap('reactionTypeID', 'count');
        if (\count($reactionCounts) <= 1) {
            // Skip filtering if only one type is present.
            return '';
        }

        $totalCount = 0;
        foreach ($reactionCounts as $count) {
            $totalCount += $count;
        }

        return WCF::getTPL()->render(
            'wcf',
            'reactionSummaryDetailsFilterButtons',
            [
                'view' => $this,
                'totalCount' => $totalCount,
                'reactionCounts' => $reactionCounts,
                'reactionTypes' => \array_filter(
                    ReactionHandler::getInstance()->getReactionTypes(),
                    static fn($reactionType) => isset($reactionCounts[$reactionType->reactionTypeID])
                ),
                'reactionTypeID' => $this->reactionTypeID,
            ]
        );
    }
}
