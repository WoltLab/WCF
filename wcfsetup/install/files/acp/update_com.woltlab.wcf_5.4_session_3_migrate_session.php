<?php

/**
 * Creates a 5.4+ session matching the current ACP session.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core
 */

use wcf\system\session\SessionHandler;
use wcf\system\WCF;

$sql = "SELECT  userID
        FROM    wcf" . WCF_N . "_user_session
        WHERE   sessionID = ?";
$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute([
    SessionHandler::getInstance()->sessionID,
]);

// Check if a matching session already exists.
if ($statement->fetchSingleColumn() == SessionHandler::getInstance()->userID) {
    return;
}

$sql = "INSERT INTO wcf" . WCF_N . "_user_session
                    (sessionID, userID, userAgent, ipAddress, creationTime, lastActivityTime, sessionVariables)
        VALUES      (?, ?, ?, ?, ?, ?, ?)";
$statement = WCF::getDB()->prepareStatement($sql);

$klass = new \ReflectionClass(SessionHandler::getInstance());
$property = $klass->getProperty('variables');
$property->setAccessible(true);
$variables = $property->getValue(SessionHandler::getInstance());

$variables[SessionHandler::class . "\0__reauthentication__"] = [
    'lastAuthentication' => TIME_NOW,
    'lastCheck' => TIME_NOW,
];

$statement->execute([
    SessionHandler::getInstance()->sessionID,
    SessionHandler::getInstance()->userID,
    SessionHandler::getInstance()->userAgent,
    SessionHandler::getInstance()->ipAddress,
    TIME_NOW,
    TIME_NOW,
    \serialize([
        'acp' => $variables,
        'frontend' => [],
    ]),
]);
