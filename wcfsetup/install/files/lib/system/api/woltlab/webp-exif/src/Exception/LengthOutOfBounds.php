<?php

declare(strict_types=1);

namespace WoltLab\WebpExif\Exception;

use OutOfBoundsException;

/**
 * @author      Alexander Ebert
 * @copyright   2025 WoltLab GmbH
 * @license     The MIT License <https://opensource.org/license/mit>
 *
 * @internal
 */
final class LengthOutOfBounds extends OutOfBoundsException implements WebpExifException
{
    public function __construct(int $length, int $offset, int $remainingBytes)
    {
        $offset = \dechex($offset);

        parent::__construct("Found the length {$length} at offset 0x{$offset} but there are only {$remainingBytes} bytes remaining");
    }
}
