<?php

namespace wcf\system\importer;

use wcf\data\object\type\ObjectTypeCache;

/**
 * Represents a trophy category importer.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class TrophyCategoryImporter extends AbstractCategoryImporter
{
    /**
     * @inheritDoc
     */
    protected $objectTypeName = 'com.woltlab.wcf.trophy.category';

    public function __construct()
    {
        $objectType = ObjectTypeCache::getInstance()
            ->getObjectTypeByName('com.woltlab.wcf.category', $this->objectTypeName);
        $this->objectTypeID = $objectType->objectTypeID;
    }
}
