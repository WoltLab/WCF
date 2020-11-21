<?php
namespace wcf\acp;

use wcf\data\object\type\ObjectTypeCache;
use wcf\system\comment\CommentHandler;
use wcf\system\WCF;

// Earlier versions did remove moderation queues without taking care of
// comments associated with them.
$commentObjectTypeID = ObjectTypeCache::getInstance()->getObjectTypeIDByName(
	"com.woltlab.wcf.comment.commentableContent",
	"com.woltlab.wcf.moderation.queue"
);

$sql = "SELECT  DISTINCT objectID
        FROM    wcf" . WCF_N . "_comment
        WHERE   objectTypeID = ?
        AND     objectID NOT IN (
                        SELECT  queueID
                        FROM    wcf" . WCF_N . "_moderation_queue
                )";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute([$commentObjectTypeID]);
$objectIDs = $statement->fetchAll(\PDO::FETCH_COLUMN);

CommentHandler::getInstance()->deleteObjects(
	"com.woltlab.wcf.moderation.queue",
	$objectIDs
);
