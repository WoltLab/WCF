<?php

namespace wcf\system\file\processor;

use wcf\system\event\EventHandler;
use wcf\system\file\processor\event\FileProcessorCollecting;
use wcf\system\SingletonFactory;

/**
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
final class FileProcessor extends SingletonFactory
{
    /**
     * @var array<string, IFileProcessor>
     */
    private array $processors;

    #[\Override]
    public function init(): void
    {
        $event = new FileProcessorCollecting();
        EventHandler::getInstance()->fire($event);
        $this->processors = $event->getProcessors();
    }

    public function forTypeName(string $typeName): ?IFileProcessor
    {
        return $this->processors[$typeName] ?? null;
    }
}
