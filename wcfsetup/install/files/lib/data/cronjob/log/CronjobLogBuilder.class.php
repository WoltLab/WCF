<?php

namespace wcf\data\cronjob\log;

use wcf\data\cronjob\Cronjob;
use wcf\data\DatabaseObjectBuilder;
use wcf\system\WCF;

/**
 * Builder for creating, updating and deleting cronjob logs.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends DatabaseObjectBuilder<CronjobLog>
 */
final class CronjobLogBuilder extends DatabaseObjectBuilder
{
    /**
     * Sets the cronjob the log entry belongs to.
     */
    public function setCronjob(Cronjob $cronjob): static
    {
        return $this->setCronjobID($cronjob->cronjobID);
    }

    /**
     * Sets the id of the cronjob the log entry belongs to.
     */
    public function setCronjobID(int $cronjobID): static
    {
        $this->properties['cronjobID'] = $cronjobID;

        return $this;
    }

    /**
     * Sets the timestamp at which the cronjob has been executed.
     */
    public function setExecTime(int $execTime): static
    {
        $this->properties['execTime'] = $execTime;

        return $this;
    }

    /**
     * Sets whether the cronjob has been executed successfully.
     */
    public function setSuccess(bool $success): static
    {
        $this->properties['success'] = $success ? 1 : 0;

        return $this;
    }

    /**
     * Sets the error message of a cronjob that did not execute successfully.
     */
    public function setError(?string $error): static
    {
        $this->properties['error'] = $error !== null ? \mb_substr($error, 0, 65000) : null;

        return $this;
    }

    #[\Override]
    protected function getRequiredProperties(): array
    {
        return ['cronjobID', 'execTime'];
    }

    /**
     * Deletes all cronjob logs.
     */
    public static function clearAll(): void
    {
        $sql = "DELETE FROM " . CronjobLog::getDatabaseTableName();
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
    }
}
