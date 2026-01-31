<?php

namespace wcf\command\language\preload;

use wcf\command\package\SetLastUpdateTime;
use wcf\data\language\Language;

/**
 * Resets the preload cache for the requested language.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2022 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class ResetPreloadCache
{
    public function __construct(
        private readonly Language $language
    ) {}

    public function __invoke(): void
    {
        // Try to remove the file if it exists.
        $filename = \WCF_DIR . $this->language->getPreloadCacheFilename();
        if (\file_exists($filename)) {
            \unlink($filename);
        }

        (new SetLastUpdateTime())();
    }
}
