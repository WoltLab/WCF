<?php

namespace wcf\system\gridView\renderer;

use wcf\data\DatabaseObject;
use wcf\util\UserUtil;

/**
 * Renders ipv6 embedded ipv4 address into ipv4 or returns input if true ipv6.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
class IpAddressColumnRenderer extends DefaultColumnRenderer
{
    #[\Override]
    public function render(mixed $value, DatabaseObject $row): string
    {
        if (!$value) {
            return '';
        }

        return UserUtil::convertIPv6To4($value);
    }
}
