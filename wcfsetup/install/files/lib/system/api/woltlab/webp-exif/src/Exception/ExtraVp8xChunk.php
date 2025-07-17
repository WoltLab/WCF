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
final class ExtraVp8xChunk extends OutOfBoundsException implements WebpExifException
{
    public function __construct()
    {
        parent::__construct("An extended WebP image format may only contain a single VP8X chunk");
    }
}
