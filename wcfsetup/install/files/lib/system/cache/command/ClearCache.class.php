<?php

namespace wcf\system\cache\command;

/**
 * Performs a full cache clear.
 *
 * @author      Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.0
 * @deprecated  6.2 Use `\wcf\command\cache\ClearCache` instead.
 */
final class ClearCache
{
    public function __invoke(): void
    {
        (new \wcf\command\cache\ClearCache())();
    }
}
