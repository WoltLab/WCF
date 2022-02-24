<?php

use wcf\data\category\CategoryEditor;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\package\PackageCache;
use wcf\data\user\UserEditor;
use wcf\data\user\UserProfileAction;
use wcf\system\WCF;

// set default landing page
$sql = "UPDATE  wcf" . WCF_N . "_page
        SET     isLandingPage = ?
        WHERE   identifier = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute([
    1,
    'com.woltlab.wcf.Dashboard',
]);

// update administrator user rank and user online marking
$editor = new UserEditor(WCF::getUser());
$action = new UserProfileAction([$editor], 'updateUserRank');
$action->executeAction();
$action = new UserProfileAction([$editor], 'updateUserOnlineMarking');
$action->executeAction();

// add default article category
CategoryEditor::create([
    'objectTypeID' => ObjectTypeCache::getInstance()
        ->getObjectTypeIDByName('com.woltlab.wcf.category', 'com.woltlab.wcf.article.category'),
    'title' => 'Default Category',
    'time' => TIME_NOW,
]);

// Randomize the times of the package list update and robot list update cronjobs.
$startMinute = \random_int(0, 59);
$startHour = \random_int(0, 23);

$sql = "UPDATE  wcf1_cronjob
        SET     startMinute = ?,
                startHour = ?,
                startDom = ?,
                startMonth = ?,
                startDow = ?,
                lastExec = ?,
                nextExec = ?,
                afterNextExec = ?
        WHERE   packageiD = ?
            AND cronjobName = ?";
$statement = WCF::getDB()->prepare($sql);
$statement->execute([
    $startMinute,
    $startHour,
    '*',
    '*',
    '*',

    0,
    \TIME_NOW,
    0,

    $this->installation->getPackageID(),
    'com.woltlab.wcf.refreshPackageUpdates',
]);
$statement->execute([
    $startMinute,
    (($startHour + 12) % 24),
    \random_int(1, 15),
    '*',
    '*',

    0,
    \TIME_NOW,
    0,

    $this->installation->getPackageID(),
    'com.woltlab.wcf.refreshSearchRobots',
]);
