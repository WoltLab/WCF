<?php

namespace wcf\system\object\filter\page;

use wcf\data\DatabaseObject;
use wcf\data\page\PageCache;
use wcf\data\user\User;
use wcf\system\form\builder\field\PagesFormField;
use wcf\system\object\filter\AbstractMultipleSelectionObjectFilter;
use wcf\system\request\RequestHandler;

/**
 * Filters by whether the requested page is one of the pages with the given ids.
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
final class RequestedPageObjectFilter extends AbstractMultipleSelectionObjectFilter
{
    #[\Override]
    public function getIdentifier(): string
    {
        return 'com.woltlab.wcf.requestedPage';
    }

    #[\Override]
    protected function getLanguageItem(): string
    {
        return 'wcf.objectFilter.requestedPage';
    }

    #[\Override]
    protected function getLabels(array $objectIDs): array
    {
        return RequestedPageObjectFilter::getPageTitles($objectIDs);
    }

    #[\Override]
    public function getFormField(): PagesFormField
    {
        return PagesFormField::create('requestedPage')
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

        return \in_array($pageID, $configuredValue, true);
    }

    /**
     * Returns the titles of the pages with the given ids, unknown pages are skipped.
     *
     * @param list<int> $pageIDs
     * @return list<string>
     * @internal
     */
    public static function getPageTitles(array $pageIDs): array
    {
        $titles = [];
        foreach ($pageIDs as $pageID) {
            $page = PageCache::getInstance()->getPage($pageID);
            if ($page !== null) {
                $titles[] = $page->getTitle();
            }
        }

        return $titles;
    }
}
