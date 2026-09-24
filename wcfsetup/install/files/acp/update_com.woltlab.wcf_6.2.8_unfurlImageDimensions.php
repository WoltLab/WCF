<?php

/**
 * Removes unfurl images that were stored with zero dimensions and refetches
 * the affected urls.
 */

use wcf\data\file\FileEditor;
use wcf\data\unfurl\url\UnfurlUrlList;
use wcf\system\background\BackgroundQueueHandler;
use wcf\system\background\job\UnfurlUrlBackgroundJob;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\package\SplitNodeException;
use wcf\system\WCF;

$imagesPerIteration = 100;

$sql = "SELECT  imageID, fileID
        FROM    wcf1_unfurl_url_image
        WHERE   width = ?
            AND height = ?";
$statement = WCF::getDB()->prepare($sql, $imagesPerIteration);
$statement->execute([0, 0]);

$imageIDs = [];
$fileIDs = [];
while ($row = $statement->fetchArray()) {
    $imageIDs[] = $row['imageID'];
    if ($row['fileID'] !== null) {
        $fileIDs[] = $row['fileID'];
    }
}

if ($imageIDs === []) {
    return;
}

// The affected urls must be collected before the image rows are removed,
// because the foreign key clears their `imageID` on delete.
$unfurlUrlList = new UnfurlUrlList();
$unfurlUrlList->getConditionBuilder()->add('unfurl_url.imageID IN (?)', [$imageIDs]);
$unfurlUrlList->readObjects();

$conditionBuilder = new PreparedStatementConditionBuilder();
$conditionBuilder->add('imageID IN (?)', [$imageIDs]);

$sql = "DELETE FROM wcf1_unfurl_url_image
        {$conditionBuilder}";
$statement = WCF::getDB()->prepare($sql);
$statement->execute($conditionBuilder->getParameters());

if ($fileIDs !== []) {
    FileEditor::deleteAll($fileIDs);
}

$jobs = [];
foreach ($unfurlUrlList as $unfurlUrl) {
    $jobs[] = new UnfurlUrlBackgroundJob($unfurlUrl);
}

if ($jobs !== []) {
    BackgroundQueueHandler::getInstance()->enqueueIn($jobs);
}

if (\count($imageIDs) === $imagesPerIteration) {
    throw new SplitNodeException();
}
