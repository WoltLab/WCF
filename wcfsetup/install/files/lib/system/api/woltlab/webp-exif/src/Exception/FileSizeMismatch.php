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
final class FileSizeMismatch extends RuntimeException implements WebpExifException
{
    public function __construct(int $expected, int $found)
    {
        parent::__construct("The file reports a payload of {$expected} bytes, but actually contains {$found} bytes");
    }
}
