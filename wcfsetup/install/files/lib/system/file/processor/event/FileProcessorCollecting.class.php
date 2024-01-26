<?php

namespace wcf\system\file\processor\event;

use wcf\system\event\IEvent;
use wcf\system\file\processor\exception\DuplicateFileProcessor;
use wcf\system\file\processor\IFileProcessor;

/**
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
final class FileProcessorCollecting implements IEvent
{
    /**
     * @var array<string, IFileProcessor>
     */
    private array $data = [];

    public function register(IFileProcessor $fileUploadProcessor): void
    {
        $typeName = $fileUploadProcessor->getTypeName();
        if (isset($this->data[$typeName])) {
            throw new DuplicateFileProcessor($typeName);
        }

        $this->data[$typeName] = $fileUploadProcessor;
    }

    /**
     * @return array<string, IFileProcessor>
     */
    public function getProcessors(): array
    {
        return $this->data;
    }
}
