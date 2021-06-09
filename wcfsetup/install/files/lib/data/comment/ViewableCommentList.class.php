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
 * @package WoltLabSuite\Core\Data\Comment
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

    /**
     * @inheritDoc
     */
    public function readObjects()
    {
        parent::readObjects();

        if (!empty($this->objects)) {
            $embeddedObjectIDs = $userIDs = [];
            foreach ($this->objects as $comment) {
                if ($comment->userID) {
                    $userIDs[] = $comment->userID;
                }

                if ($comment->hasEmbeddedObjects) {
                    $embeddedObjectIDs[] = $comment->getObjectID();
                }
            }

            if (!empty($userIDs)) {
                UserProfileRuntimeCache::getInstance()->cacheObjectIDs($userIDs);
            }

            if (!empty($embeddedObjectIDs)) {
                MessageEmbeddedObjectManager::getInstance()->loadObjects(
                    'com.woltlab.wcf.comment',
                    $embeddedObjectIDs
                );
            }
        }
    }
}
