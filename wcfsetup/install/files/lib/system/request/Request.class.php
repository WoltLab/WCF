<?php

namespace wcf\system\request;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\action\IAction;
use wcf\http\LegacyPlaceholderResponse;
use wcf\page\IPage;

/**
 * Represents a page request.
 *
 * @author  Tim Duesterhus, Marcel Werk
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class Request implements RequestHandlerInterface
{
    private readonly string $className;

    private readonly bool $isLandingPage;

    /**
     * @var array{cms?: array{pageID: int, languageID: int}}
     */
    private readonly array $metaData;

    private RequestHandlerInterface|IAction|IPage $requestObject;

    /**
     * @param array{cms?: array{pageID: int, languageID: int}} $metaData
     */
    public function __construct(string $className, array $metaData, bool $isLandingPage)
    {
        $this->className = $className;
        $this->metaData = $metaData;
        $this->isLandingPage = $isLandingPage;
    }

    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!isset($this->requestObject)) {
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
     * @return array{cms?: array{pageID: int, languageID: int}}
     */
    public function getMetaData(): array
    {
        return $this->metaData;
    }

    /**
     * Returns the current request object.
     */
    public function getRequestObject(): RequestHandlerInterface|IAction|IPage|null
    {
        if (!isset($this->requestObject)) {
            return null;
        }

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

        $page = RequestHandler::getInstance()->getActivePage();
        if ($page?->availableDuringOfflineMode) {
            return true;
        }

        return false;
    }

    /**
     * Returns the current page id.
     *
     * @return int current page id or `0` if unknown
     * @deprecated 6.1 use `RequestHandler::getInstance()->getActivePageID()` instead
     */
    public function getPageID(): int
    {
        return RequestHandler::getInstance()->getActivePageID() ?: 0;
    }
}
