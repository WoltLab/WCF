<?php
namespace wcf\system\event\listener;
use wcf\data\acp\session\access\log\ACPSessionAccessLogEditor;
use wcf\data\acp\session\log\ACPSessionLog;
use wcf\data\acp\session\log\ACPSessionLogEditor;
use wcf\system\event\EventListener;
use wcf\system\WCF;

/**
 * Creates the session access log.
 * 
 * @author	Marcel Werk
 * @copyright	2001-2011 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.wcf
 * @subpackage	system.event.listener
 * @category 	Community Framework
 */
class SessionAccessLogListener implements EventListener {
	/**
	 * @see wcf\system\event\EventListener::execute()
	 */
	public function execute($eventObj, $className, $eventName) {
		if (WCF::getUser()->userID && WCF::getSession()->getPermission('admin.general.canUseAcp') && !defined(get_class($eventObj).'::DO_NOT_LOG')) {
			// try to find existing session log
			$sql = "SELECT	sessionLogID
				FROM	wcf".WCF_N."_acp_session_log
				WHERE	sessionID = ?
					AND lastActivityTime >= ?";
			$statement = WCF::getDB()->prepareStatement($sql);
			$statement->execute(array(
				WCF::getSession()->sessionID,
				(TIME_NOW - SESSION_TIMEOUT)
			));
			$row = $statement->fetchArray();
			if (!empty($row['sessionLogID'])) {
				$sessionLogID = $row['sessionLogID'];
				
				$sessionLogEditor = new ACPSessionLogEditor(new ACPSessionLog($sessionLogID));
				$sessionLogEditor->update(array(
					'lastActivityTime' => TIME_NOW
				));
			}
			else {
				// create new session log
				$sessionLog = ACPSessionLogEditor::create(array(
					'sessionID' => WCF::getSession()->sessionID,
					'userID' => WCF::getUser()->userID,
					'ipAddress' => WCF::getSession()->ipAddress,
					'hostname' => @gethostbyaddr(WCF::getSession()->ipAddress),
					'userAgent' => WCF::getSession()->userAgent,
					'time' => TIME_NOW,
					'lastActivityTime' => TIME_NOW
				));
				$sessionLogID = $sessionLog->sessionLogID;
			}
			
			// format request uri
			$requestURI = WCF::getSession()->requestURI;
			// remove directories
			$URIComponents = explode('/', $requestURI);
			$requestURI = array_pop($URIComponents);
			// remove session url
			$requestURI = preg_replace('/(?:\?|&)s=[a-f0-9]{40}/', '', $requestURI);
			
			// save access
			ACPSessionAccessLogEditor::create(array(
				'sessionLogID' => $sessionLogID,
				'packageID' => PACKAGE_ID,
				'ipAddress' => WCF::getSession()->ipAddress,
				'time' => TIME_NOW,
				'requestURI' => $requestURI,
				'requestMethod' => WCF::getSession()->requestMethod,
				'className' => get_class($eventObj)
			));
		}
	}
}
