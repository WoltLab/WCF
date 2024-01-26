<?php

namespace wcf\system\file\processor;

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
        return true;
    }
}
