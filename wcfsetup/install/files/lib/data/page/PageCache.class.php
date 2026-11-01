<?php

namespace wcf\data\page;

use wcf\system\cache\eager\PageRowCache;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Provides access to the page cache.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @phpstan-import-type PageRow from PageRowCache
 */
final class PageCache extends SingletonFactory
{
    /**
     * database rows of the pages
     * @var array<int, PageRow>
     */
    private array $pageRows = [];

    /**
     * page ids by identifier
     * @var array<string, int>
     */
    private array $pageIDsByIdentifier = [];

    /**
     * page ids by controller
     * @var array<string, int>
     */
    private array $pageIDsByController = [];

    /**
     * localized page titles by page id and language id
     * @var array<int, array<int, string>>
     */
    private array $pageTitles = [];

    /**
     * localized page meta descriptions by page id and language id
     * @var array<int, array<int, string>>
     */
    private array $pageMetaDescriptions = [];

    private ?int $landingPageID = null;

    /**
     * pages created so far
     * @var array<int, Page>
     */
    private array $pages = [];

    #[\Override]
    protected function init()
    {
        $data = new PageRowCache()->getCache();

        $this->pageRows = $data['pages'];
        $this->pageIDsByIdentifier = $data['identifier'];
        $this->pageIDsByController = $data['controller'];
        $this->pageTitles = $data['pageTitles'];
        $this->pageMetaDescriptions = $data['pageMetaDescriptions'];
        $this->landingPageID = $data['landingPageID'];

        // `TTestablePageUserNotificationEvent` re-runs this to reload the cache within the request.
        $this->pages = [];
    }

    /**
     * Returns all available pages.
     *
     * @return  Page[]
     */
    public function getPages()
    {
        $pages = [];
        foreach (\array_keys($this->pageRows) as $pageID) {
            $pages[$pageID] = $this->getPage($pageID);
        }

        return $pages;
    }

    /**
     * Returns a page by page id or null.
     */
    public function getPage(?int $pageID): ?Page
    {
        if ($pageID === null) {
            return null;
        }

        if (!isset($this->pages[$pageID])) {
            if (!isset($this->pageRows[$pageID])) {
                return null;
            }

            $this->pages[$pageID] = new Page(null, $this->pageRows[$pageID]);
        }

        return $this->pages[$pageID];
    }

    /**
     * Returns a page by controller or null.
     *
     * @return  Page|null
     */
    public function getPageByController(string $controller)
    {
        if (isset($this->pageIDsByController[$controller])) {
            return $this->getPage($this->pageIDsByController[$controller]);
        }

        return null;
    }

    /**
     * Returns a page by its internal identifier or null.
     *
     * @return  Page|null
     */
    public function getPageByIdentifier(string $identifier)
    {
        if (isset($this->pageIDsByIdentifier[$identifier])) {
            return $this->getPage($this->pageIDsByIdentifier[$identifier]);
        }

        return null;
    }

    /**
     * Returns the localized page title by page id, optionally retrieving the title
     * for given language id if it is a multilingual page.
     *
     * @return  string  localized page title
     */
    public function getPageTitle(int $pageID, ?int $languageID = null)
    {
        if (isset($this->pageTitles[$pageID])) {
            $page = $this->getPage($pageID);
            if ($page->isMultilingual !== 0 || $page->pageType === 'system') {
                if ($languageID !== null && isset($this->pageTitles[$pageID][$languageID])) {
                    return $this->pageTitles[$pageID][$languageID];
                } elseif (isset($this->pageTitles[$pageID][WCF::getLanguage()->languageID])) {
                    return $this->pageTitles[$pageID][WCF::getLanguage()->languageID];
                }
            } else {
                return $this->pageTitles[$pageID][0];
            }
        }

        return '';
    }

    /**
     * Returns the localized page meta description by page id, optionally retrieving the description
     * for given language id if it is a multilingual page (or a system page).
     *
     * @since 5.4
     */
    public function getPageMetaDescription(int $pageID, ?int $languageID = null): string
    {
        if (isset($this->pageMetaDescriptions[$pageID])) {
            $page = $this->getPage($pageID);
            if ($page->isMultilingual !== 0 || $page->pageType === 'system') {
                if ($languageID !== null && isset($this->pageMetaDescriptions[$pageID][$languageID])) {
                    return $this->pageMetaDescriptions[$pageID][$languageID];
                } elseif (isset($this->pageMetaDescriptions[$pageID][WCF::getLanguage()->languageID])) {
                    return $this->pageMetaDescriptions[$pageID][WCF::getLanguage()->languageID];
                }
            } else {
                return $this->pageMetaDescriptions[$pageID][0];
            }
        }

        return '';
    }

    /**
     * Returns the global landing page.
     *
     * @return  Page
     */
    public function getLandingPage()
    {
        return $this->getPage($this->landingPageID);
    }
}
