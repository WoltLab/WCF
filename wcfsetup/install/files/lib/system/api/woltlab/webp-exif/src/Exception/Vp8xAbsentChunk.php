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
final class Vp8xAbsentChunk extends RuntimeException implements WebpExifException
{
    public function __construct(string $fourCC)
    {
        parent::__construct("The VP8X header indicates the presence of one or more `{$fourCC}` chunks but none are present");
    }
}
