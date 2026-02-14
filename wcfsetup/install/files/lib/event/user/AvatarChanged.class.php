<?php

namespace wcf\event\user;

use wcf\data\file\File;
use wcf\data\user\User;
use wcf\event\IPsr14Event;

/**
 * Indicates that the avatar of a user has been changed.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class AvatarChanged implements IPsr14Event
{
    public function __construct(
        public readonly User $user,
        public readonly ?File $file
    ) {}
}
