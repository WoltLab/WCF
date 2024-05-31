<?php

namespace wcf\event\user;

use wcf\event\IPsr14Event;

/**
 * Indicates that a registration by a new user is currently validated. If $matches is not empty,
 * the registration is considered to be a spammer or an undesirable user.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class RegistrationSpamChecking implements IPsr14Event
{
    private array $matches = [];

    public function __construct(
        public readonly string $username,
        public readonly string $email,
        public readonly string $ipAddress
    ) {
    }

    public function hasMatches(): bool
    {
        return $this->matches !== [];
    }

    public function addMatch(string $key): void
    {
        $this->matches[$key] = $key;
    }

    public function getMatches(): array
    {
        return \array_values($this->matches);
    }
}
