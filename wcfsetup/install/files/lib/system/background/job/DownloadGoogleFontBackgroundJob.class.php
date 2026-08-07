<?php

namespace wcf\system\background\job;

use wcf\system\style\FontManager;

/**
 * Downloads a Google Font family.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.4
 */
final class DownloadGoogleFontBackgroundJob extends AbstractBackgroundJob
{
    /**
     * @inheritDoc
     */
    const MAX_FAILURES = 5;

    public function __construct(private readonly string $family) {}

    #[\Override]
    public function retryAfter(): int
    {
        return 10 * 60;
    }

    #[\Override]
    public function perform(): void
    {
        FontManager::getInstance()->downloadFamily($this->family);
    }
}
