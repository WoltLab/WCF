<?php

namespace wcf\data;

use wcf\system\WCF;

/**
 * Adds a fastCreate() method that differs from create() by returning the ID only.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.4
 */
trait TFastCreate
{
    /**
     * Creates the object and returns the ID.
     *
     * @see IEditableObject::create()
     * @return int|string
     */
    public static function fastCreate(array $parameters)
    {
        $keys = $values = '';
        $statementParameters = [];
        foreach ($parameters as $key => $value) {
            if (!empty($keys)) {
                $keys .= ',';
                $values .= ',';
            }

            $keys .= $key;
            $values .= '?';
            $statementParameters[] = $value;
        }

        // save object
        $sql = "INSERT INTO " . static::getDatabaseTableName() . "
                            (" . $keys . ")
                VALUES      (" . $values . ")";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute($statementParameters);

        // return new object
        if (static::getDatabaseTableIndexIsIdentity()) {
            $id = WCF::getDB()->getInsertID(static::getDatabaseTableName(), static::getDatabaseTableIndexName());
        } else {
            $id = $parameters[static::getDatabaseTableIndexName()];
        }

        return $id;
    }
}
