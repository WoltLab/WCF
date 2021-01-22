<?php

namespace wcf\system\user;

use wcf\system\cache\builder\UserBirthdayCacheBuilder;
use wcf\system\event\EventHandler;
use wcf\system\SingletonFactory;

/**
 * Manages the user birthday cache.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\User
 */
class UserBirthdayCache extends SingletonFactory
{
    /**
     * loaded months
     * @var int[]
     */
    protected $monthsLoaded = [];

    /**
     * user birthdays
     * @var int[][]
     */
    protected $birthdays = [];

    /**
     * Loads the birthday cache.
     *
     * @param   int     $month
     */
    protected function loadMonth($month)
    {
        if (!isset($this->monthsLoaded[$month])) {
            $this->birthdays = \array_merge(
                $this->birthdays,
                UserBirthdayCacheBuilder::getInstance()->getData(['month' => $month])
            );
            $this->monthsLoaded[$month] = true;

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
     *
     * @param   int     $month
     * @param   int     $day
     * @return  int[]   list of user ids
     */
    public function getBirthdays($month, $day)
    {
        $this->loadMonth($month);

        $index = ($month < 10 ? '0' : '') . $month . '-' . ($day < 10 ? '0' : '') . $day;
        if (isset($this->birthdays[$index])) {
            return $this->birthdays[$index];
        }

        return [];
    }
}
