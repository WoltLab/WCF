<?php

namespace wcf\system\cache\builder;

/**
 * Caches a list of the most active members.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class MostActiveMembersCacheBuilder extends AbstractSortedUserCacheBuilder
{
    /**
     * @inheritDoc
     */
    protected $maxLifetime = 600;

    /**
     * @inheritDoc
     */
    protected $positiveValuesOnly = true;

    /**
     * @inheritDoc
     */
    protected $sortField = 'activityPoints';
}
