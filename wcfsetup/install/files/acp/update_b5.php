<?php
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\WCF;

$sql = "UPDATE	wcf".WCF_N."_user_activity_point
	SET	items = FLOOR(activityPoints / ?)
	WHERE	objectTypeID = ?";
$statement = WCF::getDB()->prepareStatement($sql);

foreach (ObjectTypeCache::getInstance()->getObjectTypes('com.woltlab.wcf.user.activityPointEvent') as $objectType) {
	// prevent division by zero
	if (!$objectType->points) {
		continue;
	}
	
	$statement->execute(array(
		$objectType->points,
		$objectType->objectTypeID
	));
}
