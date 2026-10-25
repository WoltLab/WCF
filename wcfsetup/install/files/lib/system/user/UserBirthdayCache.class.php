<?php

namespace wcf\system\user;

use wcf\system\event\EventHandler;
use wcf\system\SingletonFactory;

/**
 * Manages the user birthday cache.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserBirthdayCache extends SingletonFactory
{
    /**
     * user birthdays
     *
     * @var array<int, array<int, list<int>>>
     */
    protected array $birthdays = [];

    /**
     * Loads the birthday cache.
     *
     * @param int $month
     * @return void
     */
    protected function loadMonth(int $month): void
    {
        if (!\array_key_exists($month, $this->birthdays)) {
            $this->birthdays[$month] = new \wcf\system\cache\tolerant\UserBirthdayCache($month)->getCache();

            $data = [
                'birthdays' => $this->birthdays,
                'month' => $month,
            ];
            EventHandler::getInstance()->fireAction($this, 'loadMonth', $data);
            $this->birthdays = $data['birthdays'];
        }
    }

    /**
     * Returns the user birthdays for a specific day.
     * @return  int[]   list of user ids
     */
    public function getBirthdays(int $month, int $day): array
    {
        $this->loadMonth($month);

        return $this->birthdays[$month][$day] ?? [];
    }
}
