<?php

namespace wcf\system\cache\builder;

use wcf\data\clipboard\action\ClipboardActionList;

/**
 * Caches clipboard actions.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class ClipboardActionCacheBuilder extends AbstractCacheBuilder
{
    #[\Override]
    public function rebuild(array $parameters)
    {
        $actionList = new ClipboardActionList();
        $actionList->readObjects();

        return $actionList->getObjects();
    }
}
