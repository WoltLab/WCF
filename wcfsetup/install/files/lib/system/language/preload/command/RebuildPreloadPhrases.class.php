<?php

namespace wcf\system\language\preload\command;

use wcf\data\language\Language;
use wcf\system\event\EventHandler;
use wcf\system\language\preload\PreloadPhrasesCollecting;

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
final class RebuildPreloadPhrases
{
    private readonly EventHandler $eventHandler;
    private readonly Language $language;

    public function __construct(Language $language)
    {
        $this->eventHandler = EventHandler::getInstance();
        $this->language = $language;
    }

    public function __invoke(): void
    {
        $event = new PreloadPhrasesCollecting($this->language);
        $this->eventHandler->fire($event);

        // TODO: Do something with the collected phrases.
    }
}
