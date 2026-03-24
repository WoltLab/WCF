<?php

namespace wcf\system\tagging;

use wcf\data\DatabaseObjectList;
use wcf\data\tag\Tag;

/**
 * Abstract implementation of a taggable with support for searches with multiple tags.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.2
 * @deprecated  6.2 Use `AbstractTaggedListViewProvider` instead.
 *
 * @template T of DatabaseObjectList
 * @extends AbstractTaggable<T>
 * @implements ICombinedTaggable<T>
 */
abstract class AbstractCombinedTaggable extends AbstractTaggable implements ICombinedTaggable
{
    #[\Override]
    public function getObjectList(Tag $tag)
    {
        return $this->getObjectListFor([$tag]);
    }
}
