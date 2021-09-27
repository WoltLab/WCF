<?php

namespace wcf\system\session;

use wcf\util\IpAddress;
use wcf\util\UserAgent;

/**
 * Represents a session.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Session
 * @since       5.4
 */
final class Session
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var UserAgent
     */
    private $userAgent;

    /**
     * Session constructor.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Returns the session id for the session.
     */
    public function getSessionID(): string
    {
        return $this->data['sessionID'];
    }

    /**
     * Returns the user id for the session. If the session belongs to a guest,
     * `null` is returned.
     */
    public function getUserID(): ?int
    {
        return $this->data['userID'];
    }

    /**
     * Returns the last activity time.
     */
    public function getLastActivityTime(): int
    {
        return $this->data['lastActivityTime'];
    }

    /**
     * Returns true, if the current object is the active session of the user.
     */
    public function isCurrentSession(): bool
    {
        return $this->getSessionID() === SessionHandler::getInstance()->sessionID;
    }

    /**
     * @deprecated 5.4 Use ->getUserAgent()->getDeviceIcon().
     */
    public function getDeviceIcon(): string
    {
        return $this->getUserAgent()->getDeviceIcon();
    }

    /**
     * Returns the last used ip address of the session.
     */
    public function getIpAddress(): IpAddress
    {
        $ipAddress = new IpAddress($this->data['ipAddress']);

        return $ipAddress->asV4() ?: $ipAddress;
    }

    /**
     * Returns the user agent helper util class.
     */
    public function getUserAgent(): UserAgent
    {
        if ($this->userAgent === null) {
            $this->userAgent = new UserAgent($this->data['userAgent']);
        }

        return $this->userAgent;
    }
}
