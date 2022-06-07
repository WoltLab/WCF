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
 * @author  Tim Duesterhus, Marcel Werk
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Request
 */
final class Request implements RequestHandlerInterface
{
    private readonly string $className;

    private readonly bool $isLandingPage;

    private readonly array $metaData;

    /**
     * current page id
     * @var int
     */
    private $pageID;

    /**
     * request object
     * @var object
     */
    private $requestObject;

    public function __construct(string $className, array $metaData, bool $isLandingPage)
    {
        $this->className = $className;
        $this->metaData = $metaData;
        $this->isLandingPage = $isLandingPage;
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
     * Returns true if this request represents the landing page.
     */
    public function isLandingPage(): bool
    {
        return $this->isLandingPage;
    }

    /**
     * Returns the page class name of this request.
     */
    public function getClassName(): string
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
     */
    public function isAvailableDuringOfflineMode(): bool
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
        if (!isset($this->pageID)) {
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
