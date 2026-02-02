<?php

namespace wcf\command\package;

use wcf\data\option\OptionEditor;
use wcf\system\WCF;

/**
 * Updates the `LAST_UPDATE_TIME` constant.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class SetLastUpdateTime
{
    public function __construct(
        private readonly int $time = \TIME_NOW
    ) {}

    public function __invoke(): void
    {
        $sql = "UPDATE  wcf1_option
                SET     optionValue = ?
                WHERE   optionName = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $this->time,
            'last_update_time',
        ]);

        OptionEditor::resetCache();
    }
}
