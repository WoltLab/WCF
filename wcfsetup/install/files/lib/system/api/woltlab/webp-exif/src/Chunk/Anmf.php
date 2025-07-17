<?php

declare(strict_types=1);

namespace WoltLab\WebpExif\Chunk;

use Nelexa\Buffer\Buffer;
use Override;
use WoltLab\WebpExif\Chunk\Exception\AnimationFrameWithoutBitstream;
use WoltLab\WebpExif\Chunk\Exception\EmptyAnimationFrame;
use WoltLab\WebpExif\Decoder;
use WoltLab\WebpExif\Exception\LengthOutOfBounds;
use WoltLab\WebpExif\Exception\UnexpectedChunk;
use WoltLab\WebpExif\Exception\UnexpectedEndOfFile;

/**
 * @author      Alexander Ebert
 * @copyright   2025 WoltLab GmbH
 * @license     The MIT License <https://opensource.org/license/mit>
 */
final class Anmf extends Chunk
{
    /** @param list<Chunk> $chunks */
    private function __construct(
        int $offset,
        string $data,
        private readonly array $chunks
    ) {
        parent::__construct("ANMF", $offset, $data);
    }

    #[Override]
    public function getLength(): int
    {
        return \array_reduce(
            $this->chunks,
            static function ($acc, $chunk) {
                $paddingByte = $chunk->getLength() % 2;
                return $acc + $chunk->getLength() + $paddingByte + 8;
            },
            parent::getLength(),
        );
    }

    /**
     * @return list<Chunk>
     */
    public function getDataChunks(): array
    {
        return $this->chunks;
    }

    public static function fromBuffer(Buffer $buffer): self
    {
        $offset = $buffer->position();
        $length = $buffer->getUnsignedInt();
        if ($length > $buffer->remaining()) {
            throw new LengthOutOfBounds($length, $offset, $buffer->remaining());
        }

        // An animation frame contains at least 16 bytes for the header.
        if ($buffer->remaining() < 16) {
            throw new UnexpectedEndOfFile($buffer->position(), $buffer->remaining());
        }

        // The next 8 bytes contain the X and Y coordinates, as well as the
        // frame witdth and height. Afterwards there are 3 bytes for the frame
        // duration followed by 1 byte representing a bit field. (= 16 bytes)
        $frameHeader = $buffer->getString(16);

        $chunks = [];
        $decoder = new Decoder();
        while ($buffer->position() < $offset + 4 + $length) {
            $chunks[] = $decoder->decodeChunk($buffer);
        }

        self::validateChunks($offset, $chunks);

        return new Anmf($offset, $frameHeader, $chunks);
    }

    /**
     * @param list<Chunk> $chunks
     */
    private static function validateChunks(int $offset, array $chunks): void
    {
        if ($chunks === []) {
            throw new EmptyAnimationFrame($offset);
        }

        // An ALPH chunk can only appear at the start of the frame data.
        if ($chunks[0] instanceof Alph) {
            \array_shift($chunks);

            if ($chunks === []) {
                throw new AnimationFrameWithoutBitstream($offset);
            }
        }

        // A bitstream chunk can only appear at the first position or after an
        // ALPH chunk.
        if ($chunks[0] instanceof Vp8 || $chunks[0] instanceof Vp8l) {
            \array_shift($chunks);
        } else {
            throw new AnimationFrameWithoutBitstream($offset);
        }

        // After the bitstream chunk there may be an infinite number of unknown
        // chunks, but no known ones.
        $disallowedChunk = \array_find($chunks, static fn($chunk) => !($chunk instanceof UnknownChunk));
        if ($disallowedChunk instanceof Chunk) {
            throw new UnexpectedChunk($disallowedChunk->getFourCC(), $disallowedChunk->getOffset());
        }
    }
}
