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
final class UnexpectedChunk extends RuntimeException implements WebpExifException
{
    public function __construct(string $fourCC, int $offset)
    {
        $offset = \dechex($offset);
        parent::__construct("Found the unexpected chunk `{$fourCC}` at offset 0x{$offset}");
    }
}
