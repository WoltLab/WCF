<?php

namespace wcf\system\moderation;

use wcf\system\listView\AbstractListView;

/**
 * Interface for deleted content provider using list views.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 *
 * @template T of AbstractListView
 */
interface IDeletedContentListViewProvider
{
    /**
     * @return T
     */
    public function getListView(): AbstractListView;

    public function getIdentifier(): string;

    public function getObjectTypeTitle(): string;

    public function getContentTitle(): string;

    public function getContainerCssClassName(): string;
}
