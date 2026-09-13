<?php

namespace wcf\command\file;

use wcf\data\file\File;
use wcf\data\file\FileBuilder;
use wcf\event\file\FileDeleted;
use wcf\system\event\EventHandler;
use wcf\system\file\processor\FileProcessor;

/**
 * Deletes files permanently.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class DeleteFiles
{
    /**
     * @param non-empty-list<File> $files
     */
    public function __construct(
        private readonly array $files,
    ) {}

    public function __invoke(): void
    {
        FileProcessor::getInstance()->delete(
            $this->files
        );

        FileBuilder::deleteAll(\array_map(
            static fn($object) => $object->fileID,
            $this->files
        ));

        foreach ($this->files as $file) {
            EventHandler::getInstance()->fire(
                new FileDeleted($file)
            );
        }
    }
}
