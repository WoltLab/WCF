<?php

namespace wcf\system\file\processor;

use wcf\action\FileUploadPreflightAction;
use wcf\system\event\EventHandler;
use wcf\system\file\processor\event\FileProcessorCollecting;
use wcf\system\request\LinkHandler;
use wcf\system\SingletonFactory;
use wcf\util\JSON;
use wcf\util\StringUtil;

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

    public function getHtmlElement(IFileProcessor $fileProcessor, array $context): string
    {
        $endpoint = LinkHandler::getInstance()->getControllerLink(FileUploadPreflightAction::class);

        $allowedFileExtensions = $fileProcessor->getAllowedFileExtensions($context);
        if (\in_array('*', $allowedFileExtensions)) {
            $allowedFileExtensions = '';
        } else {
            $allowedFileExtensions = \implode(
                ',',
                \array_map(
                    static fn (string $fileExtension) => ".{$fileExtension}",
                    $allowedFileExtensions
                )
            );
        }

        return \sprintf(
            <<<'HTML'
                <woltlab-core-file-upload
                    data-endpoint="%s"
                    data-type-name="%s"
                    data-context="%s"
                    data-file-extensions="%s"
                ></woltlab-core-file-upload>
                HTML,
            StringUtil::encodeHTML($endpoint),
            StringUtil::encodeHTML($fileProcessor->getTypeName()),
            StringUtil::encodeHTML(JSON::encode($context)),
            StringUtil::encodeHTML($allowedFileExtensions),
        );
    }
}
