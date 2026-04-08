<?php

namespace wcf\data\page;

use wcf\system\cache\builder\PageCacheBuilder;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Provides access to the page cache.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class PageCache extends SingletonFactory
{
    /**
     * @var array{
     *  identifier: array<string, int>,
     *  controller: array<string, int>,
     *  pages: array<int, Page>,
     *  pageTitles: array<int, array<int, string>>,
     *  landingPage: Page,
     *  pageMetaDescriptions: array<int, array<int, string>>,
     * }
     */
    protected $cache;

    #[\Override]
    protected function init()
    {
        $this->cache = PageCacheBuilder::getInstance()->getData();
    }

    /**
     * Returns all available pages.
     *
     * @return  Page[]
     */
    public function getPages()
    {
        return $this->cache['pages'];
    }

    /**
     * Returns a page by page id or null.
     */
    public function getPage(?int $pageID): ?Page
    {
        if ($pageID === null) {
            return null;
        }

        return $this->cache['pages'][$pageID] ?? null;
    }

    /**
     * Returns a page by controller or null.
     *
     * @return  Page|null
     */
    public function getPageByController(string $controller)
    {
        if (isset($this->cache['controller'][$controller])) {
            return $this->getPage($this->cache['controller'][$controller]);
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
        if (isset($this->cache['identifier'][$identifier])) {
            return $this->getPage($this->cache['identifier'][$identifier]);
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
        if (isset($this->cache['pageTitles'][$pageID])) {
            $page = $this->getPage($pageID);
            if ($page->isMultilingual || $page->pageType == 'system') {
                if ($languageID !== null && isset($this->cache['pageTitles'][$pageID][$languageID])) {
                    return $this->cache['pageTitles'][$pageID][$languageID];
                } elseif (isset($this->cache['pageTitles'][$pageID][WCF::getLanguage()->languageID])) {
                    return $this->cache['pageTitles'][$pageID][WCF::getLanguage()->languageID];
                }
            } else {
                return $this->cache['pageTitles'][$pageID][0];
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
        if (isset($this->cache['pageMetaDescriptions'][$pageID])) {
            $page = $this->getPage($pageID);
            if ($page->isMultilingual || $page->pageType == 'system') {
                if ($languageID !== null && isset($this->cache['pageMetaDescriptions'][$pageID][$languageID])) {
                    return $this->cache['pageMetaDescriptions'][$pageID][$languageID];
                } elseif (isset($this->cache['pageMetaDescriptions'][$pageID][WCF::getLanguage()->languageID])) {
                    return $this->cache['pageMetaDescriptions'][$pageID][WCF::getLanguage()->languageID];
                }
            } else {
                return $this->cache['pageMetaDescriptions'][$pageID][0];
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
        return $this->cache['landingPage'];
    }
}
