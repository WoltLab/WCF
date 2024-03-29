<?php

namespace wcf\data;

/**
 * Every categorized object has to implement this interface.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface ICategorizedObject
{
    /**
     * Returns the category this object belongs to.
     *
     * @return  \wcf\data\category\Category
     */
    public function getCategory();
}
