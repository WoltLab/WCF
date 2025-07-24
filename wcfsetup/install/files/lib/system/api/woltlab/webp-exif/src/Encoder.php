<?php

declare(strict_types=1);

namespace WoltLab\WebpExif;

use Nelexa\Buffer\Buffer;
use Nelexa\Buffer\StringBuffer;
use WoltLab\WebpExif\Chunk\Chunk;

/**
 * Encodes the WebP object into the binary format.
 *
 * @author      Alexander Ebert
 * @copyright   2025 WoltLab GmbH
 * @license     The MIT License <https://opensource.org/license/mit>
 */
final class Encoder
{
    /**
     * Encodes a `WebP` object into the simple or extended format depending on
     * the contained chunks.
     *
     * @return string raw bytes
     */
    public function fromWebP(WebP $webp): string
    {
        if ($webp->containsOnlyBitstream()) {
            return $this->toSimpleFormat($webp);
        }

        return $this->toExtendedFileFormat($webp);
    }

    private function toSimpleFormat(WebP $webp): string
    {
        $chunks = $webp->getChunks();
        \assert(\count($chunks) === 1);
        $bitstream = $chunks[0];

        $buffer = new StringBuffer();
        $buffer->setOrder(Buffer::LITTLE_ENDIAN);

        $buffer->insertString("RIFF");

        // The header length does include neither "RIFF" nor the length itself.
        $riffHeaderLength = 4;
        $chunkHeader = 8;
        $buffer->insertInt($riffHeaderLength + $chunkHeader + $bitstream->getLength());

        $buffer->insertString("WEBP");

        $buffer->insertString($bitstream->getFourCC());
        $buffer->insertInt($bitstream->getLength());
        $buffer->insertString($bitstream->getRawBytes());

        if (\strlen($bitstream->getRawBytes()) % 2 === 1) {
            // The padding byte is not part of the RIFF length.
            $buffer->insertByte(0);
        }

        return $buffer->toString();
    }

    private function toExtendedFileFormat(WebP $webp): string
    {
        $buffer = new StringBuffer();
        $buffer->setOrder(Buffer::LITTLE_ENDIAN);

        $buffer->insertString("RIFF");
        // Notice: The length will be inserted here at the end.
        $buffer->insertString("WEBP");

        $buffer->insertString("VP8X");
        // The VP8X header has a fixed length of 10 bytes.
        $buffer->insertInt(10);
        $this->encodeExtendedFormatFeatures($webp, $buffer);
        $buffer->insertString("\x00\x00\x00");
        $this->encodeDimensions($webp, $buffer);

        $iccp = $webp->getIccProfile();
        if ($iccp !== null) {
            $this->writeChunk($iccp, $buffer);
        }

        $anim = $webp->getAnimation();
        if ($anim !== null) {
            $this->writeChunk($anim, $buffer);

            $frames = $webp->getAnimationFrames();
            \assert(\count($frames) !== 0);

            foreach ($frames as $frame) {
                $this->writeChunk($frame, $buffer);

                foreach ($frame->getDataChunks() as $dataChunk) {
                    $this->writeChunk($dataChunk, $buffer);
                }
            }
        } else {
            $alph = $webp->getAlpha();
            if ($alph !== null) {
                $this->writeChunk($alph, $buffer);
            }

            $bitstream = $webp->getBitstream();
            assert($bitstream !== null);
            $this->writeChunk($bitstream, $buffer);
        }

        $exif = $webp->getExif();
        if ($exif !== null) {
            $this->writeChunk($exif, $buffer);
        }

        $xmp = $webp->getXmp();
        if ($xmp !== null) {
            $this->writeChunk($xmp, $buffer);
        }

        foreach ($webp->getUnknownChunks() as $chunk) {
            $this->writeChunk($chunk, $buffer);
        }

        $buffer->setPosition(4);
        // The header length does include neither "RIFF" nor the length itself.
        // Only substract 4 because the length increases the size by 4 bytes.
        $buffer->insertInt($buffer->size() - 4);

        return $buffer->toString();
    }

    private function writeChunk(Chunk $chunk, Buffer $buffer): void
    {
        $buffer->insertString($chunk->getFourCC());
        $buffer->insertInt($chunk->getLength());
        $buffer->insertString($chunk->getRawBytes());

        if (\strlen($chunk->getRawBytes()) % 2 === 1) {
            $buffer->insertByte(0);
        }
    }

    private function encodeExtendedFormatFeatures(WebP $webp, Buffer $buffer): void
    {
        $bitfield = 0;
        if ($webp->getIccProfile() !== null) {
            $bitfield |= 0b00100000;
        }
        if ($webp->getAlpha() !== null) {
            $bitfield |= 0b00010000;
        }
        if ($webp->getExif() !== null) {
            $bitfield |= 0b00001000;
        }
        if ($webp->getXmp() !== null) {
            $bitfield |= 0b00000100;
        }
        if ($webp->getAnimation() !== null) {
            $bitfield |= 0b00000010;
        }

        $buffer->insertByte($bitfield);
    }

    private function encodeDimensions(WebP $webp, Buffer $buffer): void
    {
        // Encode the width and height as a 3 byte value each.
        $width = ($webp->width - 1) & 0x00FFFFFF;
        $buffer->insertByte(($width >>  0) & 0xFF);
        $buffer->insertByte(($width >>  8) & 0xFF);
        $buffer->insertByte(($width >> 16) & 0xFF);

        $height = ($webp->height - 1) & 0x00FFFFFF;
        $buffer->insertByte(($height >>  0) & 0xFF);
        $buffer->insertByte(($height >>  8) & 0xFF);
        $buffer->insertByte(($height >> 16) & 0xFF);
    }
}
