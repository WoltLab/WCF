<?php

namespace wcf\data\comment;

use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\message\embedded\object\MessageEmbeddedObjectManager;

/**
 * Represents a list of decorated comment objects.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  ViewableComment     current()
 * @method  ViewableComment[]   getObjects()
 * @method  ViewableComment|null    getSingleObject()
 * @method  ViewableComment|null    search($objectID)
 * @property    ViewableComment[] $objects
 */
class ViewableCommentList extends CommentList
{
    /**
     * @inheritDoc
     */
    public $decoratorClassName = ViewableComment::class;

    #[\Override]
    public function readObjects()
    {
        parent::readObjects();

        if ($this->objects !== []) {
            $embeddedObjectIDs = $userIDs = [];
            foreach ($this->objects as $comment) {
                if ($comment->userID !== null) {
                    $userIDs[] = $comment->userID;
                }

                if ($comment->hasEmbeddedObjects !== 0) {
                    $embeddedObjectIDs[] = $comment->getObjectID();
                }
            }

            if ($userIDs !== []) {
                UserProfileRuntimeCache::getInstance()->cacheObjectIDs($userIDs);
            }

            if ($embeddedObjectIDs !== []) {
                MessageEmbeddedObjectManager::getInstance()->loadObjects(
                    'com.woltlab.wcf.comment',
                    $embeddedObjectIDs
                );
            }
        }
    }
}
