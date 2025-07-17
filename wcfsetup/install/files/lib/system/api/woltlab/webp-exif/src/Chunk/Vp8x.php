<?php

declare(strict_types=1);

namespace WoltLab\WebpExif\Chunk;

use Nelexa\Buffer\Buffer;
use WoltLab\WebpExif\Chunk\Exception\DimensionsExceedInt32;
use WoltLab\WebpExif\Exception\ExtraVp8xChunk;
use WoltLab\WebpExif\Exception\UnexpectedChunk;
use WoltLab\WebpExif\Exception\Vp8xAbsentChunk;
use WoltLab\WebpExif\Exception\Vp8xHeaderLengthMismatch;
use WoltLab\WebpExif\Exception\Vp8xMissingImageData;
use WoltLab\WebpExif\Exception\Vp8xWithoutChunks;

/**
 * @author      Alexander Ebert
 * @copyright   2025 WoltLab GmbH
 * @license     The MIT License <https://opensource.org/license/mit>
 */
final class Vp8x extends Chunk
{
    private function __construct(
        public readonly int $width,
        public readonly int $height,
        int $offset,
        public readonly bool $iccProfile,
        public readonly bool $alpha,
        public readonly bool $exif,
        public readonly bool $xmp,
        public readonly bool $animation,
    ) {
        parent::__construct(
            "VP8X",
            $offset,
            // VP8X only contains the header because the actual payload are the
            // chunks that follow afterwards.
            ""
        );
    }

    /**
     * Filters the list of chunks and validates them against the specificiations
     * for VP8X images.
     *
     * @param list<Chunk> $chunks
     * @return list<Chunk>
     */
    public function filterChunks(array $chunks): array
    {
        if ($chunks === []) {
            throw new Vp8xWithoutChunks();
        }

        $nestedVp8x = \array_find($chunks, static fn($chunk) => $chunk instanceof Vp8x);
        if ($nestedVp8x !== null) {
            throw new ExtraVp8xChunk();
        }

        if ($this->iccProfile) {
            $hasIccProfile = self::removeExtraChunks(Iccp::class, $chunks);
            if (!$hasIccProfile) {
                throw new Vp8xAbsentChunk("ICCP");
            }
        }

        if ($this->alpha) {
            $hasAlpha = self::removeExtraChunks(Alph::class, $chunks);
            if (!$hasAlpha) {
                throw new Vp8xAbsentChunk("ALPH");
            }
        }

        if ($this->exif) {
            $hasExif = self::removeExtraChunks(Exif::class, $chunks);
            if (!$hasExif) {
                throw new Vp8xAbsentChunk("EXIF");
            }
        }

        if ($this->xmp) {
            $hasXmp = self::removeExtraChunks(Xmp::class, $chunks);
            if (!$hasXmp) {
                throw new Vp8xAbsentChunk("XMP ");
            }
        }

        $frames = \array_filter(
            $chunks,
            static fn($chunk) => $chunk instanceof Anmf
        );
        $bitstreams = \array_filter(
            $chunks,
            static fn($chunk) => ($chunk instanceof Vp8) || ($chunk instanceof Vp8l)
        );

        // The VP8X chunk must contain image data that can come in two flavors:
        //  1. Still images must contain either a VP8 or VP8L chunk.
        //  2. Animated images must contain multiple frames.
        if ($this->animation) {
            if (\count($frames) < 2) {
                throw new Vp8xMissingImageData(stillImage: false);
            }

            $bitstream = \array_shift($bitstreams);
            if ($bitstream !== null) {
                throw new UnexpectedChunk($bitstream->getFourCC(), $bitstream->getOffset());
            }
        } else {
            if (\count($bitstreams) !== 1) {
                throw new Vp8xMissingImageData(stillImage: true);
            }

            $frame = \array_shift($frames);
            if ($frame !== null) {
                throw new UnexpectedChunk($frame->getFourCC(), $frame->getOffset());
            }
        }

        return $chunks;
    }

    /**
     * Removes all chunks sharing the same class except for the first
     * occurrence. Returns true if at least one such chunk was found.
     *
     * @param class-string<Chunk> $className
     * @param list<Chunk> $chunks
     */
    private function removeExtraChunks(string $className, array &$chunks): bool
    {
        $hasChunk = false;
        $chunks = \array_values(\array_filter($chunks, static function ($chunk) use ($className, &$hasChunk) {
            if (!($chunk instanceof $className)) {
                return true;
            }

            if (!$hasChunk) {
                $hasChunk = true;
                return true;
            }

            return false;
        }));

        return $hasChunk;
    }

    public static function fromBuffer(Buffer $buffer): self
    {
        // The next 4 bytes represent the length of the VP8X header which must
        // be 10 bytes long.
        $expectedHeaderLength = 10;
        $length = $buffer->getUnsignedInt();
        if ($length !== $expectedHeaderLength) {
            throw new Vp8xHeaderLengthMismatch($expectedHeaderLength, $length);
        }

        $startOfData = $buffer->position();

        // The next byte contains a bit field for the features of this image,
        // the first two bits and the last bit are reserved and MUST be ignored.
        $bitField = $buffer->getByte();
        $iccProfile = ($bitField & 0b00100000) === 32;
        $alpha      = ($bitField & 0b00010000) === 16;
        $exif       = ($bitField & 0b00001000) ===  8;
        $xmp        = ($bitField & 0b00000100) ===  4;
        $animation  = ($bitField & 0b00000010) ===  2;

        // The next 24 bits are reserved.
        $buffer->skip(3);

        // The width of the canvas is represented as a uint24LE but minus one,
        // therefore we have to add 1 when decoding the value.
        $width = self::decodeDimension($buffer);

        // The height follows the same rules as the width.
        $height = self::decodeDimension($buffer);

        // The product of `width` and `height` must not exceed 2^31 - 1, the
        // maximum value of int32. We cannot assume that PHP is a 64 bit build
        // therefore we calculate the largest possible value for `$height` that
        // would not exceed the maximum value. This approach avoids hitting an
        // integer overflow on 32 bit builds when calculating the product.
        $maxInt32 = 2_147_483_647;
        $maximumHeight = $maxInt32 / $width;
        if ($maximumHeight < $height) {
            throw new DimensionsExceedInt32($width, $height);
        }

        return new Vp8x(
            $width,
            $height,
            $startOfData - 8,
            $iccProfile,
            $alpha,
            $exif,
            $xmp,
            $animation
        );
    }

    /**
     * @internal
     */
    public static function fromParameters(
        int $offset,
        int $width,
        int $height,
        bool $iccProfile,
        bool $alpha,
        bool $exif,
        bool $xmp,
        bool $animation
    ): self {
        return new Vp8x($width, $height, $offset, $iccProfile, $alpha, $exif, $xmp, $animation);
    }

    private static function decodeDimension(Buffer $buffer): int
    {
        $a = $buffer->getUnsignedByte();
        $b = $buffer->getUnsignedByte();
        $c = $buffer->getUnsignedByte();

        return ($a | ($b << 8) | ($c << 16)) + 1;
    }
}
