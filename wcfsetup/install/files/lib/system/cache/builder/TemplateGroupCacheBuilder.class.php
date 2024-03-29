<?php

namespace wcf\system\cache\builder;

use wcf\data\template\group\TemplateGroupList;

/**
 * Caches template groups.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class TemplateGroupCacheBuilder extends AbstractCacheBuilder
{
    /**
     * @inheritDoc
     */
    public function rebuild(array $parameters)
    {
        $templateGroupList = new TemplateGroupList();
        $templateGroupList->readObjects();

        return $templateGroupList->getObjects();
    }
}
