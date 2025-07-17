<?php

declare(strict_types=1);

namespace WoltLab\WebpExif\Chunk\Exception;

use RuntimeException;
use WoltLab\WebpExif\Exception\WebpExifException;

/**
 * @author      Alexander Ebert
 * @copyright   2025 WoltLab GmbH
 * @license     The MIT License <https://opensource.org/license/mit>
 *
 * @internal
 */
final class MissingMagicByte extends RuntimeException implements WebpExifException
{
    public function __construct(string $fourCC)
    {
        parent::__construct("The data for `{$fourCC}` is missing the magic byte");
    }
}
