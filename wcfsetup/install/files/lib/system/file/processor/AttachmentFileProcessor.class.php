<?php

namespace wcf\system\file\processor;

use wcf\system\attachment\AttachmentHandler;

/**
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
final class AttachmentFileProcessor implements IFileProcessor
{
    public function getTypeName(): string
    {
        return 'com.woltlab.wcf.attachment';
    }

    public function acceptUpload(string $filename, int $fileSize, array $context): bool
    {
        $objectType = $context['objectType'] ?? '';
        $objectID = \intval($context['objectID'] ?? 0);
        $parentObjectID = \intval($context['parentObjectID'] ?? 0);

        $attachmentHandler = new AttachmentHandler($objectType, $objectID, '', $parentObjectID);
        if (!$attachmentHandler->canUpload()) {
            return false;
        }

        if ($fileSize > $attachmentHandler->getMaxSize()) {
            return false;
        }

        $extensions = \implode("|", $attachmentHandler->getAllowedExtensions());
        $extensions = \str_replace('\*', '.*', \preg_quote($extensions), '/');
        $extensionsPattern = '/(' . $extensions . ')$/i';
        if (!\preg_match($extensionsPattern, \mb_strtolower($filename))) {
            return false;
        }

        return true;
    }

    public function toHtmlElement(string $objectType, int $objectID, int $parentObjectID): string
    {
        return FileProcessor::getInstance()->getHtmlElement(
            $this,
            [
                'objectType' => $objectType,
                'objectID' => $objectID,
                'parentObjectID' => $parentObjectID,
            ],
        );
    }
}
