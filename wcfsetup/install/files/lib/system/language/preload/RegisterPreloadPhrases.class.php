<?php

namespace wcf\system\language\preload;

use wcf\data\language\Language;
use wcf\system\event\IEvent;

final class RegisterPreloadPhrases implements IEvent
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
    public function getPreloadPhrases(): array
    {
        return $this->phrases;
    }
}
