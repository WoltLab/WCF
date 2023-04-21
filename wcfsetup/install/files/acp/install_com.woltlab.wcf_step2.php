<?php

use wcf\data\category\CategoryEditor;
use wcf\data\object\type\ObjectTypeCache;
use wcf\data\reaction\type\ReactionTypeEditor;
use wcf\data\user\rank\UserRankEditor;
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

// install default user ranks
foreach ([
    [4, 0, 'wcf.user.rank.administrator', 'blue'],
    [5, 0, 'wcf.user.rank.moderator', 'blue'],
    [3, 0, 'wcf.user.rank.user0', ''],
    [3, 300, 'wcf.user.rank.user1', ''],
    [3, 900, 'wcf.user.rank.user2', ''],
    [3, 3000, 'wcf.user.rank.user3', ''],
    [3, 9000, 'wcf.user.rank.user4', ''],
    [3, 15000, 'wcf.user.rank.user5', ''],
] as [$groupID, $requiredPoints, $rankTitle, $cssClassName]) {
    UserRankEditor::create([
        'groupID' => $groupID,
        'requiredPoints' => $requiredPoints,
        'rankTitle' => $rankTitle,
        'cssClassName' => $cssClassName,
    ]);
}

// update administrator user rank and user online marking
$editor = new UserEditor(WCF::getUser());
$action = new UserProfileAction([$editor], 'updateUserRank');
$action->executeAction();
$action = new UserProfileAction([$editor], 'updateUserOnlineMarking');
$action->executeAction();

// install default reactions
foreach ([
    [1, 1, 'like.svg'],
    [2, 2, 'thanks.svg'],
    [3, 3, 'haha.svg'],
    [4, 4, 'confused.svg'],
    [5, 5, 'sad.svg'],
] as [$reactionTypeID, $showOrder, $iconFile]) {
    ReactionTypeEditor::create([
        'reactionTypeID' => $reactionTypeID,
        'title' => "wcf.reactionType.title{$reactionTypeID}",
        'showOrder' => $showOrder,
        'iconFile' => $iconFile,
    ]);
}

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
        WHERE   packageID = ?
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
