<?php

namespace wcf\data;

/**
 * Every titled object has to implement this interface.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface ITitledObject
{
    /**
     * Returns the title of the object.
     */
    public function getTitle(): string;
}
