<?php

namespace wcf\data\user\authentication\failure;

use wcf\data\DatabaseObjectBuilder;

/**
 * Builder for creating, updating and deleting user authentication failures.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<UserAuthenticationFailure>
 */
final class UserAuthenticationFailureBuilder extends DatabaseObjectBuilder
{
    /**
     * Sets the environment in which the authentication failure occurred.
     *
     * @param 'user'|'admin' $environment
     */
    public function setEnvironment(string $environment): static
    {
        $this->properties['environment'] = $environment;

        return $this;
    }

    /**
     * Sets the id of the affected user or `null` if the provided username or
     * email address is not associated with any registered user.
     */
    public function setUserID(?int $userID): static
    {
        $this->properties['userID'] = $userID;

        return $this;
    }

    /**
     * Sets the username or email address used to login.
     */
    public function setUsername(string $username): static
    {
        $this->properties['username'] = $username;

        return $this;
    }

    /**
     * Sets the time at which the authentication failure occurred.
     */
    public function setTime(int $time): static
    {
        $this->properties['time'] = $time;

        return $this;
    }

    /**
     * Sets the ip address of the user trying to login.
     */
    public function setIpAddress(string $ipAddress): static
    {
        $this->properties['ipAddress'] = $ipAddress;

        return $this;
    }

    /**
     * Sets the user agent of the user trying to login.
     */
    public function setUserAgent(string $userAgent): static
    {
        $this->properties['userAgent'] = $userAgent;

        return $this;
    }

    /**
     * Sets the validation error that caused the authentication failure.
     */
    public function setValidationError(string $validationError): static
    {
        $this->properties['validationError'] = $validationError;

        return $this;
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['environment', 'username', 'time'];
    }
}
