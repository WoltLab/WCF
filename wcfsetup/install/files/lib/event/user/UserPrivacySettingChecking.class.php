<?php

namespace wcf\event\user;

use wcf\data\user\User;
use wcf\event\IPsr14Event;

/**
 * Indicates that the privacy setting `$name` of `$user` is being checked for
 * the user with the id `$viewerUserID`, e.g. whether the online status of
 * `$user` is visible to the viewer.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class UserPrivacySettingChecking implements IPsr14Event
{
    public function __construct(
        public readonly User $user,
        public readonly string $name,
        public readonly int $viewerUserID,
        private bool $accessible,
    ) {}

    public function isAccessible(): bool
    {
        return $this->accessible;
    }

    public function setAccessible(bool $accessible): void
    {
        $this->accessible = $accessible;
    }
}
