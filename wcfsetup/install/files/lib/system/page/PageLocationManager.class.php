<?php

namespace wcf\system\page;

use wcf\data\ITitledLinkObject;
use wcf\data\page\PageCache;
use wcf\system\exception\SystemException;
use wcf\system\request\RequestHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles page locations for use with menu active markings.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
class PageLocationManager extends SingletonFactory
{
    /**
     * true if all parents of the highest page have been added
     * @var bool
     */
    protected $addedParentLocations = false;

    /**
     * list of locations with descending priority
     * @var array
     */
    protected $stack = [];

    /**
     * @inheritDoc
     */
    public function init()
    {
        $pageID = $pageObjectID = 0;

        if (\str_starts_with(\WCF_VERSION, '6.0')) {
            // `RequestHandler::getActivePage()` was added in 6.1, but is being
            // indirectly accessed during the upgrade from 6.0 → 6.1 in the
            // shutdown handler for the session.
            //
            // This branch should be removed in 6.2 because it only exist for
            // the upgrade itself. DO NOT remove it in 6.1 in case later
            // releases are being used for the upgrade.
            $page = null;
        } else {
            $page = RequestHandler::getInstance()->getActivePage();
        }

        if ($page !== null) {
            $pageID = $page->pageID;

            if (!empty($_REQUEST['id'])) {
                $pageObjectID = \intval($_REQUEST['id']);
            }
        }

        if ($page !== null) {
            $this->stack[] = [
                'identifier' => $page->identifier,
                'link' => $page->getLink(),
                'pageID' => $pageID,
                'pageObjectID' => $pageObjectID,
                'title' => $page->getTitle(),
            ];
        }
    }

    /**
     * Appends a parent location to the stack, the later it is added the lower
     * is its assumed priority when matching suitable menu items.
     *
     * @param string $identifier internal page identifier
     * @param int $pageObjectID page object id
     * @param ITitledLinkObject $locationObject optional label for breadcrumbs usage
     * @param bool $useAsParentLocation
     * @throws  SystemException
     */
    public function addParentLocation(
        $identifier,
        $pageObjectID = 0,
        ?ITitledLinkObject $locationObject = null,
        $useAsParentLocation = false
    ) {
        $page = PageCache::getInstance()->getPageByIdentifier($identifier);
        if ($page === null) {
            throw new SystemException("Unknown page identifier '" . $identifier . "'.");
        }

        // check if the provided location is already part of the stack
        for ($i = 0, $length = \count($this->stack); $i < $length; $i++) {
            if ($this->stack[$i]['identifier'] == $identifier && $this->stack[$i]['pageObjectID'] == $pageObjectID) {
                return;
            }
        }

        if ($locationObject !== null) {
            $link = $locationObject->getLink();
            $title = $locationObject->getTitle();
        } else {
            $link = $page->getLink();
            $title = $page->getTitle();
        }

        $landingPage = PageCache::getInstance()->getLandingPage();
        if ($page->pageID == $landingPage->pageID && BREADCRUMBS_HOME_USE_PAGE_TITLE) {
            $title = WCF::getLanguage()->get(PAGE_TITLE);
        }

        $this->stack[] = [
            'identifier' => $identifier,
            'link' => $link,
            'pageID' => $page->pageID,
            'pageObjectID' => $pageObjectID,
            'title' => $title,
            'useAsParentLocation' => $useAsParentLocation,
        ];
    }

    /**
     * Returns the list of locations with descending priority.
     *
     * @return  array
     */
    public function getLocations()
    {
        if (!$this->addedParentLocations) {
            $this->addParents();

            $this->addedParentLocations = true;
        }

        return $this->stack;
    }

    /**
     * Adds all parents as defined through the page configuration.
     */
    protected function addParents()
    {
        if (!empty($this->stack)) {
            $location = \end($this->stack);

            if ($location['pageID']) {
                $page = PageCache::getInstance()->getPage($location['pageID']);
                $landingPage = PageCache::getInstance()->getLandingPage();
                while ($page !== null && $page->parentPageID) {
                    $page = PageCache::getInstance()->getPage($page->parentPageID);
                    if (!$page->isVisible()) {
                        continue;
                    }

                    if ($page->pageID == $landingPage->pageID && BREADCRUMBS_HOME_USE_PAGE_TITLE) {
                        $title = WCF::getLanguage()->get(PAGE_TITLE);
                    } else {
                        $title = $page->getTitle();
                    }

                    $this->stack[] = [
                        'identifier' => $page->identifier,
                        'link' => $page->getLink(),
                        'pageID' => $page->pageID,
                        'pageObjectID' => 0,
                        'title' => $title,
                        'useAsParentLocation' => false,
                    ];
                }
            }
        }
    }
}
