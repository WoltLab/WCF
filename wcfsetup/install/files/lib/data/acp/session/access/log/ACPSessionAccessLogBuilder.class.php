<?php

namespace wcf\data\acp\session\access\log;

use wcf\data\acp\session\log\ACPSessionLog;
use wcf\data\DatabaseObjectBuilder;

/**
 * Builder for creating, updating and deleting ACP session access logs.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<ACPSessionAccessLog>
 */
final class ACPSessionAccessLogBuilder extends DatabaseObjectBuilder
{
    /**
     * Sets the ACP session log the access log entry belongs to.
     */
    public function setSessionLog(ACPSessionLog $sessionLog): static
    {
        $this->properties['sessionLogID'] = $sessionLog->sessionLogID;

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
     * Sets the timestamp at which the log entry has been created.
     */
    public function setTime(int $time): static
    {
        $this->properties['time'] = $time;

        return $this;
    }

    /**
     * Sets the uri of the logged request.
     */
    public function setRequestURI(string $requestURI): static
    {
        $this->properties['requestURI'] = $requestURI;

        return $this;
    }

    /**
     * Sets the request method of the logged request, e.g. `GET` or `POST`.
     */
    public function setRequestMethod(string $requestMethod): static
    {
        $this->properties['requestMethod'] = $requestMethod;

        return $this;
    }

    /**
     * Sets the name of the PHP controller class that has handled the request.
     */
    public function setClassName(string $className): static
    {
        $this->properties['className'] = $className;

        return $this;
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['sessionLogID', 'ipAddress', 'time', 'requestURI', 'requestMethod', 'className'];
    }
}
