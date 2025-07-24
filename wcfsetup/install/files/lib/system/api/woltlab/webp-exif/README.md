WebP Encoder/Decoder and EXIF Extractor
=======================================

This library can decode WebP images to extract data, for example, EXIF and XMP
chunks. The deconstructed image can be modified and then enconded as a WebP
image again, using the most efficient format.

The most notable feature is that this library is able to parse EXIF data
embedded in WebP images. PHP’s `exif_read_data()` function is limited to JPEG
images and to work around this limitation the EXIF chunk offers the ability to
inject the extracted EXIF data in a small host JPEG to process it with PHP.

Usage
-----

```php
<?php
$binary = \file_get_contents("example.webp");

$decoder = new Decoder();
$webp = $decoder->fromBinary($binary);
if ($webp->getExif() !== null) {
    // Extract the EXIF data to store it separate from the file.
    $exifData = $webp->getExif()->getRawBytes();
    // … or just retrieve the parsed EXIF data.
    \var_dump($webp->getExif()->getParsedExif());

    // Strip the EXIF data.
    $webp->withExif(null);
}

$encoder = new Encoder();
$binary = $encoder->fromWebP($webp);
```

The decoder performs a strict validation of the input data and will throw an
exception when it encounters any violations. If you do not care about the
actual problem, you can catch the generic interface `WebpExifException` that
is implemented by all exceptions.
