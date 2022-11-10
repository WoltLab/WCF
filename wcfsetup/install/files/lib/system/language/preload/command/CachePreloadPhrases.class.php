<?php

namespace wcf\system\language\preload\command;

use Laminas\Diactoros\Stream;
use wcf\data\language\Language;
use wcf\system\event\EventHandler;
use wcf\system\language\preload\PreloadPhrasesCollecting;
use wcf\util\StringUtil;

/**
 * Rebuilds the phrase preload cache for the
 * requested language.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Language\Preload\Command
 * @since 6.0
 */
final class CachePreloadPhrases
{
    private readonly EventHandler $eventHandler;
    private readonly Language $language;

    public function __construct(Language $language)
    {
        $this->language = $language;

        $this->eventHandler = EventHandler::getInstance();
    }

    public function __invoke(): void
    {
        $event = new PreloadPhrasesCollecting($this->language);
        $this->eventHandler->fire($event);

        $file = new Stream(\WCF_DIR . $this->language->getPreloadCacheFilename(), 'wb');
        foreach ($event->getPhrases() as $phrase) {
            $file->write(
                \sprintf(
                    "WoltLabLanguage.registerPhrase('%s', '%s');\n",
                    $phrase,
                    $this->getEncodedValue($phrase),
                )
            );
        }
        $file->close();
    }

    private function getEncodedValue(string $phrase): string
    {
        return StringUtil::encodeJS($this->language->get($phrase));
    }
}
