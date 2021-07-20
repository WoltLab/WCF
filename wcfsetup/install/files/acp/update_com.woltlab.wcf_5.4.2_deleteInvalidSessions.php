<?php

/**
 * Deletes all sessions with an empty IP address.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core
 */

use wcf\system\WCF;

$sql = "DELETE FROM wcf1_session WHERE ipAddress = ?";
$statement = WCF::getDB()->prepare($sql);
$statement->execute(['']);
