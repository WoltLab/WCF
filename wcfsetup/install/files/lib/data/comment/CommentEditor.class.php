<?php

namespace wcf\data\comment;

use wcf\data\DatabaseObjectEditor;
use wcf\system\WCF;

/**
 * Provides functions to edit comments.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Data\Comment
 *
 * @method static Comment     create(array $parameters = [])
 * @method      Comment     getDecoratedObject()
 * @mixin       Comment
 */
class CommentEditor extends DatabaseObjectEditor
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = Comment::class;

    /**
     * Updates response ids.
     */
    public function updateResponseIDs()
    {
        $sql = "SELECT      responseID
                FROM        wcf1_comment_response
                WHERE       commentID = ?
                        AND isDisabled = ?
                ORDER BY    time ASC, responseID ASC";
        $statement = WCF::getDB()->prepare($sql, 5);
        $statement->execute([$this->commentID, 0]);
        $responseIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);

        $this->update(['responseIDs' => \serialize($responseIDs)]);
    }

    /**
     * Updates response ids, including disabled ones.
     */
    public function updateUnfilteredResponseIDs()
    {
        $sql = "SELECT      responseID
                FROM        wcf1_comment_response
                WHERE       commentID = ?
                ORDER BY    time ASC, responseID ASC";
        $statement = WCF::getDB()->prepare($sql, 5);
        $statement->execute([$this->commentID]);
        $responseIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);

        $this->update(['unfilteredResponseIDs' => \serialize($responseIDs)]);
    }

    /**
     * Updates the counter for responses.
     *
     * @since 6.0
     */
    public function updateResponses(): void
    {
        $sql = "SELECT      COUNT(*)
                FROM        wcf1_comment_response
                WHERE       commentID = ?
                        AND isDisabled = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->commentID, 0]);

        $this->update(['responses' => $statement->fetchSingleColumn()]);
    }

    /**
     * Updates the counter for responses, including disabled ones.
     *
     * @since 6.0
     */
    public function updateUnfilteredResponses(): void
    {
        $sql = "SELECT      COUNT(*)
                FROM        wcf1_comment_response
                WHERE       commentID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->commentID]);

        $this->update(['unfilteredResponses' => $statement->fetchSingleColumn()]);
    }
}
