<?php

namespace wcf\system\user\activity\event;

use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\cache\runtime\ViewableCommentRuntimeCache;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * User activity event implementation for profile comments.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ProfileCommentUserActivityEvent extends SingletonFactory implements IUserActivityEvent
{
    /**
     * @inheritDoc
     */
    public function prepare(array $events)
    {
        if (!WCF::getSession()->getPermission('user.profile.canViewUserProfile')) {
            return;
        }

        $commentIDs = [];
        foreach ($events as $event) {
            $commentIDs[] = $event->objectID;
        }

        // fetch comments
        $comments = ViewableCommentRuntimeCache::getInstance()->getObjects($commentIDs);

        // fetch users
        $userIDs = $users = [];
        foreach ($comments as $comment) {
            if ($comment === null) {
                continue;
            }

            $userIDs[] = $comment->objectID;
        }
        if (!empty($userIDs)) {
            $users = UserProfileRuntimeCache::getInstance()->getObjects($userIDs);
        }

        // set message
        foreach ($events as $event) {
            if (isset($comments[$event->objectID])) {
                // short output
                $comment = $comments[$event->objectID];
                if (isset($users[$comment->objectID])) {
                    if (!$users[$comment->objectID]->isProtected()) {
                        $event->setIsAccessible();

                        $event->setTitle(WCF::getLanguage()->getDynamicVariable(
                            'wcf.user.profile.recentActivity.profileComment',
                            [
                                'commentID' => $comment->commentID,
                                'user' => $users[$comment->objectID],
                                'author' => $event->getUserProfile(),
                            ]
                        ));
                        $event->setDescription(
                            StringUtil::encodeHTML(
                                StringUtil::truncate($comment->getPlainTextMessage(), 500)
                            ),
                            true
                        );
                        $event->setLink($comment->getLink());
                    }
                    continue;
                }
            }

            $event->setIsOrphaned();
        }
    }
}
