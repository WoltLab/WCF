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
 */
final class ConfigureBoxes
{
    public function __construct(
        private readonly AcpDashboard $dashboard,
        private readonly User $user,
        private readonly array $boxes,
    ) {
    }

    public function __invoke()
    {
        $this->resetBoxes();
        $this->saveBoxes();
    }

    private function resetBoxes(): void
    {
        $sql = "DELETE FROM wcf1_acp_dashboard_box_to_user WHERE userID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$this->user->userID]);
    }

    private function saveBoxes(): void
    {
        $sql = "INSERT INTO wcf1_acp_dashboard_box_to_user (boxName, userID, enabled, showOrder) VALUES (?, ?, ?, ?)";
        $statement = WCF::getDB()->prepare($sql);
        $showOrder = 0;
        foreach ($this->boxes as $boxName) {
            $statement->execute([
                $boxName,
                $this->user->userID,
                1,
                $showOrder++
            ]);
        }

        foreach ($this->dashboard->getBoxes() as $box) {
            if (\in_array($box->getName(), $this->boxes)) {
                continue;
            }

            $statement->execute([
                $box->getName(),
                $this->user->userID,
                0,
                0
            ]);
        }
    }
}
