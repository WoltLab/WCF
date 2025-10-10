<?php

namespace wcf\system\language\preload\command;

use wcf\data\language\Language;

/**
 * Resets the preload cache for the requested language.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2022 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.0
 * @deprecated  6.2 Use `\wcf\command\language\preload\ResetPreloadCache` instead.
 */
final class ResetPreloadCache
{
    public function __construct(
        private readonly Language $language
    ) {}

    public function __invoke(): void
    {
        (new \wcf\command\language\preload\ResetPreloadCache(
            $this->language
        ))();
    }
}
