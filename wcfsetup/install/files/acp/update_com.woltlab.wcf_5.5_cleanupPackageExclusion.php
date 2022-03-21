<?php

/**
 * Since version 5.5 the excluded package version must be explicit set. If not, the installation of the plugin in denied.
 * For this reason, the (unused) format is rewritten from empty to `*`.
 *
 * see https://github.com/WoltLab/WCF/pull/4492
 *
 * @author Joshua Ruesweg
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core
 */

use wcf\system\WCF;

$sql = "UPDATE  wcf1_package_exclusion
        SET     excludedPackageVersion = ?
        WHERE   excludedPackageVersion = ?";
$statement = WCF::getDB()->prepare($sql);
$statement->execute([
    '*',
    '',
]);
