<?php

namespace wcf\system\stat;

use wcf\system\comment\CommentHandler;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\SystemException;
use wcf\system\WCF;

/**
 * Abstract implementation of a comment stat handler.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class AbstractCommentStatDailyHandler extends AbstractStatDailyHandler
{
    /**
     * name of the comment object type
     * @var string
     */
    protected $objectType = '';

    /**
     * @inheritDoc
     */
    public function getData($date)
    {
        $objectTypeID = CommentHandler::getInstance()->getObjectTypeID($this->objectType);
        if ($objectTypeID === null) {
            throw new SystemException("Unknown comment object type '" . $this->objectType . "'");
        }

        $sql = "SELECT (
                    SELECT  COUNT(*)
                    FROM    wcf1_comment
                    WHERE   objectTypeID = ?
                        AND time BETWEEN ? AND ?
                ) + (
                    SELECT      COUNT(*)
                    FROM        wcf1_comment_response comment_response
                    LEFT JOIN   wcf1_comment comment
                    ON          comment.commentID = comment_response.commentID
                    WHERE       comment.objectTypeID = ?
                            AND comment_response.time BETWEEN ? AND ?
                )";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $objectTypeID,
            $date,
            $date + 86399,
            $objectTypeID,
            $date,
            $date + 86399,
        ]);
        $counter = $statement->fetchSingleColumn();

        $sql = "SELECT (
                    SELECT  COUNT(*)
                    FROM    wcf1_comment
                    WHERE   objectTypeID = ?
                        AND time < ?
                ) + (
                    SELECT      COUNT(*)
                    FROM        wcf1_comment_response comment_response
                    LEFT JOIN   wcf1_comment comment
                    ON          comment.commentID = comment_response.commentID
                    WHERE       comment.objectTypeID = ?
                            AND comment_response.time < ?
                )";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $objectTypeID,
            $date + 86400,
            $objectTypeID,
            $date + 86400,
        ]);
        $total = $statement->fetchSingleColumn();

        return [
            'counter' => $counter,
            'total' => $total,
        ];
    }

    /**
     * @inheritDoc
     * @since   3.1
     */
    protected function addConditions(PreparedStatementConditionBuilder $conditionBuilder)
    {
        throw new \BadMethodCallException(__CLASS__ . " does not support addConditions().");
    }
}
