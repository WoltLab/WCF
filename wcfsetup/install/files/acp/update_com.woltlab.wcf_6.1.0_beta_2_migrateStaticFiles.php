<?php

/**
 * Migrates some previously static files to the private storage.
 *
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */

use wcf\data\file\FileEditor;
use wcf\data\file\FileList;
use wcf\system\WCF;
use wcf\util\FileUtil;

$moveTypes = [
    'avif' => 'image/avif',
    'mp3' => 'audio/mpeg',
    'mp4' => 'video/mp4',
    'pdf' => 'application/pdf',
    'tiff' => 'image/tiff',
    'txt' => 'text/plain',
    'webm' => 'video/webm',
];

$fileList = new FileList();
$fileList->getConditionBuilder()->add("file.mimeType IN (?)", [\array_values($moveTypes)]);
$fileList->getConditionBuilder()->add("file.fileExtension <> ?", ["bin"]);
$fileList->readObjects();

$sql = "UPDATE  wcf1_file
        SET     fileExtension = ?
        WHERE   fileID = ?";
$statement = WCF::getDB()->prepare($sql);

$defunctFileIDs = [];
foreach ($fileList->getObjects() as $file) {
    $folderA = \substr($file->fileHash, 0, 2);
    $folderB = \substr($file->fileHash, 2, 2);

    $path = \WCF_DIR . \sprintf(
        '_data/private/files/%s/%s/',
        $folderA,
        $folderB,
    );

    if (!\is_dir($path)) {
        \mkdir($path, recursive: true);
        FileUtil::makeWritable($path);
    }

    $filename = \sprintf(
        '%d-%s.bin',
        $file->fileID,
        $file->fileHash,
    );

    $newPathname = $path . $filename;

    $sourceFile = $file->getPathname();
    if (\file_exists($sourceFile)) {
        \rename(
            $sourceFile,
            $newPathname,
        );
    } else if (!\file_exists($newPathname)) {
        $defunctFileIDs[] = $file->fileID;
        continue;
    }

    $statement->execute([
        'bin',
        $file->fileID,
    ]);
}

if ($defunctFileIDs !== []) {
    FileEditor::deleteAll($defunctFileIDs);
}
