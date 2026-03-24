<?php

namespace wcf\system\tagging;

use wcf\data\DatabaseObjectList;
use wcf\data\object\type\AbstractObjectTypeProcessor;

/**
 * Abstract implementation of a taggable.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated  6.2 Use `AbstractTaggedListViewProvider` instead.
 *
 * @template T of DatabaseObjectList
 * @implements ITaggable<T>
 */
abstract class AbstractTaggable extends AbstractObjectTypeProcessor implements ITaggable
{
    #[\Override]
    public function getApplication()
    {
        $classParts = \explode('\\', static::class);

        return $classParts[0];
    }
}
