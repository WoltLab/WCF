<?php

namespace wcf\system\importer;

/**
 * Basic interface for all importer.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface IImporter
{
    /**
     * Imports a data set.
     *
     * @param mixed[] $data
     * @param mixed[] $additionalData
     * @return  mixed       new id
     */
    public function import(mixed $oldID, array $data, array $additionalData = []);

    /**
     * Returns database object class name.
     *
     * @return  string
     */
    public function getClassName();
}
