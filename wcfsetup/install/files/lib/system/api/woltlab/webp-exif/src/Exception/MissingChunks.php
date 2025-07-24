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
final class MissingChunks extends RuntimeException implements WebpExifException
{
    public function __construct()
    {
        parent::__construct("A WebP container must contain at least one data chunk");
    }
}
