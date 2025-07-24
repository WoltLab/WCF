<?php

declare(strict_types=1);

namespace WoltLab\WebpExif\Chunk;

use WoltLab\WebpExif\Chunk\Exception\UnknownChunkWithKnownFourCC;
use WoltLab\WebpExif\ChunkType;

/**
 * @author      Alexander Ebert
 * @copyright   2025 WoltLab GmbH
 * @license     The MIT License <https://opensource.org/license/mit>
 */
final class UnknownChunk extends Chunk
{
    public static function forBytes(string $fourCC, int $offset, string $bytes): self
    {
        if (ChunkType::fromFourCC($fourCC) !== ChunkType::UnknownChunk) {
            throw new UnknownChunkWithKnownFourCC($fourCC);
        }

        return new UnknownChunk($fourCC, $offset, $bytes);
    }
}
