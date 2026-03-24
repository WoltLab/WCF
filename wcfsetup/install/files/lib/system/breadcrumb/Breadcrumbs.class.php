<?php

namespace wcf\system\breadcrumb;

use wcf\data\page\PageCache;
use wcf\system\page\PageLocationManager;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Manages breadcrumbs.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @implements \Iterator<int, Breadcrumb>
 */
final class Breadcrumbs extends SingletonFactory implements \Countable, \Iterator
{
    /**
     * list of breadcrumbs
     * @var Breadcrumb[]
     */
    private array $items;

    /**
     * Current iterator-index
     */
    private int $index = 0;

    /**
     * Returns the list of breadcrumbs.
     *
     * @return  Breadcrumb[]
     */
    public function get(): array
    {
        if (!isset($this->items)) {
            $this->loadBreadcrumbs();
        }

        return $this->items;
    }

    /**
     * Loads the list of breadcrumbs.
     */
    private function loadBreadcrumbs(): void
    {
        $this->items = [];
        $locations = PageLocationManager::getInstance()->getLocations();

        // locations are provided starting with the current location followed
        // by all relevant ancestors, but breadcrumbs appear in the reverse order
        $locations = \array_reverse($locations);

        // add the landing page as first location, unless it is already included
        $landingPage = PageCache::getInstance()->getLandingPage();
        $addLandingPage = true;
        for ($i = 0, $length = \count($locations); $i < $length; $i++) {
            if ($locations[$i]['pageID'] == $landingPage->pageID) {
                $addLandingPage = false;
                break;
            }
        }

        if ($addLandingPage) {
            \array_unshift($locations, [
                'link' => $landingPage->getLink(),
                'title' => BREADCRUMBS_HOME_USE_PAGE_TITLE ? WCF::getLanguage()->get(PAGE_TITLE) : $landingPage->getTitle(),
            ]);
        }

        // ignore the last location as it represents the current page
        \array_pop($locations);

        for ($i = 0, $length = \count($locations); $i < $length; $i++) {
            $this->items[] = new Breadcrumb($locations[$i]['title'], $locations[$i]['link']);
        }
    }

    #[\Override]
    public function count(): int
    {
        if (!isset($this->items)) {
            $this->loadBreadcrumbs();
        }

        return \count($this->items);
    }

    #[\Override]
    public function current(): Breadcrumb
    {
        return $this->items[$this->index];
    }

    #[\Override]
    public function key(): int
    {
        return $this->index;
    }

    #[\Override]
    public function valid(): bool
    {
        return isset($this->items[$this->index]);
    }

    #[\Override]
    public function rewind(): void
    {
        $this->index = 0;
    }

    #[\Override]
    public function next(): void
    {
        $this->index++;
    }
}
