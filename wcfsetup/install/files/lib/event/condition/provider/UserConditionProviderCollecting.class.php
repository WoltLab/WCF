<?php

namespace wcf\event\condition\provider;

use wcf\event\IPsr14Event;
use wcf\system\condition\provider\UserConditionProvider;

/**
 * Indicates that the provider for user condition is collecting conditions.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class UserConditionProviderCollecting implements IPsr14Event
{
    public function __construct(public readonly UserConditionProvider $provider)
    {
    }
}
