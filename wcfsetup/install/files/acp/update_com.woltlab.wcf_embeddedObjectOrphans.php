<?php
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\WCF;

// 1) Articles
$articleContentObjectTypeID = ObjectTypeCache::getInstance()->getObjectTypeIDByName('com.woltlab.wcf.message', 'com.woltlab.wcf.article.content');

$sql = "DELETE FROM	wcf".WCF_N."_message_embedded_object
	WHERE		messageObjectTypeID = ?
		AND	messageID NOT IN (
			SELECT	articleContentID
			FROM	wcf".WCF_N."_article_content
		)";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute([$articleContentObjectTypeID]);

// 2) Pages
$pageContentObjectTypeID = ObjectTypeCache::getInstance()->getObjectTypeIDByName('com.woltlab.wcf.message', 'com.woltlab.wcf.page.content');

$sql = "DELETE FROM	wcf".WCF_N."_message_embedded_object
	WHERE		messageObjectTypeID = ?
		AND	messageID NOT IN (
			SELECT	pageContentID
			FROM	wcf".WCF_N."_page_content
		)";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute([$pageContentObjectTypeID]);

