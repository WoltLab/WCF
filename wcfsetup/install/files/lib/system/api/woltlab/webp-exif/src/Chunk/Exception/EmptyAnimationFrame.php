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
final class EmptyAnimationFrame extends RuntimeException implements WebpExifException
{
    public function __construct(int $offset)
    {
        $offset = \dechex($offset);
        parent::__construct("The ANMF frame at offset 0x{$offset} contains no chunks");
    }
}
