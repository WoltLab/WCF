<?php

namespace wcf\system\moderation;

use wcf\system\listView\AbstractListView;
use wcf\system\WCF;

/**
 * Abstract implementation of a deleted content provider using list views.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 *
 * @template TListView of AbstractListView
 * @implements IDeletedContentListViewProvider<TListView>
 */
abstract class AbstractDeletedContentListViewProvider implements IDeletedContentListViewProvider
{
    public function __construct(
        protected readonly string $identifier,
        protected readonly string $objectTypeTitle,
        protected readonly string $contentTitle
    ) {}

    #[\Override]
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    #[\Override]
    public function getObjectTypeTitle(): string
    {
        return WCF::getLanguage()->getDynamicVariable($this->objectTypeTitle);
    }

    #[\Override]
    public function getContentTitle(): string
    {
        return WCF::getLanguage()->getDynamicVariable($this->contentTitle);
    }
}
