<?php

namespace wcf\system\importer;

use wcf\data\object\type\ObjectTypeCache;

/**
 * Imports media categories.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class MediaCategoryImporter extends AbstractCategoryImporter
{
    /**
     * @inheritDoc
     */
    protected $objectTypeName = 'com.woltlab.wcf.media.category';

    /**
     * Creates a new `MediaCategoryImporter` object.
     */
    public function __construct()
    {
        $this->objectTypeID = ObjectTypeCache::getInstance()->getObjectTypeByName(
            'com.woltlab.wcf.category',
            'com.woltlab.wcf.media.category'
        )->objectTypeID;
    }
}
