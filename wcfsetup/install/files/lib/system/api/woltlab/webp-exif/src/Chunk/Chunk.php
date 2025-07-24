<?php

declare(strict_types=1);

namespace WoltLab\WebpExif\Chunk;

/**
 * @author      Alexander Ebert
 * @copyright   2025 WoltLab GmbH
 * @license     The MIT License <https://opensource.org/license/mit>
 */
abstract class Chunk
{
    protected function __construct(
        private readonly string $fourCC,
        private readonly int $offset,
        private readonly string $data,
    ) {}

    public function getFourCC(): string
    {
        return $this->fourCC;
    }

    public function getLength(): int
    {
        return \strlen($this->data);
    }

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function getRawBytes(): string
    {
        return $this->data;
    }
}
