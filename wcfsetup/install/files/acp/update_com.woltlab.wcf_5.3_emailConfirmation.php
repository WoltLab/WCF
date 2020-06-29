<?php
use wcf\system\WCF;

/**
 * @author	Joshua Ruesweg
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core
 */

// HEADS UP: This script must be executed, AFTER the SQL tables are changed. 

$sql = "UPDATE  wcf". WCF_N ."_user 
	SET     emailConfirmed = activationCode
	WHERE   activationCode <> 0";

$statement = WCF::getDB()->prepareStatement($sql);
$statement->execute();
