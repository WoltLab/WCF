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
final class Vp8xHeaderLengthMismatch extends OutOfBoundsException implements WebpExifException
{
    public function __construct(int $expected, int $found)
    {
        parent::__construct("The length of the VP8X header was expected to be {$expected} but found {$found}");
    }
}
