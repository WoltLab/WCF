<?php

namespace wcf\system\event\listener;

use wcf\data\language\Language;
use wcf\event\language\PhraseChanged;
use wcf\event\language\PreloadPhrasesCollecting;
use wcf\system\event\EventHandler;
use wcf\system\language\preload\command\ResetPreloadCache;

/**
 * Resets the preload cache if the modified phrase is
 * part of it.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
final class PhraseChangedPreloadListener
{
    private readonly EventHandler $eventHandler;

    public function __construct()
    {
        $this->eventHandler = EventHandler::getInstance();
    }

    public function __invoke(PhraseChanged $event): void
    {
        $resetCache = false;

        $preloadPhrases = $this->getPhrasesForPreloading($event->language);
        foreach ($preloadPhrases as $phrase) {
            if ($phrase === $event->name) {
                $resetCache = true;
                break;
            }
        }

        if ($resetCache) {
            $command = new ResetPreloadCache($event->language);
            $command();
        }
    }

    /**
     * @return string[]
     */
    private function getPhrasesForPreloading(Language $language): array
    {
        $event = new PreloadPhrasesCollecting($language);
        $this->eventHandler->fire($event);

        return $event->getPhrases();
    }
}
