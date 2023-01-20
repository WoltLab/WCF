<?php

namespace wcf\system\package;

use wcf\system\WCF;

/**
 * Manages audit logging (wcf1_package_audit_log) for the package system.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   6.0
 */
final class AuditLogger
{
    /**
     * Logs the given $payload with the current time.
     */
    public function log(string $payload): void
    {
        $time = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));

        $sql = "INSERT INTO wcf1_package_audit_log
                            (time, wcfVersion, payload)
                VALUES      (?, ?, ?)";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            $time->format('Y-m-d\TH:i:s.uP'),
            \WCF_VERSION,
            $payload,
        ]);
    }
}
