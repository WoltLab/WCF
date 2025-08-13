<?php

namespace wcf\event\user;

use wcf\event\IPsr14Event;

/**
 * Indicates that the current user unignored another user.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
final class UserUnignored implements IPsr14Event
{
    public function __construct(public readonly int $userID) {}
}
