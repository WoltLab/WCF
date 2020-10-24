<?php
namespace wcf\system\session;

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
}
