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
final class UnrecognizedFileFormat extends RuntimeException implements WebpExifException
{
    public function __construct()
    {
        parent::__construct("The provided source appears not be a WebP image");
    }
}
