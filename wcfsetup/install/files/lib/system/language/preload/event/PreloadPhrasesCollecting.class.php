<?php

namespace wcf\system\language\preload\event;

use wcf\data\language\Language;
use wcf\system\event\IEvent;

/**
 * Requests the collection of phrases that should
 * be included in the preload cache.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 * @deprecated 6.1 use `wcf\event\language\PreloadPhrasesCollecting` instead
 */
class PreloadPhrasesCollecting implements IEvent
{
    public readonly Language $language;

    /**
     * @var string[]
     */
    private array $phrases = [];

    public function __construct(Language $language)
    {
        $this->language = $language;
    }

    /**
     * Registers the name of a phrases that should
     * be added to the list of preloaded values.
     */
    public function preload(string $phrase): void
    {
        $this->phrases[] = $phrase;
    }

    /**
     * @return string[]
     */
    public function getPhrases(): array
    {
        return $this->phrases;
    }
}
