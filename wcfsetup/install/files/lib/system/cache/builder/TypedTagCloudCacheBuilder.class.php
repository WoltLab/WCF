<?php

namespace wcf\system\cache\builder;

/**
 * Caches the typed tag cloud.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class TypedTagCloudCacheBuilder extends TagCloudCacheBuilder
{
    /**
     * @inheritDoc
     */
    protected function rebuild(array $parameters)
    {
        $this->objectTypeIDs = $parameters['objectTypeIDs'];
        $this->languageIDs = $parameters['languageIDs'];

        // get tags
        $this->getTags();

        return $this->tags;
    }
}
