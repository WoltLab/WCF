<?php

namespace wcf\system\style\command;

use wcf\data\style\Style;
use wcf\system\WCF;

/**
 * Generate then `manifest-*.json` files for a style.
 *
 * @author      Olaf Braun
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 * @deprecated  6.2 Use `\wcf\command\style\CreateManifest` instead.
 */
final class CreateManifest
{
    public function __construct(
        private readonly Style $style
    ) {}

    public function __invoke(): void
    {
        (new \wcf\command\style\CreateManifest(
            $this->style
        ))();
    }
}
