<?php

namespace wcf\data;

/**
 * Provides a method to access the unique id of an object.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
interface IIDObject
{
    /**
     * Returns the unique id of the object.
     *
     * @return  int
     */
    public function getObjectID();
}
