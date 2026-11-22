<?php

namespace wcf\data\page\notice;

use wcf\data\CollectionDatabaseObject;
use wcf\data\page\Page;
use wcf\data\page\PageCache;
use wcf\data\user\UserProfile;
use wcf\system\html\output\HtmlOutputProcessor;
use wcf\system\l10n\L10nDefinition;
use wcf\system\l10n\L10nStorage;
use wcf\system\page\handler\ILookupPageHandler;
use wcf\system\WCF;

/**
 * Represents a notice that is shown on a specific page, optionally limited to
 * a specific object of that page.
 *
 * The localized message is stored in the `wcf1_page_notice_l10n` table.
 *
 * @author      Matthias Schmidt, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @property-read   int     $pageNoticeID       unique id of the page notice
 * @property-read   int     $pageID             id of the page the notice is shown on
 * @property-read   ?int    $pageObjectID       id of the object of the page the notice is shown on or `null` if the page does not require an object
 * @property-read   ?int    $userID             id of the user who created the notice or `null` if the user does not exist anymore
 * @property-read   string  $username           name of the user who created the notice
 * @property-read   int     $time               timestamp at which the notice has been created
 * @property-read   string  $type               type of the notice, one of `PageNotice::TYPES`
 * @property-read   0|1     $isInternal         is `1` if the notice is only visible to users who can manage page notices, otherwise `0`
 * @property-read   0|1     $hasEmbeddedObjects is `1` if the message contains embedded objects, otherwise `0`
 *
 * @extends CollectionDatabaseObject<PageNoticeCollection>
 *
 * @phpstan-import-type L10nValue from L10nStorage
 */
final class PageNotice extends CollectionDatabaseObject
{
    /**
     * @inheritDoc
     */
    protected static $databaseTableIndexName = 'pageNoticeID';

    public const TYPES = [
        'info',
        'success',
        'warning',
        'error',
    ];

    public const MESSAGE_OBJECT_TYPE = 'com.woltlab.wcf.pageNotice';

    /**
     * Returns the formatted message in the active language.
     */
    public function getFormattedMessage(): string
    {
        $this->loadEmbeddedObjects();

        $processor = new HtmlOutputProcessor();
        $processor->process($this->getMessage(), self::MESSAGE_OBJECT_TYPE, $this->pageNoticeID);

        return $processor->getHtml();
    }

    /**
     * Returns the message in the active language.
     */
    public function getMessage(): string
    {
        return $this->getCollection()->getResolvedL10nValue($this, 'message');
    }

    /**
     * Returns the localized values of the message as a `languageID => value`
     * map (see `L10nStorage`).
     *
     * @return L10nValue
     */
    public function getL10nMessage(): array
    {
        return $this->getCollection()->getL10nValues($this, 'message');
    }

    /**
     * Returns the page the notice is shown on.
     */
    public function getPage(): Page
    {
        $page = PageCache::getInstance()->getPage($this->pageID);
        \assert($page !== null);

        return $page;
    }

    /**
     * Returns the user profile of the user who created the notice.
     */
    public function getUserProfile(): UserProfile
    {
        return $this->getCollection()->getUserProfile($this);
    }

    /**
     * Returns true if the active user can see this notice.
     */
    public function isVisible(): bool
    {
        if (!self::canAccessPage($this->getPage(), $this->pageObjectID)) {
            return false;
        }

        return $this->isInternal === 0 || self::hasManagePermission();
    }

    /**
     * Returns true if the active user can edit and delete this notice.
     */
    public function canEdit(): bool
    {
        return self::canManage($this->getPage(), $this->pageObjectID);
    }

    /**
     * Returns true if the active user can access the given page and object.
     */
    public static function canAccessPage(Page $page, ?int $pageObjectID): bool
    {
        if (!$page->isVisible() || !$page->isAccessible()) {
            return false;
        }

        if ($page->requireObjectID !== 0 && $pageObjectID === null) {
            return false;
        }

        $handler = $page->getHandler();
        if ($handler === null) {
            return true;
        }

        if ($pageObjectID !== null && $handler instanceof ILookupPageHandler && !$handler->isValid($pageObjectID)) {
            return false;
        }

        return $handler->isVisible($pageObjectID);
    }

    /**
     * Returns true if the active user can add, edit and delete notices of the
     * given page and object.
     */
    public static function canManage(Page $page, ?int $pageObjectID): bool
    {
        return self::hasManagePermission() && self::canAccessPage($page, $pageObjectID);
    }

    /**
     * Returns true if the active user has the permission to manage notices and
     * to see internal notices.
     */
    public static function hasManagePermission(): bool
    {
        return WCF::getSession()->hasPermission('mod.general.canManagePageNotice');
    }

    public static function getL10nDefinition(): L10nDefinition
    {
        return new L10nDefinition(
            'wcf1_page_notice',
            'wcf1_page_notice_l10n',
            'pageNoticeID',
            ['message'],
        );
    }

    private function loadEmbeddedObjects(): void
    {
        $this->getCollection()->loadEmbeddedObjects(self::MESSAGE_OBJECT_TYPE);
    }
}
