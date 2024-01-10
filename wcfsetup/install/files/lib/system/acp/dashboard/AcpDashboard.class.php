<?php

namespace wcf\system\acp\dashboard;

use wcf\system\acp\dashboard\box\IAcpDashboardBox;
use wcf\system\acp\dashboard\event\AcpDashboardCollecting;
use wcf\system\event\EventHandler;
use wcf\system\WCF;

/**
 * Represents the acp dashboard.
 *
 * @author      Marcel Werk
 * @copyright   2001-2023 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class AcpDashboard
{
    /**
     * @var IAcpDashboardBox[]
     */
    private array $boxes;

    public function __construct()
    {
        $event = new AcpDashboardCollecting();
        EventHandler::getInstance()->fire($event);

        $this->boxes = $event->getBoxes();
    }

    /**
     * @return IAcpDashboardBox[]
     */
    public function getBoxes(): array
    {
        return $this->boxes;
    }

    /**
     * @return IAcpDashboardBox[]
     */
    public function getAccessibleBoxes(): array
    {
        return \array_filter($this->getBoxes(), static fn (IAcpDashboardBox $box) => $box->isAccessible());
    }

    /**
     * @return IAcpDashboardBox[]
     */
    public function getVisibleBoxes(): array
    {
        $userConfiguration = $this->getUserConfiguration();
        $availableBoxes = \array_filter($this->getAccessibleBoxes(), static fn (IAcpDashboardBox $box) => $box->hasContent());
        $availableBoxes = \array_filter(
            $availableBoxes,
            static function (IAcpDashboardBox $box) use ($userConfiguration) {
                return !isset($userConfiguration[$box->getName()]) || $userConfiguration[$box->getName()]['enabled'];
            }
        );

        \uasort(
            $availableBoxes,
            static function (IAcpDashboardBox $boxA, IAcpDashboardBox $boxB) use ($userConfiguration) {
                $showOrderA = 999;
                $showOrderB = 999;

                if (isset($userConfiguration[$boxA->getName()])) {
                    $showOrderA = $userConfiguration[$boxA->getName()]['showOrder'];
                }
                if (isset($userConfiguration[$boxB->getName()])) {
                    $showOrderB = $userConfiguration[$boxB->getName()]['showOrder'];
                }

                if ($showOrderA < $showOrderB) {
                    return -1;
                } else if ($showOrderA > $showOrderB) {
                    return 1;
                }

                return 0;
            }
        );

        return $availableBoxes;
    }

    public function getUserConfiguration(): array
    {
        $boxes = [];
        $sql = "SELECT * FROM wcf1_acp_dashboard_box_to_user WHERE userID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([WCF::getUser()->userID]);
        while ($row = $statement->fetchArray()) {
            $boxes[$row['boxName']] = $row;
        }

        return $boxes;
    }
}
