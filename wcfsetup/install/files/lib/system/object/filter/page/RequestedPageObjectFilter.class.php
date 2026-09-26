<?php

namespace wcf\system\object\filter\page;

use wcf\data\DatabaseObject;
use wcf\data\page\PageCache;
use wcf\data\user\User;
use wcf\system\form\builder\field\PagesFormField;
use wcf\system\object\filter\IObjectFilter;
use wcf\system\request\RequestHandler;
use wcf\system\WCF;

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
 * @implements IObjectFilter<User, list<int>>
 */
final class RequestedPageObjectFilter implements IObjectFilter
{
    #[\Override]
    public function getIdentifier(): string
    {
        return 'com.woltlab.wcf.requestedPage';
    }

    #[\Override]
    public function getTitle(): string
    {
        return WCF::getLanguage()->get('wcf.objectFilter.requestedPage');
    }

    #[\Override]
    public function getFormField(): PagesFormField
    {
        return PagesFormField::create('requestedPage')
            ->label('wcf.page.requestedPage')
            ->required();
    }

    /**
     * @param list<int|string> $value
     */
    #[\Override]
    public function serializeValue(mixed $value): string
    {
        return \implode(',', \array_map(static fn($pageID) => (int)$pageID, $value));
    }

    #[\Override]
    public function unserializeValue(string $serializedValue): array
    {
        return \array_map(static fn($pageID) => (int)$pageID, \explode(',', $serializedValue));
    }

    /**
     * @return list<int>
     */
    #[\Override]
    public function toFormFieldValue(mixed $value): array
    {
        return $value;
    }

    #[\Override]
    public function summarizeValue(mixed $value): string
    {
        return WCF::getLanguage()->getDynamicVariable('wcf.objectFilter.requestedPage.summary', [
            'pages' => RequestedPageObjectFilter::getPageTitles($value),
        ]);
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
     * Returns the comma-separated titles of the pages with the given ids.
     *
     * @param list<int> $pageIDs
     * @internal
     */
    public static function getPageTitles(array $pageIDs): string
    {
        $titles = [];
        foreach ($pageIDs as $pageID) {
            $page = PageCache::getInstance()->getPage($pageID);
            if ($page !== null) {
                $titles[] = $page->getTitle();
            }
        }

        return \implode(', ', $titles);
    }
}
