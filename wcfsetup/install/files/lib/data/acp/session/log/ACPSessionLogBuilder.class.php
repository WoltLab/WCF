<?php

namespace wcf\data\acp\session\log;

use wcf\data\DatabaseObjectBuilder;
use wcf\data\user\User;

/**
 * Builder for creating, updating and deleting ACP session logs.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<ACPSessionLog>
 */
final class ACPSessionLogBuilder extends DatabaseObjectBuilder
{
    /**
     * Sets the id of the ACP session the log entry belongs to.
     */
    public function setSessionID(string $sessionID): static
    {
        $this->properties['sessionID'] = $sessionID;

        return $this;
    }

    /**
     * Sets the user that has caused the log entry.
     */
    public function setUser(User $user): static
    {
        $this->properties['userID'] = $user->userID;

        return $this;
    }

    /**
     * Sets the ip address of the user that has caused the log entry.
     */
    public function setIpAddress(string $ipAddress): static
    {
        $this->properties['ipAddress'] = $ipAddress;

        return $this;
    }

    /**
     * Sets the user agent of the user that has caused the log entry.
     */
    public function setUserAgent(string $userAgent): static
    {
        $this->properties['userAgent'] = $userAgent;

        return $this;
    }

    /**
     * Sets the timestamp at which the log entry has been created.
     */
    public function setTime(int $time): static
    {
        $this->properties['time'] = $time;

        return $this;
    }

    /**
     * Sets the timestamp at which the associated session has been active for the last time.
     */
    public function setLastActivityTime(int $lastActivityTime): static
    {
        $this->properties['lastActivityTime'] = $lastActivityTime;

        return $this;
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['sessionID', 'ipAddress', 'time', 'lastActivityTime'];
    }
}
