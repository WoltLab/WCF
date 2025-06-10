<?php

namespace wcf\system\cache\builder;

use wcf\system\cache\eager\UserGroupAssignmentCache;

/**
 * Caches the enabled automatic user group assignments.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @deprecated 6.2 use `UserGroupAssignmentCache` instead
 */
final class UserGroupAssignmentCacheBuilder extends AbstractLegacyCacheBuilder
{
    #[\Override]
    protected function rebuild(array $parameters): array
    {
        return (new UserGroupAssignmentCache())->getCache();
    }

    #[\Override]
    public function reset(array $parameters = [])
    {
        (new UserGroupAssignmentCache())->rebuild();
    }
}
