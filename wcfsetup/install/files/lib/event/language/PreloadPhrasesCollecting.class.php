<?php

namespace wcf\event\language;

use wcf\data\language\Language;
use wcf\event\IPsr14Event;

/**
 * Requests the collection of phrases that should be included in the preload cache.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class PreloadPhrasesCollecting extends \wcf\system\language\preload\event\PreloadPhrasesCollecting implements IPsr14Event
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
