<?php

namespace wcf\system\stat;

/**
 * Provides a general interface for statistic handler.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface IStatDailyHandler
{
    /**
     * Returns the stats.
     *
     * @return array{counter: int, total: int}
     */
    public function getData(int $date);

    /**
     * Returns a formatted counter value.
     *
     * @return  mixed
     */
    public function getFormattedCounter(int $counter);
}
