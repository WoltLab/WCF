<?php

namespace wcf\system\view;

use SystemException;
use wcf\data\comment\StructuredCommentList;
use wcf\system\comment\CommentHandler;
use wcf\system\comment\manager\ICommentManager;

/**
 * Represents a comments view.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class CommentsView
{
    private int $objectTypeID;
    private StructuredCommentList $commentList;
    private ICommentManager $commentManager;

    public function __construct(
        public readonly string $objectTypeName,
        public readonly int $objectID,
        public readonly string $commentContainerID,
        public readonly bool $canAddComments = true,
        public readonly int $totalComments = 0,
        public readonly bool $showSection = true
    ) {
        $this->init();
    }

    private function init(): void
    {
        $objectTypeID = CommentHandler::getInstance()
            ->getObjectTypeID($this->objectTypeName);
        if (!$objectTypeID) {
            throw new SystemException("Unable to find object type '{$this->objectTypeName}'");
        }

        $this->objectTypeID = $objectTypeID;

        $this->commentManager = CommentHandler::getInstance()
            ->getObjectType($this->objectTypeID)
            ->getProcessor();
        $this->commentList = CommentHandler::getInstance()
            ->getCommentList($this->commentManager, $this->objectTypeID, $this->objectID);
    }

    public function getObjectTypeID(): int
    {
        return $this->objectTypeID;
    }

    public function getCommentManager(): ICommentManager
    {
        return $this->commentManager;
    }

    public function getCommentList(): StructuredCommentList
    {
        return $this->commentList;
    }

    public function getLastCommentTime(): int
    {
        return $this->commentList->getMinCommentTime();
    }

    public function getLikeData(): array
    {
        if (!MODULE_LIKE) {
            return [];
        }

        return $this->commentList->getLikeData();
    }

    public function isVisible(): bool
    {
        return $this->canAddComments || \count($this->getCommentList());
    }
}
