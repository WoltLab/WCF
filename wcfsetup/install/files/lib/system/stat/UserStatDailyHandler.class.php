<?php

namespace wcf\system\stat;

/**
 * Stat handler implementation for user stats.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserStatDailyHandler extends AbstractStatDailyHandler
{
    /**
     * @inheritDoc
     */
    public function getData($date)
    {
        return [
            'counter' => $this->getCounter($date, 'wcf1_user', 'registrationDate'),
            'total' => $this->getTotal($date, 'wcf1_user', 'registrationDate'),
        ];
    }
}
