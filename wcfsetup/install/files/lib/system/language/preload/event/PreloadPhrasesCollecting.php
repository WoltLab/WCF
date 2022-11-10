<?php

namespace wcf\system\language\preload;

use wcf\data\language\Language;
use wcf\system\event\IEvent;

/**
 * Requests the collection of phrases that should
 * be included in the preload cache.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Language\Preload\Event
 * @since 6.0
 */
final class PreloadPhrasesCollecting implements IEvent
{
    public readonly Language $language;

    /**
     * @var PreloadPhrase[]
     */
    private array $phrases = [];

    public function __construct(Language $language)
    {
        $this->language = $language;
    }

    public function preload(string|PreloadPhrase $phrase): void
    {
        if (\is_string($phrase)) {
            $phrase = new PreloadPhrase($phrase, false);
        }

        $this->phrases[] = $phrase;
    }

    /**
     * @return PreloadPhrase[]
     */
    public function getPhrases(): array
    {
        return $this->phrases;
    }
}
