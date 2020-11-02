<?php
namespace wcf\system\session;
use wcf\util\UserAgent;
use wcf\util\UserUtil;

/**
 * Represents a session.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Session
 * @since       5.4
 */
final class Session {
	/**
	 * @var array 
	 */
	private $data;
	
	/**
	 * @var bool 
	 */
	private $isAcpSession;
	
	/**
	 * @var UserAgent
	 */
	private $userAgent;
	
	/**
	 * Session constructor.
	 */
	public function __construct(array $data, bool $isAcpSession = false) {
		$this->data = $data;
		$this->isAcpSession = $isAcpSession;
	}
	
	/**
	 * Returns the session id for the session.
	 */
	public function getSessionID(): string {
		return $this->data['sessionID'];
	}
	
	/**
	 * Returns the user id for the session. If the session belongs to a guest, 
	 * `null` is returned.
	 */
	public function getUserID(): ?int {
		return $this->data['userID'];
	}
	
	/**
	 * Returns the last activity time.
	 */
	public function getLastActivityTime(): int {
		return $this->data['lastActivityTime'];
	}
	
	/**
	 * Returns `true`, if the session is an acp session.
	 */
	public function isAcpSession(): bool {
		return $this->isAcpSession;
	}
	
	/**
	 * Returns true, if the current object is the active session of the user.
	 */
	public function isCurrentSession(): bool {
		return $this->getSessionID() === SessionHandler::getInstance()->sessionID;
	}
	
	/**
	 * Returns a font awesome device icon.
	 */
	public function getDeviceIcon(): string {
		if ($this->getUserAgent()->isTablet()) {
			return 'tablet';
		}
		
		if ($this->getUserAgent()->isMobileBrowser()) {
			return 'mobile';
		}
		
		return 'laptop';
	}
	
	/**
	 * Returns the converted ip address of the session.
	 */
	public function getIpAddress(): string {
		return UserUtil::convertIPv6To4($this->data['ipAddress']);
	}
	
	/**
	 * Returns the user agent helper util class.
	 */
	public function getUserAgent(): UserAgent {
		if ($this->userAgent === null) {
			$this->userAgent = new UserAgent($this->data['userAgent']);
		}
		
		return $this->userAgent;
	}
}
