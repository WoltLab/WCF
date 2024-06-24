<?php

namespace wcf\system\worker;

use wcf\data\like\LikeList;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\user\activity\point\UserActivityPointHandler;
use wcf\system\WCF;

/**
 * Worker implementation for updating likes.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @method  LikeList    getObjectList()
 */
final class LikeRebuildDataWorker extends AbstractLinearRebuildDataWorker
{
    /**
     * @inheritDoc
     */
    protected $objectListClassName = LikeList::class;

    /**
     * @inheritDoc
     */
    protected $limit = 10000;

    #[\Override]
    protected function initObjectList()
    {
        parent::initObjectList();

        $this->objectList->sqlOrderBy = 'like_table.objectTypeID, like_table.objectID, like_table.likeID';
    }

    #[\Override]
    public function execute()
    {
        parent::execute();

        if (!$this->loopCount) {
            // reset activity points
            UserActivityPointHandler::getInstance()->reset('com.woltlab.wcf.like.activityPointEvent.receivedLikes');

            // reset like object data
            $sql = "UPDATE  wcf1_like_object
                    SET     likes = 0,
                            dislikes = 0,
                            cumulativeLikes = 0,
                            cachedReactions = NULL";
            $statement = WCF::getDB()->prepare($sql);
            $statement->execute();
        }

        if (\count($this->getObjectList()) === 0) {
            return;
        }

        $itemsToUser = [];
        $likeObjectData = [];
        foreach ($this->objectList as $like) {
            if ($like->objectUserID) {
                if (!isset($itemsToUser[$like->objectUserID])) {
                    $itemsToUser[$like->objectUserID] = 0;
                }

                $itemsToUser[$like->objectUserID]++;
            }

            if (!isset($likeObjectData[$like->objectTypeID])) {
                $likeObjectData[$like->objectTypeID] = [];
            }
            if (!isset($likeObjectData[$like->objectTypeID][$like->objectID])) {
                $likeObjectData[$like->objectTypeID][$like->objectID] = [
                    'likes' => 0,
                    'cumulativeLikes' => 0,
                    'objectUserID' => $like->objectUserID,
                    'cachedReactions' => [],
                ];
            }

            $likeObjectData[$like->objectTypeID][$like->objectID]['likes']++;
            $likeObjectData[$like->objectTypeID][$like->objectID]['cumulativeLikes']++;

            if (!isset($likeObjectData[$like->objectTypeID][$like->objectID]['cachedReactions'][$like->getReactionType()->reactionTypeID])) {
                $likeObjectData[$like->objectTypeID][$like->objectID]['cachedReactions'][$like->getReactionType()->reactionTypeID] = 0;
            }

            $likeObjectData[$like->objectTypeID][$like->objectID]['cachedReactions'][$like->getReactionType()->reactionTypeID]++;
        }

        // No objects are fetched. We can abort the execution.
        if ($likeObjectData === []) {
            return;
        }

        // update activity points
        UserActivityPointHandler::getInstance()->fireEvents(
            'com.woltlab.wcf.like.activityPointEvent.receivedLikes',
            $itemsToUser,
            false
        );

        $rows = [];
        foreach ($likeObjectData as $objectTypeID => $objects) {
            if (!isset($rows[$objectTypeID])) {
                $rows[$objectTypeID] = [];
            }

            $condition = new PreparedStatementConditionBuilder();
            $condition->add('objectTypeID = ?', [$objectTypeID]);
            $condition->add('objectID IN (?)', [\array_keys($objects)]);
            $sql = "SELECT  objectTypeID, objectID, objectUserID, likes, dislikes, cumulativeLikes, cachedReactions
                    FROM    wcf1_like_object
                    " . $condition;
            $objectStatement = WCF::getDB()->prepare($sql);
            $objectStatement->execute($condition->getParameters());
            while ($row = $objectStatement->fetchArray()) {
                $rows[$objectTypeID][$row['objectID']] = $row;
            }
        }

        $sql = "INSERT INTO wcf1_like_object
                            (objectTypeID, objectID, objectUserID, likes, dislikes, cumulativeLikes, cachedReactions)
                VALUES      (?, ?, ?, ?, ?, ?, ?)";
        $insertStatement = WCF::getDB()->prepare($sql);

        $sql = "UPDATE  wcf1_like_object
                SET     objectUserID = ?,
                        likes = ?,
                        dislikes = 0,
                        cumulativeLikes = ?,
                        cachedReactions = ?
                WHERE   objectTypeID = ?
                AND     objectID = ?";
        $updateStatement = WCF::getDB()->prepare($sql);

        WCF::getDB()->beginTransaction();
        foreach ($likeObjectData as $objectTypeID => $objects) {
            foreach ($objects as $objectID => $data) {
                if (isset($rows[$objectTypeID][$objectID])) {
                    $existingRow = $rows[$objectTypeID][$objectID];

                    $updateStatement->execute([
                        $data['objectUserID'],
                        $existingRow['likes'] + $data['likes'],
                        $existingRow['cumulativeLikes'] + $data['cumulativeLikes'],
                        \serialize(
                            $this->mergeCachedReactions(
                                @\unserialize($existingRow['cachedReactions']),
                                $data['cachedReactions']
                            )
                        ),
                        $objectTypeID,
                        $objectID,
                    ]);
                } else {
                    $insertStatement->execute([
                        $objectTypeID,
                        $objectID,
                        $data['objectUserID'],
                        $data['likes'],
                        0,
                        $data['cumulativeLikes'],
                        \serialize($data['cachedReactions']),
                    ]);
                }
            }
        }
        WCF::getDB()->commitTransaction();
    }

    /**
     * Merges two cached reaction objects into one object.
     *
     * @param int[]|null $oldCachedReactions
     * @param int[] $newCachedReactions
     * @return      int[]
     */
    private function mergeCachedReactions($oldCachedReactions, array $newCachedReactions)
    {
        if (!\is_array($oldCachedReactions)) {
            $oldCachedReactions = [];
        }

        foreach ($newCachedReactions as $reactionTypeID => $count) {
            $oldCachedReactions[$reactionTypeID] = ($oldCachedReactions[$reactionTypeID] ?? 0) + $count;
        }

        return $oldCachedReactions;
    }
}
