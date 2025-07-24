<?php

declare(strict_types=1);

namespace WoltLab\WebpExif\Chunk;

use WoltLab\WebpExif\Chunk\Exception\MissingExifExtension;

/**
 * @author      Alexander Ebert
 * @copyright   2025 WoltLab GmbH
 * @license     The MIT License <https://opensource.org/license/mit>
 */
final class Exif extends Chunk
{
    private function __construct(int $offset, string $data)
    {
        parent::__construct("EXIF", $offset, $data);
    }

    /**
     * @param bool $sectionsAsArrays Creates separate arrays for each contained section
     * @return ($sectionsAsArrays is true ? null|array<string, array<string, int|string>> : null|array<string, int|string>)
     * @throws MissingExifExtension
     */
    public function getParsedExif(bool $sectionsAsArrays = true): ?array
    {
        $bytes = $this->getRawBytes();
        if (\strlen($bytes) === 0) {
            return null;
        }

        // We're offloading the EXIF decoding task for `exif_read_data()` which
        // cannot process WebP.
        if (!\function_exists('exif_read_data')) {
            // @codeCoverageIgnoreStart
            throw new MissingExifExtension();
            // @codeCoverageIgnoreEnd
        }

        // A tiny JPEG is used as the host for the EXIF data.
        // See https://github.com/mathiasbynens/small/blob/267b39f682598eebb0dafe7590b1504be79b5cad/jpeg.jpg
        // This is a modified version without the leading 0xFF 0xD8 (SOI)!
        $jpegBody = "\xFF\xDB\x00\x43\x00\x03\x02\x02\x02\x02\x02\x03\x02\x02\x02\x03\x03\x03\x03\x04\x06\x04\x04\x04\x04\x04\x08\x06\x06\x05\x06\x09\x08\x0A\x0A\x09\x08\x09\x09\x0A\x0C\x0F\x0C\x0A\x0B\x0E\x0B\x09\x09\x0D\x11\x0D\x0E\x0F\x10\x10\x11\x10\x0A\x0C\x12\x13\x12\x10\x13\x0F\x10\x10\x10\xFF\xC9\x00\x0B\x08\x00\x01\x00\x01\x01\x01\x11\x00\xFF\xCC\x00\x06\x00\x10\x10\x05\xFF\xDA\x00\x08\x01\x01\x00\x00\x3F\x00\xD2\xCF\x20\xFF\xD9";

        // The image does not have a JFIF tag and instead directly starts with
        // the quantization table (DQT, 0xFF xDB). The SOI (start of image, 0xFF
        // 0xD8) is prepended below to simpify the construction of the image.
        $soiTag = "\xFF\xD8";

        $app1Tag = "\xFF\xE1";
        $exifHeader = "\x45\x78\x69\x66\x00\x00";

        // The byte length is the length of the EXIF header (6), the payload and
        // the two bytes for the length itself.
        $byteLength = \pack("n", 2 + 6 + \strlen($bytes));

        // We must suppress warnings here to gracefully recover from bad EXIF data.
        $exif = @\exif_read_data(
            \sprintf(
                "data://image/jpeg;base64,%s",
                \base64_encode(
                    $soiTag .
                        $app1Tag .
                        $byteLength .
                        $exifHeader .
                        $bytes .
                        $jpegBody
                ),
            ),
            as_arrays: $sectionsAsArrays,
        );

        // There is no known case where the call to `\exif_read_data()` can fail
        // hard, there may be warnings about garbage data but since the
        // constructed host image is guaranteed to be valid, it is infallible.
        \assert($exif !== false);

        /** @var array<string, int|string> $exif */
        return $exif;
    }

    public static function forBytes(int $offset, string $bytes): self
    {
        return new Exif($offset, $bytes);
    }
}
