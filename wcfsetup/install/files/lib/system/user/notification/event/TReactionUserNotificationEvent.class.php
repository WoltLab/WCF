<?php

namespace wcf\system\user\notification\event;

use wcf\data\user\UserProfile;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\user\notification\object\IUserNotificationObject;
use wcf\system\WCF;

/**
 * This trait can be used in likeable user notification events to determine the reactionType counts
 * for a specific user notification object.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
trait TReactionUserNotificationEvent
{
    /**
     * Cached reactions
     * @var int[]
     */
    private $cachedReactions;

    /**
     * Returns the count of reactionTypeIDs for the specific user notification object.
     *
     * @return int[]
     */
    final protected function getReactionsForAuthors()
    {
        if ($this->cachedReactions !== null) {
            return $this->cachedReactions;
        }

        $conditionBuilder = new PreparedStatementConditionBuilder();
        $conditionBuilder->add('like_table.userID IN (?)', [\array_keys($this->getAuthors())]);
        $conditionBuilder->add('like_table_join.likeID = ?', [$this->getUserNotificationObject()->getObjectID()]);

        $sql = "SELECT      like_table.reactionTypeID, COUNT(like_table.reactionTypeID) as count 
                FROM        wcf1_like like_table
                LEFT JOIN   wcf1_like like_table_join
                ON          like_table_join.objectTypeID = like_table.objectTypeID
                        AND like_table_join.objectID = like_table.objectID
                " . $conditionBuilder . " 
                GROUP BY    like_table.reactionTypeID";

        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($conditionBuilder->getParameters());

        $this->cachedReactions = $statement->fetchMap('reactionTypeID', 'count');

        return $this->cachedReactions;
    }

    /**
     * Returns the author for this notification event.
     *
     * @return  UserProfile
     */
    abstract public function getAuthor();

    /**
     * Returns a list of authors for stacked notifications sorted by time.
     *
     * @return  UserProfile[]
     */
    abstract public function getAuthors();

    /**
     * Returns the underlying user notification object.
     *
     * @return  IUserNotificationObject
     */
    abstract public function getUserNotificationObject();
}
