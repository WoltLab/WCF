<?php

namespace wcf\system\database\table;

/**
 * Provides methods for database components which can be dropped.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
trait TDroppableDatabaseComponent
{
    /**
     * is `true` if the component will be dropped
     */
    protected bool $drop = false;

    /**
     * Marks the component to be dropped.
     *
     * @return  $this
     */
    public function drop(): static
    {
        $this->drop = true;

        return $this;
    }

    /**
     * Returns `true` if the component will be dropped.
     */
    public function willBeDropped(): bool
    {
        return $this->drop;
    }
}
