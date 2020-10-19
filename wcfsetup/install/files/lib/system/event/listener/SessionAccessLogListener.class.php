<?php
namespace wcf\system\event\listener;
use wcf\data\acp\session\access\log\ACPSessionAccessLogEditor;
use wcf\data\acp\session\log\ACPSessionLog;
use wcf\data\acp\session\log\ACPSessionLogEditor;
use wcf\system\WCF;
use wcf\util\UserUtil;

/**
 * Creates the session access log.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2020 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Event\Listener
 */
class SessionAccessLogListener implements IParameterizedEventListener {
	/**
	 * @inheritDoc
	 */
	public function execute($eventObj, $className, $eventName, array &$parameters) {
		if (WCF::getUser()->userID && WCF::getSession()->getPermission('admin.general.canUseAcp') && !defined(get_class($eventObj).'::DO_NOT_LOG')) {
			// try to find existing session log
			$sql = "SELECT	sessionLogID
				FROM	wcf".WCF_N."_acp_session_log
				WHERE	sessionID = ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute([
				WCF::getSession()->sessionID,
			]);
			$row = $statement->fetchArray();
			if (!empty($row['sessionLogID'])) {
				$sessionLogID = $row['sessionLogID'];
				
				$sessionLogEditor = new ACPSessionLogEditor(new ACPSessionLog(null, ['sessionLogID' => $sessionLogID]));
				$sessionLogEditor->update([
					'lastActivityTime' => TIME_NOW
				]);
			}
			else {
				// create new session log
				$sessionLog = ACPSessionLogEditor::create([
					'sessionID' => WCF::getSession()->sessionID,
					'userID' => WCF::getUser()->userID,
					'ipAddress' => UserUtil::getIpAddress(),
					'hostname' => @gethostbyaddr(UserUtil::getIpAddress()),
					'userAgent' => UserUtil::getUserAgent(),
					'time' => TIME_NOW,
					'lastActivityTime' => TIME_NOW
				]);
				$sessionLogID = $sessionLog->sessionLogID;
			}
			
			// format request uri
			$requestURI = UserUtil::getRequestURI();
			// remove directories
			$URIComponents = explode('/', $requestURI);
			$requestURI = array_pop($URIComponents);
			// remove session url
			$requestURI = preg_replace('/(?:\?|&)s=[a-f0-9]{40}/', '', $requestURI);
			
			// save access
			ACPSessionAccessLogEditor::create([
				'sessionLogID' => $sessionLogID,
				'ipAddress' => UserUtil::getIpAddress(),
				'time' => TIME_NOW,
				'requestURI' => $requestURI,
				'requestMethod' => substr($_SERVER['REQUEST_METHOD'] ?? '', 0, 255),
				'className' => get_class($eventObj)
			]);
		}
	}
}
