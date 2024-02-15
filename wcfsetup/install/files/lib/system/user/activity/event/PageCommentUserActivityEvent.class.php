<?php

namespace wcf\system\user\activity\event;

use wcf\data\page\PageCache;
use wcf\system\cache\runtime\ViewableCommentRuntimeCache;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * User activity event implementation for page comments.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
class PageCommentUserActivityEvent extends SingletonFactory implements IUserActivityEvent
{
    /**
     * @inheritDoc
     */
    public function prepare(array $events)
    {
        $commentIDs = [];
        foreach ($events as $event) {
            $commentIDs[] = $event->objectID;
        }

        // fetch comments
        $comments = ViewableCommentRuntimeCache::getInstance()->getObjects($commentIDs);

        // set message
        foreach ($events as $event) {
            if (isset($comments[$event->objectID])) {
                // short output
                $comment = $comments[$event->objectID];
                if (PageCache::getInstance()->getPage($comment->objectID)) {
                    $page = PageCache::getInstance()->getPage($comment->objectID);

                    // check permissions
                    if (!$page->isAccessible()) {
                        continue;
                    }
                    $event->setIsAccessible();

                    $event->setTitle(WCF::getLanguage()->getDynamicVariable('wcf.page.recentActivity.pageComment', [
                        'page' => $page,
                        'commentID' => $comment->commentID,
                        'author' => $event->getUserProfile(),
                    ]));
                    $event->setDescription(
                        StringUtil::encodeHTML(
                            StringUtil::truncate($comment->getPlainTextMessage(), 500)
                        ),
                        true
                    );
                    $event->setLink($comment->getLink());

                    continue;
                }
            }

            $event->setIsOrphaned();
        }
    }
}
