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
final class Vp8xWithoutChunks extends RuntimeException implements WebpExifException
{
    public function __construct()
    {
        parent::__construct("The file uses the extended WebP format but does not provide any other chunks");
    }
}
