<?php

declare(strict_types=1);

namespace WoltLab\WebpExif\Chunk;

/**
 * @author      Alexander Ebert
 * @copyright   2025 WoltLab GmbH
 * @license     The MIT License <https://opensource.org/license/mit>
 */
final class Xmp extends Chunk
{
    private function __construct(int $offset, string $data)
    {
        parent::__construct("XMP ", $offset, $data);
    }

    public static function forBytes(int $offset, string $bytes): self
    {
        return new Xmp($offset, $bytes);
    }
}
