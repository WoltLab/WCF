<?php

namespace wcf\system\cache\builder;

use wcf\data\bbcode\media\provider\BBCodeMediaProviderList;

/**
 * Caches the BBCode media providers.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Cache\Builder
 */
class BBCodeMediaProviderCacheBuilder extends AbstractCacheBuilder
{
    /**
     * @inheritDoc
     */
    protected function rebuild(array $parameters)
    {
        $providerList = new BBCodeMediaProviderList();
        $providerList->getConditionBuilder()->add('isDisabled = ?', [0]);
        $providerList->readObjects();

        return $providerList->getObjects();
    }
}
