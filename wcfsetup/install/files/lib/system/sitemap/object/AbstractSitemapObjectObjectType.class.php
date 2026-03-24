<?php

namespace wcf\system\sitemap\object;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectList;

/**
 * Abstract implementation of a sitemap object.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.1
 *
 * @template TDatabaseObject of DatabaseObject
 * @template TDatabaseObjectList of DatabaseObjectList
 * @implements ISitemapObjectObjectType<TDatabaseObject, TDatabaseObjectList>
 */
abstract class AbstractSitemapObjectObjectType implements ISitemapObjectObjectType
{
    #[\Override]
    public function getObjectListClass()
    {
        return $this->getObjectClass() . 'List';
    }

    #[\Override]
    public function getObjectList()
    {
        $className = $this->getObjectListClass();

        return new $className();
    }

    #[\Override]
    public function getLastModifiedColumn()
    {
        return null;
    }

    #[\Override]
    public function canView(DatabaseObject $object)
    {
        return true;
    }

    #[\Override]
    public function isAvailableType()
    {
        return true;
    }
}
