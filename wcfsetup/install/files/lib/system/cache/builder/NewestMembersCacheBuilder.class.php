<?php

namespace wcf\system\cache\builder;

/**
 * Caches a list of the newest members.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class NewestMembersCacheBuilder extends AbstractSortedUserCacheBuilder
{
    /**
     * @inheritDoc
     */
    protected $sortField = 'registrationDate';
}
