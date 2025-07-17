<?php

declare(strict_types=1);

namespace WoltLab\WebpExif\Chunk\Exception;

use OutOfRangeException;
use WoltLab\WebpExif\Exception\WebpExifException;

/**
 * @author      Alexander Ebert
 * @copyright   2025 WoltLab GmbH
 * @license     The MIT License <https://opensource.org/license/mit>
 *
 * @internal
 */
final class DimensionsExceedInt32 extends OutOfRangeException implements WebpExifException
{
    public function __construct(int $width, int $height)
    {
        parent::__construct("The product of {$width} and {$height} exceeds the boundary of 2^31 - 1");
    }
}
