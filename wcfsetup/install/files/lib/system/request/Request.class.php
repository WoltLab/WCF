<?php

namespace wcf\system\request;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\data\page\PageCache;
use wcf\http\LegacyPlaceholderResponse;

/**
 * Represents a page request.
 *
 * @author  Marcel Werk
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Request
 */
final class Request implements RequestHandlerInterface
{
    /**
     * page class name
     * @var string
     */
    protected $className = '';

    /**
     * @var bool
     */
    protected $isLandingPage = false;

    /**
     * request meta data
     * @var string[]
     */
    protected $metaData;

    /**
     * current page id
     * @var int
     */
    protected $pageID;

    /**
     * page name
     * @var string
     */
    protected $pageName = '';

    /**
     * request object
     * @var object
     */
    protected $requestObject;

    /**
     * Creates a new request object.
     *
     * @param string $className fully qualified name
     * @param string $pageName class name
     * @param string[] $metaData additional meta data
     */
    public function __construct($className, $pageName, array $metaData)
    {
        $this->className = $className;
        $this->metaData = $metaData;
        $this->pageName = $pageName;
    }

    /**
     * Marks this request as landing page.
     */
    public function setIsLandingPage()
    {
        $this->isLandingPage = true;
    }

    /**
     * @inheritDoc
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if ($this->requestObject === null) {
            $this->requestObject = new $this->className();
        }

        if ($this->requestObject instanceof RequestHandlerInterface) {
            return $this->requestObject->handle($request);
        } else {
            $response = $this->requestObject->__run();

            if ($response instanceof ResponseInterface) {
                return $response;
            } else {
                return new LegacyPlaceholderResponse();
            }
        }
    }

    /**
     * @deprecated 5.5 This method is of questionable value, currently unused and might not play nicely along with the future PSR-7 evaluation.
     */
    public function isExecuted()
    {
        return $this->requestObject !== null;
    }

    /**
     * Returns true if this request represents the landing page.
     *
     * @return bool
     */
    public function isLandingPage()
    {
        return $this->isLandingPage;
    }

    /**
     * Returns the page class name of this request.
     *
     * @return  string
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * Returns request meta data.
     *
     * @return  array
     * @since   3.0
     */
    public function getMetaData()
    {
        return $this->metaData;
    }

    /**
     * Returns the page name of this request.
     *
     * @return  string
     */
    public function getPageName()
    {
        return $this->pageName;
    }

    /**
     * Returns the current request object.
     *
     * @return  object
     */
    public function getRequestObject()
    {
        return $this->requestObject;
    }

    /**
     * Returns true if the requested page is available during the offline mode.
     *
     * @return  bool
     */
    public function isAvailableDuringOfflineMode()
    {
        if (
            \defined($this->className . '::AVAILABLE_DURING_OFFLINE_MODE')
            && \constant($this->className . '::AVAILABLE_DURING_OFFLINE_MODE')
        ) {
            return true;
        }

        if ($this->getPageID() && ($page = PageCache::getInstance()->getPage($this->getPageID()))) {
            if ($page->availableDuringOfflineMode) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the current page id.
     *
     * @return  int     current page id or `0` if unknown
     */
    public function getPageID()
    {
        if ($this->pageID === null) {
            if (isset($this->metaData['cms'])) {
                $this->pageID = $this->metaData['cms']['pageID'];
            } else {
                $page = PageCache::getInstance()->getPageByController($this->className);
                if ($page !== null) {
                    $this->pageID = $page->pageID;
                } else {
                    $this->pageID = 0;
                }
            }
        }

        return $this->pageID;
    }
}
