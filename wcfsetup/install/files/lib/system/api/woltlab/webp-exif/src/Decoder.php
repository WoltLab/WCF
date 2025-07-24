<?php

declare(strict_types=1);

namespace WoltLab\WebpExif;

use Nelexa\Buffer\Buffer;
use Nelexa\Buffer\StringBuffer;
use WoltLab\WebpExif\Chunk\Alph;
use WoltLab\WebpExif\Chunk\Anim;
use WoltLab\WebpExif\Chunk\Anmf;
use WoltLab\WebpExif\Chunk\Chunk;
use WoltLab\WebpExif\Chunk\Exif;
use WoltLab\WebpExif\Chunk\Iccp;
use WoltLab\WebpExif\Chunk\UnknownChunk;
use WoltLab\WebpExif\Chunk\Vp8;
use WoltLab\WebpExif\Chunk\Vp8l;
use WoltLab\WebpExif\Chunk\Vp8x;
use WoltLab\WebpExif\Chunk\Xmp;
use WoltLab\WebpExif\Exception\FileSizeMismatch;
use WoltLab\WebpExif\Exception\LengthOutOfBounds;
use WoltLab\WebpExif\Exception\NotEnoughData;
use WoltLab\WebpExif\Exception\UnexpectedEndOfFile;
use WoltLab\WebpExif\Exception\UnrecognizedFileFormat;

/**
 * Decodes a WebP image from binary data.
 *
 * @author      Alexander Ebert
 * @copyright   2025 WoltLab GmbH
 * @license     The MIT License <https://opensource.org/license/mit>
 */
final class Decoder
{
    public function fromBinary(string $binary): WebP
    {
        $buffer = new StringBuffer($binary);
        $buffer->setOrder(Buffer::LITTLE_ENDIAN);
        $buffer->setReadOnly(true);

        // A RIFF container at its minimum contains the "RIFF" header, a
        // uint32LE representing the chunk size, the "WEBP" type and the data
        // section. The data section of a WebP at minimum contains one chunk
        // (header + uint32LE + data).
        //
        // The shortest possible WebP image is a simple VP8L container that
        // contains only the magic byte, a uint32 for the flags and dimensions,
        // and at last a single byte of data. This takes up 26 bytes in total.
        $expectedMinimumFileSize = 26;
        if ($buffer->size() < $expectedMinimumFileSize) {
            throw new NotEnoughData($expectedMinimumFileSize, $buffer->size());
        }

        $riff = $buffer->getString(4);
        $length = $buffer->getUnsignedInt();
        $format = $buffer->getString(4);
        if ($riff !== 'RIFF' || $format !== 'WEBP') {
            throw new UnrecognizedFileFormat();
        }

        // The length in the header does not include "RIFF" and the length
        // itself. It must therefore be exactly 8 bytes shorter than the total
        // size.
        $actualLength = $buffer->size() - 8;
        if ($length !== $actualLength) {
            throw new FileSizeMismatch($length, $actualLength);
        }

        /** @var list<Chunk> */
        $chunks = [];
        do {
            $chunks[] = $this->decodeChunk($buffer);
        } while ($buffer->hasRemaining());

        return WebP::fromChunks($chunks);
    }

    /**
     * @internal
     */
    public function decodeChunk(Buffer $buffer): Chunk
    {
        $remainingBytes = $buffer->remaining();
        if ($remainingBytes < 8) {
            throw new UnexpectedEndOfFile($buffer->position(), $buffer->remaining());
        }

        $chunkPosition = $buffer->position();
        $fourCC = $buffer->getString(4);
        $originalOffset = $buffer->position();
        $length = $buffer->getUnsignedInt();
        if ($buffer->remaining() < $length) {
            throw new LengthOutOfBounds($length, $originalOffset, $buffer->remaining());
        }

        $chunk = match (ChunkType::fromFourCC($fourCC)) {
            ChunkType::ALPH => Alph::forBytes($chunkPosition, $buffer->getString($length)),
            ChunkType::ANIM => Anim::forBytes($chunkPosition, $buffer->getString($length)),
            ChunkType::ANMF => Anmf::fromBuffer($buffer->setPosition($originalOffset)),
            ChunkType::EXIF => Exif::forBytes($chunkPosition, $buffer->getString($length)),
            ChunkType::ICCP => Iccp::forBytes($chunkPosition, $buffer->getString($length)),
            ChunkType::XMP  => Xmp::forBytes($chunkPosition, $buffer->getString($length)),
            default         => UnknownChunk::forBytes($fourCC, $chunkPosition, $buffer->getString($length)),

            // VP8, VP8L and VP8X are a bit different because these need to be
            // able to evaluate the length of the chunk themselves for various
            // reasons.
            ChunkType::VP8  => Vp8::fromBuffer($buffer->setPosition($originalOffset)),
            ChunkType::VP8L => Vp8l::fromBuffer($buffer->setPosition($originalOffset)),
            ChunkType::VP8X => Vp8x::fromBuffer($buffer->setPosition($originalOffset)),
        };

        // The length of every chunk in a RIFF container must be of an even
        // length. Uneven chunks must be padded by a single 0x00 at the end.
        if ($length % 2 === 1) {
            $buffer->setPosition($buffer->position() + 1);
        }

        return $chunk;
    }
}
