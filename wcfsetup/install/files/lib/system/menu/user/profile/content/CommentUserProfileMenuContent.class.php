<?php

namespace wcf\system\menu\user\profile\content;

use wcf\system\comment\CommentHandler;
use wcf\system\comment\manager\ICommentManager;
use wcf\system\WCF;

/**
 * Handles user profile comment content.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class CommentUserProfileMenuContent implements IUserProfileMenuContent
{
    private ICommentManager $commentManager;

    #[\Override]
    public function getContent(int $userID): string
    {
        $objectTypeID = CommentHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.user.profileComment');

        $commentList = CommentHandler::getInstance()->getCommentList(
            $this->getCommentManager(),
            $objectTypeID,
            $userID
        );

        return WCF::getTPL()->render('wcf', 'userProfileCommentList', [
            'commentCanAdd' => $this->getCommentManager()->canAdd($userID),
            'commentList' => $commentList,
            'commentObjectTypeID' => $objectTypeID,
            'userID' => $userID,
            'lastCommentTime' => $commentList->getMinCommentTime(),
        ]);
    }

    private function getCommentManager(): ICommentManager
    {
        if (!isset($this->commentManager)) {
            $objectTypeID = CommentHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.user.profileComment');
            $this->commentManager = CommentHandler::getInstance()->getObjectType($objectTypeID)->getProcessor();
        }

        return $this->commentManager;
    }

    #[\Override]
    public function isVisible(int $userID): bool
    {
        return true;
    }
}
