<?php

namespace wcf\system\user\activity\event;

use wcf\data\page\PageCache;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * User activity event implementation for responses to page comments.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
class PageCommentResponseUserActivityEvent extends SingletonFactory implements IUserActivityEvent
{
    use TCommentResponseUserActivityEvent;

    /**
     * @inheritDoc
     */
    public function prepare(array $events)
    {
        $this->readResponseData($events);

        // set message
        foreach ($events as $event) {
            if (isset($this->responses[$event->objectID])) {
                $response = $this->responses[$event->objectID];
                $comment = $this->comments[$response->commentID];
                if (
                    PageCache::getInstance()->getPage($comment->objectID)
                    && isset($this->commentAuthors[$comment->userID])
                ) {
                    $page = PageCache::getInstance()->getPage($comment->objectID);

                    // check permissions
                    if (!$page->isAccessible()) {
                        continue;
                    }
                    $event->setIsAccessible();

                    $event->setTitle(WCF::getLanguage()->getDynamicVariable('wcf.page.recentActivity.pageCommentResponse', [
                        'commentAuthor' => $this->commentAuthors[$comment->userID],
                        'commentID' => $comment->commentID,
                        'responseID' => $response->responseID,
                        'page' => $page,
                        'author' => $event->getUserProfile(),
                    ]));
                    $event->setDescription(
                        StringUtil::encodeHTML(
                            StringUtil::truncate($response->getPlainTextMessage(), 500)
                        ),
                        true
                    );
                    $event->setLink($response->getLink());

                    continue;
                }
            }

            $event->setIsOrphaned();
        }
    }
}
