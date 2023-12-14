<?php

namespace wcf\system\session\event;

use wcf\system\event\IEvent;

/**
 * This event allows the configuration of session variables that are to be preserved when the user changes.
 *
 * @author      Marcel Werk
 * @copyright   2001-2023 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class PreserveVariablesCollecting implements IEvent
{
    /**
     * @var string[]
     */
    public array $keys = [];
}
