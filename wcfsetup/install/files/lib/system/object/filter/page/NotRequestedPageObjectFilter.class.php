<?php

namespace wcf\system\object\filter\page;

use wcf\data\DatabaseObject;
use wcf\data\user\User;
use wcf\system\form\builder\field\PagesFormField;
use wcf\system\object\filter\AbstractMultipleSelectionObjectFilter;
use wcf\system\request\RequestHandler;

/**
 * Filters by whether the requested page is none of the pages with the given ids.
 * Requests without an active page never match.
 *
 * The given user is ignored, therefore this filter is only meaningful when
 * testing the active user.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @extends AbstractMultipleSelectionObjectFilter<User>
 */
final class NotRequestedPageObjectFilter extends AbstractMultipleSelectionObjectFilter
{
    #[\Override]
    public function getIdentifier(): string
    {
        return 'com.woltlab.wcf.notRequestedPage';
    }

    #[\Override]
    protected function getLanguageItem(): string
    {
        return 'wcf.objectFilter.notRequestedPage';
    }

    #[\Override]
    protected function getLabels(array $objectIDs): array
    {
        return RequestedPageObjectFilter::getPageTitles($objectIDs);
    }

    #[\Override]
    public function getFormField(): PagesFormField
    {
        return PagesFormField::create('notRequestedPage')
            ->label('wcf.page.requestedPage')
            ->required();
    }

    #[\Override]
    public function testObject(DatabaseObject $object, mixed $configuredValue): bool
    {
        $pageID = RequestHandler::getInstance()->getActivePageID();
        if ($pageID === null) {
            return false;
        }

        return !\in_array($pageID, $configuredValue, true);
    }
}
