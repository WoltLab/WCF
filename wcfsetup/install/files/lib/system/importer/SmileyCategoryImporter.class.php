<?php

namespace wcf\system\importer;

use wcf\data\object\type\ObjectTypeCache;

/**
 * Imports smiley categories.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class SmileyCategoryImporter extends AbstractCategoryImporter
{
    /**
     * @inheritDoc
     */
    protected $objectTypeName = 'com.woltlab.wcf.smiley.category';

    /**
     * Creates a new SmileyCategoryImporter object.
     */
    public function __construct()
    {
        $objectType = ObjectTypeCache::getInstance()
            ->getObjectTypeByName('com.woltlab.wcf.category', 'com.woltlab.wcf.bbcode.smiley');
        $this->objectTypeID = $objectType->objectTypeID;
    }
}
