<?php

namespace wcf\system\package\command;

use wcf\system\WCF;

/**
 * Rebuilds the bootstrapping script.
 *
 * @author      Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.0
 * @deprecated  6.2 Use `\wcf\command\package\RebuildBootstrapper` instead.
 */
final class RebuildBootstrapper
{
    public function __invoke(): void
    {
        (new \wcf\command\package\RebuildBootstrapper())();
    }
}
