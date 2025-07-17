<?php

declare(strict_types=1);

namespace WoltLab\WebpExif\Chunk;

use Nelexa\Buffer\Buffer;
use WoltLab\WebpExif\Chunk\Exception\ExpectedKeyFrame;
use WoltLab\WebpExif\Chunk\Exception\MissingMagicByte;
use WoltLab\WebpExif\Exception\LengthOutOfBounds;

/**
 * @author      Alexander Ebert
 * @copyright   2025 WoltLab GmbH
 * @license     The MIT License <https://opensource.org/license/mit>
 */
final class Vp8 extends Chunk
{
    private function __construct(
        public readonly int $width,
        public readonly int $height,
        int $offset,
        string $data,
    ) {
        parent::__construct("VP8 ", $offset, $data);
    }

    public static function fromBuffer(Buffer $buffer): self
    {
        $length = $buffer->getUnsignedInt();
        $startOfData = $buffer->position();
        if ($length > $buffer->remaining()) {
            throw new LengthOutOfBounds($length, $buffer->position(), $buffer->remaining());
        }

        $tag = $buffer->getUnsignedByte();

        // We expect the first frame to be a keyframe.
        $frameType = $tag & 1;
        if ($frameType !== 0) {
            throw new ExpectedKeyFrame();
        }

        // Skip the next two bytes, they are part of the header but do not
        // contain any information that is relevant to us.
        $buffer->skip(2);

        // Keyframes must start with 3 magic bytes.
        $marker = $buffer->getString(3);
        if ($marker !== "\x9D\x01\x2A") {
            throw new MissingMagicByte("VP8");
        }

        // The width and height are encoded using 2 bytes each. However, the
        // first two bits are the scale followed by 14 bits for the dimension.
        $width = $buffer->getUnsignedShort() & 0x3FFF;
        $height = $buffer->getUnsignedShort() & 0x3FFF;

        return new Vp8(
            $width,
            $height,
            $startOfData - 8,
            $buffer->setPosition($startOfData)->getString($length)
        );
    }
}
