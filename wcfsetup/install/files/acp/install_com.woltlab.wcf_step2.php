<?php

use wcf\data\category\CategoryEditor;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\package\PackageCache;
use wcf\data\user\UserEditor;
use wcf\data\user\UserProfileAction;
use wcf\system\image\adapter\ImagickImageAdapter;
use wcf\system\WCF;
use wcf\util\StringUtil;

// set default landing page
$sql = "UPDATE  wcf1_application
        SET     landingPageID = (
                    SELECT  pageID
                    FROM    wcf1_page
                    WHERE   identifier = ?
                )
        WHERE   packageID = ?";
$statement = WCF::getDB()->prepare($sql);
$statement->execute([
    'com.woltlab.wcf.Dashboard',
    1,
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
    'description' => '',
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

// Configure dynamic option values

$sql = "UPDATE  wcf1_option
        SET     optionValue = ?
        WHERE   optionName = ?";
$statement = WCF::getDB()->prepare($sql);
$statement->execute([
    StringUtil::getUUID(),
    'wcf_uuid',
]);

if (
    ImagickImageAdapter::isSupported()
    && ImagickImageAdapter::supportsAnimatedGIFs(ImagickImageAdapter::getVersion())
    && ImagickImageAdapter::supportsWebp()
) {
    $statement->execute([
        'imagick',
        'image_adapter_type',
    ]);
}

$user = WCF::getUser();
$statement->execute([
    $user->username,
    'mail_from_name',
]);
$statement->execute([
    $user->email,
    'mail_from_address',
]);
$statement->execute([
    $user->email,
    'mail_admin_address',
]);
