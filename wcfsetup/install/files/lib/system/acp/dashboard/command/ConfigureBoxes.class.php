<?php

namespace wcf\system\acp\dashboard\command;

use wcf\data\user\User;
use wcf\system\acp\dashboard\AcpDashboard;
use wcf\system\WCF;

/**
 * Saves the configuration of the acp dashboard boxes.
 *
 * @author      Marcel Werk
 * @copyright   2001-2023 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 * @deprecated  6.2 Use `\wcf\command\acp\dashboard\ConfigureBoxes` instead.
 */
final class ConfigureBoxes
{
    /**
     * @param string[] $boxes
     */
    public function __construct(
        private readonly AcpDashboard $dashboard,
        private readonly User $user,
        private readonly array $boxes,
    ) {}

    public function __invoke(): void
    {
        (new \wcf\command\acp\dashboard\ConfigureBoxes($this->dashboard, $this->user, $this->boxes))();
    }
}
