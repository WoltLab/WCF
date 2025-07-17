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
final class Vp8xMissingImageData extends OutOfBoundsException implements WebpExifException
{
    public function __construct(bool $stillImage)
    {
        if ($stillImage) {
            $message = "The file did not contain any VP8 or VP8L chunks";
        } else {
            $message = "The file did not contain multiple ANMF chunks";
        }

        parent::__construct($message);
    }
}
