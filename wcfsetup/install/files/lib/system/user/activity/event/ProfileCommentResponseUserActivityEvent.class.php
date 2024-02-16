<?php

namespace wcf\system\user\activity\event;

use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * User activity event implementation for profile comment responses.
 *
 * @author  Marcel Werk
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ProfileCommentResponseUserActivityEvent extends SingletonFactory implements IUserActivityEvent
{
    use TCommentResponseUserActivityEvent;

    /**
     * @inheritDoc
     */
    public function prepare(array $events)
    {
        if (!WCF::getSession()->getPermission('user.profile.canViewUserProfile')) {
            return;
        }

        $this->readResponseData($events);

        // fetch users
        $userIDs = $users = [];
        foreach ($this->comments as $comment) {
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
            if (isset($this->responses[$event->objectID])) {
                $response = $this->responses[$event->objectID];
                $comment = $this->comments[$response->commentID];
                if (isset($users[$comment->objectID]) && isset($this->commentAuthors[$comment->userID])) {
                    if (!$users[$comment->objectID]->isProtected()) {
                        $event->setIsAccessible();

                        $event->setTitle(WCF::getLanguage()->getDynamicVariable(
                            'wcf.user.profile.recentActivity.profileCommentResponse',
                            [
                                'commentAuthor' => $this->commentAuthors[$comment->userID],
                                'commentID' => $comment->commentID,
                                'responseID' => $response->responseID,
                                'user' => $users[$comment->objectID],
                                'author' => $event->getUserProfile(),
                            ]
                        ));
                        $event->setDescription(
                            StringUtil::encodeHTML(
                                StringUtil::truncate($response->getPlainTextMessage(), 500)
                            ),
                            true
                        );
                        $event->setLink($response->getLink());
                    }
                    continue;
                }
            }

            $event->setIsOrphaned();
        }
    }
}
