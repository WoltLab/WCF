<?php

namespace wcf\data;

/**
 * Every database object action whose objects can be deleted (via AJAX) has to
 * implement this interface.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface IDeleteAction
{
    /**
     * Deletes the relevant objects and returns the number of deleted objects.
     *
     * @return  int
     */
    public function delete();

    /**
     * Validates the "delete" action.
     */
    public function validateDelete();
}
