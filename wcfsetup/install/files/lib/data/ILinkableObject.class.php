<?php

namespace wcf\data;

/**
 * Every linkable object has to implement this interface.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface ILinkableObject
{
    /**
     * Returns the link to the object.
     */
    public function getLink(): string;
}
