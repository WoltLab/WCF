<?php

namespace wcf\system\object\filter\page;

use wcf\data\DatabaseObject;
use wcf\data\user\User;
use wcf\system\form\builder\field\PagesFormField;
use wcf\system\object\filter\IObjectFilter;
use wcf\system\request\RequestHandler;
use wcf\system\WCF;

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
 * @implements IObjectFilter<User, list<int>>
 */
final class NotRequestedPageObjectFilter implements IObjectFilter
{
    #[\Override]
    public function getIdentifier(): string
    {
        return 'com.woltlab.wcf.notRequestedPage';
    }

    #[\Override]
    public function getTitle(): string
    {
        return WCF::getLanguage()->get('wcf.objectFilter.notRequestedPage');
    }

    #[\Override]
    public function getFormField(): PagesFormField
    {
        return PagesFormField::create('notRequestedPage')
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
        return WCF::getLanguage()->getDynamicVariable('wcf.objectFilter.notRequestedPage.summary', [
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

        return !\in_array($pageID, $configuredValue, true);
    }
}
