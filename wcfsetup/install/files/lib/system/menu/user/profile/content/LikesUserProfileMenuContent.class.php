<?php

namespace wcf\system\menu\user\profile\content;

use wcf\data\like\ViewableLikeList;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles user profile likes content.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class LikesUserProfileMenuContent extends SingletonFactory implements IUserProfileMenuContent
{
    #[\Override]
    public function getContent(int $userID)
    {
        $likeList = new ViewableLikeList();
        $likeList->getConditionBuilder()->add("like_table.objectUserID = ?", [$userID]);
        $likeList->readObjects();

        return WCF::getTPL()->render('wcf', 'userProfileLikes', [
            'likeList' => $likeList,
            'userID' => $userID,
            'lastLikeTime' => $likeList->getLastLikeTime(),
        ]);
    }

    #[\Override]
    public function isVisible(int $userID)
    {
        if (!WCF::getSession()->hasPermission('user.like.canViewLike')) {
            return false;
        }

        return true;
    }
}
