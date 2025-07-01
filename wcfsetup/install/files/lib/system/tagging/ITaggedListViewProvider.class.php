<?php

namespace wcf\system\tagging;

use wcf\system\listView\AbstractListView;

/**
 * Interface for providers of list views containing tagged items.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 *
 * @template T of AbstractListView
 */
interface ITaggedListViewProvider
{
    /**
     * Returns a list view that match all provided tags.
     *
     * @param int[] $tagIDs
     * @return T
     */
    public function getListView(array $tagIDs): AbstractListView;

    public function getObjectTypeTitle(): string;

    public function getContentTitle(): string;
}
