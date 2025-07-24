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
final class ExtraChunksInSimpleFormat extends RuntimeException implements WebpExifException
{
    /** @param string[] $chunkNames */
    public function __construct(string $fourCC, array $chunkNames)
    {
        $names = \implode(', ', $chunkNames);
        parent::__construct("The file was recognized as simple {$fourCC} but contains extra chunks: {$names}");
    }
}
