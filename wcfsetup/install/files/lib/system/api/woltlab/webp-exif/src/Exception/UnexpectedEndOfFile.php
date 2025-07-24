<?php

declare(strict_types=1);

namespace WoltLab\WebpExif\Exception;

use RuntimeException;

/**
 * @author      Alexander Ebert
 * @copyright   2025 WoltLab GmbH
 * @license     The MIT License <https://opensource.org/license/mit>
 *
 * @internal
 */
final class UnexpectedEndOfFile extends RuntimeException implements WebpExifException
{
    public function __construct(int $offset, int $remainingBytes)
    {
        $offset = \dechex($offset);

        parent::__construct("Expected more data after offset 0x{$offset} ({$remainingBytes} bytes remaining)");
    }
}
