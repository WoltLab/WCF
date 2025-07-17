<?php

declare(strict_types=1);

namespace WoltLab\WebpExif\Chunk;

/**
 * @author      Alexander Ebert
 * @copyright   2025 WoltLab GmbH
 * @license     The MIT License <https://opensource.org/license/mit>
 */
final class Iccp extends Chunk
{
    private function __construct(int $offset, string $data)
    {
        parent::__construct("ICCP", $offset, $data);
    }

    public static function forBytes(int $offset, string $bytes): self
    {
        return new Iccp($offset, $bytes);
    }
}
