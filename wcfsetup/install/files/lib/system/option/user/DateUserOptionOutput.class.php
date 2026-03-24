<?php

namespace wcf\system\option\user;

use wcf\data\user\option\UserOption;
use wcf\data\user\User;
use wcf\system\WCF;

/**
 * User option output implementation for for the output of a date input.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class DateUserOptionOutput implements IUserOptionOutput
{
    #[\Override]
    public function getOutput(User $user, UserOption $option, string $value)
    {
        if (empty($value) || $value == '0000-00-00') {
            return '';
        }

        $date = self::splitDate($value);

        return \IntlDateFormatter::formatObject(
            WCF::getUser()->getLocalDate(\gmmktime(12, 1, 1, $date['month'], $date['day'], $date['year'])),
            [
                \IntlDateFormatter::LONG,
                \IntlDateFormatter::NONE,
            ],
            WCF::getLanguage()->getLocale()
        );
    }

    /**
     * Splits the given dashed date into its components.
     *
     * @param string $value
     * @return  int[]
     */
    protected static function splitDate($value)
    {
        $year = $month = $day = 0;
        $optionValue = \explode('-', $value);
        $year = \intval($optionValue[0]);
        if (isset($optionValue[1])) {
            $month = \intval($optionValue[1]);
        }
        if (isset($optionValue[2])) {
            $day = \intval($optionValue[2]);
        }

        return ['year' => $year, 'month' => $month, 'day' => $day];
    }
}
