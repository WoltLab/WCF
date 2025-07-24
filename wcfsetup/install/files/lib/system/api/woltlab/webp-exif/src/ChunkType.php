<?php

declare(strict_types=1);

namespace WoltLab\WebpExif;

/**
 * List of the currently supported chunk types.
 *
 * @author      Alexander Ebert
 * @copyright   2025 WoltLab GmbH
 * @license     The MIT License <https://opensource.org/license/mit>
 */
enum ChunkType
{
    case ALPH;
    case ANIM;
    case ANMF;
    case EXIF;
    case ICCP;
    case VP8;
    case VP8L;
    case VP8X;
    case XMP;
    case UnknownChunk;

    public static function fromFourCC(string $fourCC): self
    {
        return match ($fourCC) {
            "ALPH" => self::ALPH,
            "ANIM" => self::ANIM,
            "ANMF" => self::ANMF,
            "EXIF" => self::EXIF,
            "ICCP" => self::ICCP,
            "VP8 " => self::VP8,
            "VP8L" => self::VP8L,
            "VP8X" => self::VP8X,
            "XMP " => self::XMP,
            default => self::UnknownChunk,
        };
    }
}
