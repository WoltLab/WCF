<?php

namespace wcf\system\acp\dashboard\box;

/**
 * Provides an abstract implementation of acp dashboard boxes.
 *
 * @author      Marcel Werk
 * @copyright   2001-2023 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class AbstractAcpDashboardBox implements IAcpDashboardBox
{
    public function isAccessible(): bool
    {
        return true;
    }

    public function hasContent(): bool
    {
        return true;
    }
}
