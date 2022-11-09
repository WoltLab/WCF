<?php

namespace wcf\system\language\preload;

final class PreloadPhrase
{
    public readonly string $name;
    public readonly bool $literal;

    public function __construct(string $name, bool $literal)
    {
        $this->name = $name;
        $this->literal = $literal;
    }
}
