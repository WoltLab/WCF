<?php

namespace wcf\system\request;

use Laminas\Diactoros\Exception\ExceptionInterface as DiactorosException;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\ServerRequestFilter\FilterUsingXForwardedHeaders;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\data\page\Page;
use wcf\data\page\PageCache;
use wcf\http\error\NotFoundHandler;
use wcf\http\LegacyPlaceholderResponse;
use wcf\http\middleware\AddAcpSecurityHeaders;
use wcf\http\middleware\CheckForEnterpriseNonOwnerAccess;
use wcf\http\middleware\CheckForExpiredAppEvaluation;
use wcf\http\middleware\CheckForForceLogin;
use wcf\http\middleware\CheckForMultifactorRequirement;
use wcf\http\middleware\CheckForOfflineMode;
use wcf\http\middleware\CheckHttpMethod;
use wcf\http\middleware\CheckSystemEnvironment;
use wcf\http\middleware\CheckUserBan;
use wcf\http\middleware\EnforceAcpAuthentication;
use wcf\http\middleware\EnforceCacheControlPrivate;
use wcf\http\middleware\EnforceFrameOptions;
use wcf\http\middleware\EnforceNoCacheForTemporaryRedirects;
use wcf\http\middleware\HandleExceptions;
use wcf\http\middleware\HandleStartupErrors;
use wcf\http\middleware\HandleValinorMappingErrors;
use wcf\http\middleware\JsonBody;
use wcf\http\middleware\PreventMimeSniffing;
use wcf\http\middleware\RedirectMediaToFrontend;
use wcf\http\middleware\TriggerBackgroundQueue;
use wcf\http\middleware\VaryAcceptLanguage;
use wcf\http\middleware\Xsrf;
use wcf\http\Pipeline;
use wcf\http\StaticResponseHandler;
use wcf\system\application\ApplicationHandler;
use wcf\system\event\EventHandler;
use wcf\system\exception\AJAXException;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\InvalidSecurityTokenException;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\exception\SystemException;
use wcf\system\request\event\ActivePageResolving;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles http requests.
 *
 * @author  Marcel Werk
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class RequestHandler extends SingletonFactory
{
    /**
     * active request object
     */
    protected ?Request $activeRequest = null;

    /**
     * indicates if the request is an acp request
     */
    protected bool $isACPRequest = false;

    private ?Page $activePage;

    /**
     * @inheritDoc
     */
    protected function init()
    {
        $this->isACPRequest = \class_exists('wcf\system\WCFACP', false);
    }

    /**
     * Handles a http request.
     *
     * @throws  AJAXException
     * @throws  IllegalLinkException
     * @throws  SystemException
     */
    public function handle(string $application = 'wcf', bool $isACPRequest = false): void
    {
        try {
            $this->isACPRequest = $isACPRequest;

            // This must be called before the PSR request is created, because it registers the
            // route paramters in $_GET.
            $routeMatches = RouteHandler::getInstance()->matches();

            try {
                $psrRequest = ServerRequestFactory::fromGlobals(
                    null, // $_SERVER
                    null, // $_GET
                    null, // $_POST
                    null, // $_COOKIE
                    null, // $_FILES
                    FilterUsingXForwardedHeaders::trustProxies(
                        ['*'],
                        [FilterUsingXForwardedHeaders::HEADER_PROTO]
                    )
                );
            } catch (DiactorosException $e) {
                if (\ENABLE_DEBUG_MODE) {
                    throw $e;
                }

                \header('HTTP/1.1 500 Internal Server Error');

                // Intentionally not localized, because this must never happen for well-formed requests.
                throw new NamedUserException('Failed to parse the incoming request.', 0, $e);
            }

            if ($routeMatches) {
                $builtRequest = $this->buildRequest($psrRequest, $application);
            } else {
                if (ENABLE_DEBUG_MODE) {
                    throw new SystemException("Cannot handle request, no valid route provided.");
                }

                $builtRequest = new NotFoundHandler();
            }

            if ($builtRequest instanceof Request) {
                $this->activeRequest = $builtRequest;

                $pipeline = new Pipeline([
                    new HandleStartupErrors(),
                    new PreventMimeSniffing(),
                    new AddAcpSecurityHeaders(),
                    new EnforceCacheControlPrivate(),
                    new EnforceNoCacheForTemporaryRedirects(),
                    new EnforceFrameOptions(),
                    new VaryAcceptLanguage(),
                    new CheckHttpMethod(),
                    new Xsrf(),
                    new CheckSystemEnvironment(),
                    new CheckUserBan(),
                    new RedirectMediaToFrontend(),
                    new EnforceAcpAuthentication(),
                    new CheckForEnterpriseNonOwnerAccess(),
                    new CheckForExpiredAppEvaluation(),
                    new CheckForOfflineMode(),
                    new CheckForForceLogin(),
                    new CheckForMultifactorRequirement(),
                    new JsonBody(),
                    new TriggerBackgroundQueue(),
                    new HandleExceptions(),
                    new HandleValinorMappingErrors(),
                ]);

                $response = $pipeline->process($psrRequest, $this->getActiveRequest());

                if ($response instanceof LegacyPlaceholderResponse) {
                    return;
                }
            } else {
                \assert($builtRequest instanceof RequestHandlerInterface);

                $pipeline = new Pipeline([
                    new HandleStartupErrors(),
                    new PreventMimeSniffing(),
                    new AddAcpSecurityHeaders(),
                    new EnforceCacheControlPrivate(),
                    new EnforceNoCacheForTemporaryRedirects(),
                    new EnforceFrameOptions(),
                    new VaryAcceptLanguage(),
                ]);

                $response = $pipeline->process($psrRequest, $builtRequest);
            }

            $emitter = new SapiEmitter();
            $emitter->emit($response);
        } catch (IllegalLinkException | PermissionDeniedException | InvalidSecurityTokenException $e) {
            throw new \LogicException(\sprintf(
                "'%s' escaped from the middleware stack.",
                $e::class
            ), 0, $e);
        } catch (NamedUserException $e) {
            $e->show();

            exit;
        }
    }

    /**
     * Builds a new request.
     *
     * @throws  NamedUserException
     * @throws  SystemException
     */
    protected function buildRequest(RequestInterface $psrRequest, string $application): Request|RequestHandlerInterface
    {
        try {
            $routeData = RouteHandler::getInstance()->getRouteData();

            \assert(RouteHandler::getInstance()->isDefaultController() xor ($routeData['controller'] ?? false));
            \assert(!isset($routeData['isDefaultController']));

            if (RouteHandler::getInstance()->isDefaultController()) {
                if ($this->isACPRequest()) {
                    $routeData['controller'] = 'index';

                    if ($application !== 'wcf') {
                        return new StaticResponseHandler(new RedirectResponse(
                            LinkHandler::getInstance()->getLink(),
                            301
                        ));
                    }
                } else {
                    $data = ControllerMap::getInstance()->lookupDefaultController($application);

                    // copy route data
                    foreach ($data as $key => $value) {
                        $routeData[$key] = $value;
                    }
                }
            }

            if (!$this->isACPRequest()) {
                // check if accessing from the wrong domain (e.g. "www." omitted but domain was configured with)
                $domainName = ApplicationHandler::getInstance()->getDomainName();
                if ($domainName !== $psrRequest->getUri()->getHost()) {
                    $targetUrl = $psrRequest->getUri()->withHost($domainName);

                    return new StaticResponseHandler(new RedirectResponse(
                        $targetUrl,
                        301
                    ));
                }
            }

            if (isset($routeData['className'])) {
                $className = $routeData['className'];
            } else {
                $controller = $routeData['controller'];

                $classApplication = $application;
                if (
                    RouteHandler::getInstance()->isDefaultController()
                    && !empty($routeData['application'])
                    && $routeData['application'] !== $application
                ) {
                    $classApplication = $routeData['application'];
                }

                $classData = ControllerMap::getInstance()->resolve(
                    $classApplication,
                    $controller,
                    $this->isACPRequest(),
                    RouteHandler::getInstance()->isRenamedController()
                );
                if (\is_string($classData)) {
                    $routeData['application'] = $application;
                    $routeData['controller'] = $classData;

                    // append the remaining query parameters
                    foreach ($_GET as $key => $value) {
                        if (!empty($value) && $key != 'controller') {
                            $routeData[$key] = $value;
                        }
                    }

                    return new StaticResponseHandler(new RedirectResponse(
                        LinkHandler::getInstance()->getLink($routeData['controller'], $routeData),
                        301
                    ));
                } else {
                    $className = $classData['className'];
                }
            }

            // handle CMS page meta data
            $metaData = [];
            if (isset($routeData['cmsPageID'])) {
                $metaData['cms'] = [
                    'pageID' => $routeData['cmsPageID'],
                    'languageID' => $routeData['cmsPageLanguageID'],
                ];

                if (
                    $routeData['cmsPageLanguageID']
                    && $routeData['cmsPageLanguageID'] != WCF::getLanguage()->languageID
                ) {
                    WCF::setLanguage($routeData['cmsPageLanguageID']);
                }
            }

            return new Request(
                $className,
                $metaData,
                !$this->isACPRequest() && ControllerMap::getInstance()->isLandingPage($className, $metaData)
            );
        } catch (SystemException $e) {
            if (
                \defined('ENABLE_DEBUG_MODE')
                && ENABLE_DEBUG_MODE
                && \defined('ENABLE_DEVELOPER_TOOLS')
                && ENABLE_DEVELOPER_TOOLS
            ) {
                throw $e;
            }

            return new NotFoundHandler();
        }
    }

    /**
     * Returns the active request object.
     */
    public function getActiveRequest(): ?Request
    {
        return $this->activeRequest;
    }

    /**
     * Returns true if the request is an acp request.
     */
    public function isACPRequest(): bool
    {
        return $this->isACPRequest;
    }

    /**
     * @deprecated 6.0 - This method always returns false.
     */
    public function inRescueMode(): bool
    {
        return false;
    }

    public function getActivePage(): ?Page
    {
        if (!isset($this->activePage)) {
            $this->determineActivePage();
        }

        return $this->activePage;
    }

    private function determineActivePage(): void
    {
        $this->activePage = null;

        if ($this->getActiveRequest() === null) {
            return;
        }

        $metaData = $this->getActiveRequest()->getMetaData();
        if (isset($metaData['cms'])) {
            $this->activePage = PageCache::getInstance()->getPage($metaData['cms']['pageID']);
        } else {
            $this->activePage = PageCache::getInstance()->getPageByController($this->getActiveRequest()->getClassName());

            if ($this->activePage === null) {
                $event = new ActivePageResolving($this->getActiveRequest());
                EventHandler::getInstance()->fire($event);
                $this->activePage = $event->page;
            }
        }
    }

    public function getActivePageID(): ?int
    {
        return $this->getActivePage()?->pageID;
    }
}
