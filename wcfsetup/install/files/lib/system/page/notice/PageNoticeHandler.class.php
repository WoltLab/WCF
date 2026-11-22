<?php

namespace wcf\system\page\notice;

use wcf\action\PageNoticeAddAction;
use wcf\data\page\notice\PageNotice;
use wcf\data\page\notice\PageNoticeList;
use wcf\data\page\Page;
use wcf\system\cache\eager\PageNoticeLocationCache;
use wcf\system\interaction\StandaloneInteractionContextMenuComponent;
use wcf\system\interaction\user\PageNoticeInteractions;
use wcf\system\request\LinkHandler;
use wcf\system\request\RequestHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Provides the page notices of the active page and renders them.
 *
 * @author      Matthias Schmidt, Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
final class PageNoticeHandler extends SingletonFactory
{
    private ?Page $page = null;

    private ?int $pageObjectID = null;

    #[\Override]
    protected function init(): void
    {
        $page = RequestHandler::getInstance()->getActivePage();
        if ($page === null) {
            return;
        }

        if ($page->requireObjectID !== 0) {
            // This matches the object id of the page location, see `PageLocationManager::init()`.
            $pageObjectID = (int)($_REQUEST['id'] ?? 0);
            if ($pageObjectID <= 0) {
                return;
            }

            $this->pageObjectID = $pageObjectID;
        }

        $this->page = $page;
    }

    /**
     * Returns true if the active user can add notices to the active page.
     */
    public function canAddNotice(): bool
    {
        if ($this->page === null || RequestHandler::getInstance()->isACPRequest()) {
            return false;
        }

        return PageNotice::canManage($this->page, $this->pageObjectID);
    }

    /**
     * Returns the link to the dialog that adds a notice to the active page.
     */
    public function getAddLink(): string
    {
        if ($this->page === null) {
            throw new \BadMethodCallException("There is no active page.");
        }

        $parameters = ['pageID' => $this->page->pageID];
        if ($this->pageObjectID !== null) {
            $parameters['pageObjectID'] = $this->pageObjectID;
        }

        return LinkHandler::getInstance()->getControllerLink(PageNoticeAddAction::class, $parameters);
    }

    /**
     * Returns the rendered notices of the active page.
     */
    public function render(): string
    {
        if ($this->page === null || RequestHandler::getInstance()->isACPRequest()) {
            return '';
        }

        return $this->renderNotices($this->page, $this->pageObjectID);
    }

    /**
     * Returns the id of the active page or `null` if notices are not supported
     * on the active page.
     */
    public function getPageID(): ?int
    {
        return $this->page?->pageID;
    }

    /**
     * Returns the object id of the active page or `null` if the active page
     * does not require an object.
     */
    public function getPageObjectID(): ?int
    {
        return $this->pageObjectID;
    }

    /**
     * Returns the rendered notices of the given page and object that are
     * visible to the active user.
     */
    public function renderNotices(Page $page, ?int $pageObjectID): string
    {
        $pageNotices = $this->getVisibleNotices($page, $pageObjectID);
        if ($pageNotices === []) {
            return '';
        }

        $provider = new PageNoticeInteractions();
        $contextMenus = [];
        foreach ($pageNotices as $pageNotice) {
            $contextMenus[$pageNotice->pageNoticeID] = new StandaloneInteractionContextMenuComponent(
                $provider,
                $pageNotice,
                // Unused, `Component/PageNotice/Setup.ts` intercepts the removal of notices.
                ''
            );
        }

        return WCF::getTPL()->render('wcf', 'pageNoticeList', [
            'pageNotices' => $pageNotices,
            'pageNoticeContextMenus' => $contextMenus,
        ]);
    }

    /**
     * Returns the notices of the given page and object that are visible to the
     * active user.
     *
     * @return list<PageNotice>
     */
    public function getVisibleNotices(Page $page, ?int $pageObjectID): array
    {
        $locations = new PageNoticeLocationCache()->getCache();
        $hasPublicNotices = $locations[$page->pageID][$pageObjectID ?? 0] ?? null;
        if ($hasPublicNotices === null) {
            return [];
        }

        $canManage = PageNotice::hasManagePermission();
        if (!$hasPublicNotices && !$canManage) {
            return [];
        }

        if (!PageNotice::canAccessPage($page, $pageObjectID)) {
            return [];
        }

        $list = new PageNoticeList();
        $list->getConditionBuilder()->add('page_notice.pageID = ?', [$page->pageID]);
        if ($pageObjectID === null) {
            $list->getConditionBuilder()->add('page_notice.pageObjectID IS NULL');
        } else {
            $list->getConditionBuilder()->add('page_notice.pageObjectID = ?', [$pageObjectID]);
        }
        if (!$canManage) {
            $list->getConditionBuilder()->add('page_notice.isInternal = ?', [0]);
        }
        $list->readObjects();

        return \array_values($list->getObjects());
    }
}
