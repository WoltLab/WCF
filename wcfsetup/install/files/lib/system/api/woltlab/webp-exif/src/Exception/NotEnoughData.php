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
final class NotEnoughData extends RuntimeException implements WebpExifException
{
    public function __construct(int $expected, int $found)
    {
        parent::__construct("The file size is expected to be at least {$expected} bytes but is only {$found} bytes long");
    }
}
