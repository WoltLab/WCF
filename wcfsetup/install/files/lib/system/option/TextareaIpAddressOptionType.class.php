<?php

namespace wcf\system\option;

use wcf\data\option\Option;
use wcf\system\exception\UserInputException;
use wcf\system\WCF;
use wcf\util\UserUtil;

/**
 * Option type implementation for ip address textareas.
 *
 * IP addresses will be converted into IPv6 upon saving but will be displayed as
 * IPv4 whenever applicable.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class TextareaIpAddressOptionType extends TextOptionType
{
    #[\Override]
    public function getFormElement(Option $option, mixed $value)
    {
        if (!empty($value)) {
            $ips = \explode("\n", $value);
            foreach ($ips as &$ip) {
                $ip = UserUtil::convertIPv6To4($ip);
            }
            unset($ip);

            $value = \implode("\n", $ips);
        }

        return WCF::getTPL()->render('wcf', 'textareaOptionType', [
            'option' => $option,
            'value' => $value,
        ]);
    }

    #[\Override]
    public function validate(Option $option, mixed $newValue)
    {
        if (!empty($newValue)) {
            $ips = \explode("\n", $newValue);
            foreach ($ips as $ip) {
                $ip = \trim($ip);

                $ip = UserUtil::convertIPv6To4($ip);
                if (empty($ip)) {
                    throw new UserInputException($option->optionName, 'validationFailed');
                }
            }
        }
    }

    #[\Override]
    public function getData(Option $option, mixed $newValue)
    {
        if (!empty($newValue)) {
            $ips = \explode("\n", $newValue);
            foreach ($ips as &$ip) {
                $ip = \trim($ip);
                $ip = UserUtil::convertIPv4To6($ip);
            }
            unset($ip);

            $newValue = \implode("\n", $ips);
        }

        return $newValue;
    }
}
