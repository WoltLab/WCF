<?php

namespace wcf\system\file\processor;

final class ThumbnailFormat
{
    private const THUMBNAIL_FORMAT_VERSION = 1;

    public function __construct(
        public readonly string $identifier,
        public readonly int $height,
        public readonly int $width,
        public readonly bool $retainDimensions,
    ) {
        $maxUnsignedShort = 65535;

        if ($this->width > $maxUnsignedShort) {
            throw new \OutOfBoundsException("The thumbnail width cannot exceed {$maxUnsignedShort} pixels.");
        }

        if ($this->height > $maxUnsignedShort) {
            throw new \OutOfBoundsException("The thumbnail height cannot exceed {$maxUnsignedShort} pixels.");
        }
    }

    public function toChecksum(): string
    {
        return \bin2hex(\pack(
            'CvvC',
            self::THUMBNAIL_FORMAT_VERSION,
            $this->width,
            $this->height,
            $this->retainDimensions ? 1 : 0,
        ));
    }
}
