<?php

namespace wcf\system\menu\user\profile\content;

use wcf\system\comment\CommentHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles user profile comment content.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class CommentUserProfileMenuContent extends SingletonFactory implements IUserProfileMenuContent
{
    /**
     * comment manager object
     * @var \wcf\system\comment\manager\ICommentManager
     */
    public $commentManager;

    /**
     * object type id
     * @var int
     */
    public $objectTypeID = 0;

    #[\Override]
    public function getContent(int $userID)
    {
        if ($this->commentManager === null) {
            $this->objectTypeID = CommentHandler::getInstance()->getObjectTypeID('com.woltlab.wcf.user.profileComment');
            $objectType = CommentHandler::getInstance()->getObjectType($this->objectTypeID);
            $this->commentManager = $objectType->getProcessor();
        }

        $commentList = CommentHandler::getInstance()->getCommentList(
            $this->commentManager,
            $this->objectTypeID,
            $userID
        );

        return WCF::getTPL()->render('wcf', 'userProfileCommentList', [
            'commentCanAdd' => $this->commentManager->canAdd($userID),
            'commentList' => $commentList,
            'commentObjectTypeID' => $this->objectTypeID,
            'userID' => $userID,
            'lastCommentTime' => $commentList->getMinCommentTime(),
            'likeData' => \MODULE_LIKE ? $commentList->getLikeData() : [],
        ]);
    }

    #[\Override]
    public function isVisible(int $userID)
    {
        return true;
    }
}
