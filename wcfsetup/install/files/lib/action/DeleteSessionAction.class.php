<?php
namespace wcf\action;
use wcf\system\exception\IllegalLinkException;
use wcf\system\session\SessionHandler;
use wcf\system\WCF;
use wcf\util\JSON;
use wcf\util\StringUtil;

/**
 * Deletes a specific user session.
 *
 * @author	Joshua Ruesweg
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\Action
 */
class DeleteSessionAction extends AbstractAction {
	/**
	 * @inheritDoc
	 */
	public $loginRequired = true;
	
	/**
	 * @var string
	 */
	private $sessionID;
	
	/**
	 * @inheritDoc
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_POST['sessionID'])) $this->sessionID = StringUtil::trim($_POST['sessionID']);
		
		if (empty($this->sessionID)) {
			throw new IllegalLinkException();
		}
		
		$found = false;
		foreach (SessionHandler::getInstance()->getUserSessions(WCF::getUser()) as $session) {
			if ($session->getSessionID() === $this->sessionID) {
				$found = true;
				break;
			}
		}
		
		if (!$found) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @inheritDoc
	 */
	public function execute() {
		parent::execute();
		
		SessionHandler::getInstance()->deleteUserSession($this->sessionID);
		
		$this->executed();
		
		// send JSON-encoded response
		header('Content-type: application/json');
		echo JSON::encode([
			'sessionID' => $this->sessionID,
		]);
		exit;
	}
}
