<?php

namespace wcf\system\file\processor;

enum ResizeFileType
{
    case Jpeg;
    case Keep;
    case Webp;

    public function toString(): string
    {
        return match ($this) {
            self::Jpeg => 'image/jpeg',
            self::Keep => 'keep',
            self::Webp => 'image/webp',
        };
    }

    public static function fromString(string $fileType): self
    {
        return match ($fileType) {
            'image/jpeg' => self::Jpeg,
            'keep' => self::Keep,
            'image/webp' => self::Webp,
        };
    }
}
