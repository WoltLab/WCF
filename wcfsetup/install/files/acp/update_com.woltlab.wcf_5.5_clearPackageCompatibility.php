<?php

/**
 * Clear the wcf1_package_compatibility table.
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
