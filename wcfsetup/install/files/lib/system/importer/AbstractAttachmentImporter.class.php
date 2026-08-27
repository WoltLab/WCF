<?php

namespace wcf\system\importer;

use wcf\data\attachment\Attachment;
use wcf\data\attachment\AttachmentBuilder;
use wcf\data\file\FileEditor;

/**
 * Imports attachments.
 *
 * @author  Tim Duesterhus, Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class AbstractAttachmentImporter extends AbstractImporter
{
    /**
     * @inheritDoc
     */
    protected $className = Attachment::class;

    /**
     * object type id for attachments
     * @var int
     */
    protected $objectTypeID = 0;

    #[\Override]
    public function import(mixed $oldID, array $data, array $additionalData = [])
    {
        // check file location
        if (!\is_readable($additionalData['fileLocation'])) {
            return 0;
        }

        $objectID = $data['objectID'] ?? null;
        $userID = ImportHandler::getInstance()->getNewID('com.woltlab.wcf.user', $data['userID'] ?? null);
        $downloads = (int)($data['downloads'] ?? 0);
        $uploadTime = (int)($data['uploadTime'] ?? 0);

        // set default last download time
        $lastDownloadTime = (int)($data['lastDownloadTime'] ?? 0);
        if ($lastDownloadTime === 0 && $downloads !== 0) {
            $lastDownloadTime = \TIME_NOW;
        }

        // The source file must be preserved, therefore it is copied into the
        // storage of the file API instead of being moved.
        $file = FileEditor::createFromExistingFile(
            $additionalData['fileLocation'],
            $data['filename'] ?? \basename($additionalData['fileLocation']),
            'com.woltlab.wcf.attachment',
            true,
            $uploadTime ?: null
        );
        if ($file === null) {
            return 0;
        }

        $builder = AttachmentBuilder::forCreate()
            ->setObjectTypeID($this->objectTypeID)
            ->setObjectID($objectID !== null ? (int)$objectID : null)
            ->setUserID($userID)
            ->setUploadTime($uploadTime)
            ->setShowOrder((int)($data['showOrder'] ?? 0))
            ->setDownloads($downloads)
            ->setLastDownloadTime($lastDownloadTime)
            ->setFile($file);

        // check existing attachment id
        if (\ctype_digit((string)$oldID)) {
            $attachment = new Attachment($oldID);
            if ($attachment->isNil()) {
                $builder->setID((int)$oldID);
            }
        }

        return $builder->create()->attachmentID;
    }

    /**
     * Replaces old attachment BBCodes with BBCodes with the new attachment id.
     *
     * @return  string|bool
     */
    protected function fixEmbeddedAttachments(string $message, int $oldID, int $newID)
    {
        if (
            \mb_strripos($message, '[attach]' . $oldID . '[/attach]') !== false
            || \mb_strripos($message, '[attach=' . $oldID . ']') !== false
            || \mb_strripos($message, '[attach=' . $oldID . ',') !== false
        ) {
            $message = \str_ireplace('[attach]' . $oldID . '[/attach]', '[attach]' . $newID . '[/attach]', $message);
            $message = \str_ireplace('[attach=' . $oldID . ']', '[attach=' . $newID . ']', $message);

            return \str_ireplace('[attach=' . $oldID . ',', '[attach=' . $newID . ',', $message);
        }

        return \preg_replace_callback(
            '~<woltlab-metacode data-name="attach" data-attributes="(?<attributes>[^"]+)">~',
            static function (array $matches) use ($oldID, $newID): string {
                $encodedAttributes = $matches['attributes'];

                $base64Decoded = \base64_decode($matches['attributes'], true);
                if ($base64Decoded !== false && $base64Decoded !== '') {
                    try {
                        $attributes = \json_decode($base64Decoded, true, flags: \JSON_THROW_ON_ERROR);
                        // @phpstan-ignore equal.notAllowed (the decoded attribute can be an integer or a numeric string)
                        if ($attributes[0] == $oldID) {
                            $attributes[0] = $newID;
                        }

                        $encodedAttributes = \base64_encode(\json_encode($attributes, \JSON_THROW_ON_ERROR));
                    } catch (\Exception $e) {
                        $encodedAttributes = $matches['attributes'];
                    }
                }

                return '<woltlab-metacode data-name="attach" data-attributes="' . $encodedAttributes . '">';
            },
            $message
        );
    }
}
