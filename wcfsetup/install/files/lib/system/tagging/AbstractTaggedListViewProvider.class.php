<?php

namespace wcf\system\tagging;

use wcf\data\object\type\AbstractObjectTypeProcessor;
use wcf\system\listView\AbstractListView;
use wcf\system\WCF;

/**
 * Abstract implementation of a list view providers containing tagged items.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 *
 * @template T of AbstractListView
 * @implements ITaggedListViewProvider<AbstractListView>
 */
abstract class AbstractTaggedListViewProvider extends AbstractObjectTypeProcessor implements ITaggedListViewProvider
{
    #[\Override]
    public function getObjectTypeTitle(): string
    {
        return WCF::getLanguage()->getDynamicVariable('wcf.tagging.objectType.' . $this->getDecoratedObject()->objectType);
    }

    #[\Override]
    public function getContentTitle(): string
    {
        return WCF::getLanguage()->getDynamicVariable('wcf.tagging.combinedTaggedObjects.' . $this->getDecoratedObject()->objectType);
    }
}
