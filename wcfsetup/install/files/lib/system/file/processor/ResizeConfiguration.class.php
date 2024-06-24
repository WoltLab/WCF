<?php

namespace wcf\system\file\processor;

final class ResizeConfiguration implements \JsonSerializable
{
    public function __construct(
        public readonly int $maxWidth,
        public readonly int $maxHeight,
        public readonly ResizeFileType $fileType,
        public readonly int $quality,
    ) {
        if ($quality <= 0 || $quality > 100) {
            throw new \OutOfRangeException("The quality value must be larger than 0 and less than or equal to 100.");
        }
    }

    #[\Override]
    public function jsonSerialize(): mixed
    {
        return [
            'maxWidth' => $this->maxWidth,
            'maxHeight' => $this->maxHeight,
            'fileType' => $this->fileType->toString(),
            'quality' => $this->quality,
        ];
    }

    public static function unbounded(): self
    {
        return new self(
            -1,
            -1,
            ResizeFileType::Keep,
            100
        );
    }
}
