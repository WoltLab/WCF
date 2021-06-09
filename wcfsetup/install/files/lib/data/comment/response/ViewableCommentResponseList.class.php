<?php

namespace wcf\data\comment\response;

use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;

/**
 * Represents a list of decorated comment response objects.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\Comment\Response
 *
 * @method  ViewableCommentResponse     current()
 * @method  ViewableCommentResponse[]   getObjects()
 * @method  ViewableCommentResponse|null    getSingleObject()
 * @method  ViewableCommentResponse|null    search($objectID)
 * @property    ViewableCommentResponse[] $objects
 */
class ViewableCommentResponseList extends CommentResponseList
{
    /**
     * @inheritDoc
     */
    public $decoratorClassName = ViewableCommentResponse::class;

    /**
     * @inheritDoc
     */
    public function readObjects()
    {
        parent::readObjects();

        if (!empty($this->objects)) {
            $embeddedObjectIDs = $userIDs = [];
            foreach ($this->objects as $response) {
                if ($response->userID) {
                    $userIDs[] = $response->userID;
                }

                if ($response->hasEmbeddedObjects) {
                    $embeddedObjectIDs[] = $response->getObjectID();
                }
            }

            if (!empty($userIDs)) {
                UserProfileRuntimeCache::getInstance()->cacheObjectIDs($userIDs);
            }

            if (!empty($embeddedObjectIDs)) {
                MessageEmbeddedObjectManager::getInstance()->loadObjects(
                    'com.woltlab.wcf.comment.response',
                    $embeddedObjectIDs
                );
            }
        }
    }
}
