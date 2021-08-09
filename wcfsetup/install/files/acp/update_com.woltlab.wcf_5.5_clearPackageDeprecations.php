<?php

/**
 * Clear the wcf1_package_(update_)?compatibility, wcf1_package_update_optional tables.
 *
 * see https://github.com/WoltLab/WCF/pull/4371
 *
 * @author Tim Duesterhus
 * @copyright 2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core
 */

use wcf\system\WCF;

$sql = "DELETE FROM wcf1_package_compatibility";
$statement = WCF::getDB()->prepare($sql);
$statement->execute();

$sql = "DELETE FROM wcf1_package_update_compatibility";
$statement = WCF::getDB()->prepare($sql);
$statement->execute();

$sql = "DELETE FROM wcf1_package_update_optional";
$statement = WCF::getDB()->prepare($sql);
$statement->execute();
