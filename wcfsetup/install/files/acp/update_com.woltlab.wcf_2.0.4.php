<?php
use wcf\data\like\object\LikeObjectList;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\like\LikeHandler;

/**
 * @author	Matthias Schmidt
 * @copyright	2001-2014 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @category	Community Framework
 */
// delete likes for deleted comments responses
$likeObjectList = new LikeObjectList();
$likeObjectList->sqlJoins = "LEFT JOIN wcf".WCF_N."_comment_response comment_response ON (comment_response.responseID = like_object.objectID)";
$likeObjectList->getConditionBuilder()->add('like_object.objectTypeID = ?', array(ObjectTypeCache::getInstance()->getObjectTypeIDByName('com.woltlab.wcf.like.likeableObject', 'com.woltlab.wcf.comment.response')));
$likeObjectList->getConditionBuilder()->add('comment_response.responseID IS NULL');
$likeObjectList->readObjects();

$deletedResponseIDs = array();
foreach ($likeObjectList as $likeObject) {
	$deletedResponseIDs[] = $likeObject->objectID;
}

if (!empty($deletedResponseIDs)) {
	LikeHandler::getInstance()->removeLikes('com.woltlab.wcf.comment.response', $deletedResponseIDs);
}
