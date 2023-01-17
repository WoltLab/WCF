<?php

namespace wcf\system\request;

use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\ServerRequestFilter\FilterUsingXForwardedHeaders;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use wcf\http\LegacyPlaceholderResponse;
use wcf\http\middleware\AddAcpSecurityHeaders;
use wcf\http\middleware\CheckForEnterpriseNonOwnerAccess;
use wcf\http\middleware\CheckForExpiredAppEvaluation;
use wcf\http\middleware\CheckForOfflineMode;
use wcf\http\middleware\CheckHttpMethod;
use wcf\http\middleware\CheckSystemEnvironment;
use wcf\http\middleware\CheckUserBan;
use wcf\http\middleware\EnforceAcpAuthentication;
use wcf\http\middleware\EnforceCacheControlPrivate;
use wcf\http\middleware\EnforceFrameOptions;
use wcf\http\middleware\EnforceNoCacheForTemporaryRedirects;
use wcf\http\middleware\HandleStartupErrors;
use wcf\http\middleware\HandleValinorMappingErrors;
use wcf\http\middleware\JsonBody;
use wcf\http\middleware\PreventMimeSniffing;
use wcf\http\middleware\TriggerBackgroundQueue;
use wcf\http\middleware\Xsrf;
use wcf\http\Pipeline;
use wcf\system\application\ApplicationHandler;
use wcf\system\exception\AJAXException;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;

/**
 * Handles http requests.
 *
 * @author  Marcel Werk
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Request
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

            if (!RouteHandler::getInstance()->matches()) {
                if (ENABLE_DEBUG_MODE) {
                    throw new SystemException("Cannot handle request, no valid route provided.");
                } else {
                    throw new IllegalLinkException();
                }
            }

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

            $builtRequest = $this->buildRequest($psrRequest, $application);

            if ($builtRequest instanceof Request) {
                $this->activeRequest = $builtRequest;

                $pipeline = new Pipeline([
                    new HandleStartupErrors(),
                    new PreventMimeSniffing(),
                    new AddAcpSecurityHeaders(),
                    new EnforceCacheControlPrivate(),
                    new EnforceNoCacheForTemporaryRedirects(),
                    new EnforceFrameOptions(),
                    new CheckHttpMethod(),
                    new Xsrf(),
                    new CheckSystemEnvironment(),
                    new CheckUserBan(),
                    new EnforceAcpAuthentication(),
                    new CheckForEnterpriseNonOwnerAccess(),
                    new CheckForExpiredAppEvaluation(),
                    new CheckForOfflineMode(),
                    new JsonBody(),
                    new TriggerBackgroundQueue(),
                    new HandleValinorMappingErrors(),
                ]);

                $response = $pipeline->process($psrRequest, $this->getActiveRequest());

                if ($response instanceof LegacyPlaceholderResponse) {
                    return;
                }
            } else {
                \assert($builtRequest instanceof ResponseInterface);
                $response = $builtRequest;
            }

            $emitter = new SapiEmitter();
            $emitter->emit($response);
        } catch (NamedUserException $e) {
            $e->show();

            exit;
        }
    }

    /**
     * Builds a new request.
     *
     * @throws  IllegalLinkException
     * @throws  NamedUserException
     * @throws  SystemException
     */
    protected function buildRequest(RequestInterface $psrRequest, string $application): Request|ResponseInterface
    {
        try {
            $routeData = RouteHandler::getInstance()->getRouteData();

            \assert(RouteHandler::getInstance()->isDefaultController() xor ($routeData['controller'] ?? false));
            \assert(!isset($routeData['isDefaultController']));

            if (RouteHandler::getInstance()->isDefaultController()) {
                if ($this->isACPRequest()) {
                    $routeData['controller'] = 'index';

                    if ($application !== 'wcf') {
                        return new RedirectResponse(
                            LinkHandler::getInstance()->getLink(),
                            301
                        );
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

                    return new RedirectResponse(
                        $targetUrl,
                        301
                    );
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

                    return new RedirectResponse(
                        LinkHandler::getInstance()->getLink($routeData['controller'], $routeData),
                        301
                    );
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

            throw new IllegalLinkException();
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
}
