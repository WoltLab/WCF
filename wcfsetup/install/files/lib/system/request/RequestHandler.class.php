<?php

namespace wcf\system\request;

use Laminas\Diactoros\ServerRequestFactory;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use wcf\http\LegacyPlaceholderResponse;
use wcf\http\middleware\AddAcpSecurityHeaders;
use wcf\http\middleware\CheckForOfflineMode;
use wcf\http\middleware\EnforceCacheControlPrivate;
use wcf\http\middleware\EnforceFrameOptions;
use wcf\http\Pipeline;
use wcf\system\application\ApplicationHandler;
use wcf\system\exception\AJAXException;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\HeaderUtil;

/**
 * Handles http requests.
 *
 * @author  Marcel Werk
 * @copyright   2001-2020 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Request
 */
class RequestHandler extends SingletonFactory
{
    /**
     * active request object
     * @var Request
     */
    protected $activeRequest;

    /**
     * true, if current domain mismatch any known domain
     * @var bool
     */
    protected $inRescueMode = false;

    /**
     * indicates if the request is an acp request
     * @var bool
     */
    protected $isACPRequest = false;

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
     * @param string $application
     * @param bool $isACPRequest
     * @throws  AJAXException
     * @throws  IllegalLinkException
     * @throws  SystemException
     */
    public function handle($application = 'wcf', $isACPRequest = false)
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

            $psrRequest = ServerRequestFactory::fromGlobals();

            // build request
            $this->buildRequest($application);

            // enforce that certain ACP pages are not available for non-owners in enterprise mode
            if (
                $this->isACPRequest()
                && ENABLE_ENTERPRISE_MODE
                && \defined($this->getActiveRequest()->getClassName() . '::BLACKLISTED_IN_ENTERPRISE_MODE')
                && \constant($this->getActiveRequest()->getClassName() . '::BLACKLISTED_IN_ENTERPRISE_MODE')
                && !WCF::getUser()->hasOwnerAccess()
            ) {
                throw new IllegalLinkException();
            }

            $this->checkAppEvaluation();

            $pipeline = new Pipeline([
                new AddAcpSecurityHeaders(),
                new EnforceCacheControlPrivate(),
                new EnforceFrameOptions(),
                new CheckForOfflineMode(),
            ]);

            $response = $pipeline->process($psrRequest, $this->getActiveRequest());

            if ($response instanceof LegacyPlaceholderResponse) {
                return;
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
     * @param string $application
     * @throws  IllegalLinkException
     * @throws  NamedUserException
     * @throws  SystemException
     */
    protected function buildRequest($application)
    {
        try {
            $routeData = RouteHandler::getInstance()->getRouteData();

            // handle landing page for frontend requests
            if (!$this->isACPRequest()) {
                $this->handleDefaultController($application, $routeData);

                // check if accessing from the wrong domain (e.g. "www." omitted but domain was configured with)
                $domainName = ApplicationHandler::getInstance()->getDomainName();
                if ($domainName !== $_SERVER['HTTP_HOST']) {
                    // build URL, e.g. http://example.net/forum/
                    $url = FileUtil::addTrailingSlash(
                        RouteHandler::getProtocol() . $domainName . RouteHandler::getPath()
                    );

                    // query string, e.g. ?foo=bar
                    if (!empty($_SERVER['QUERY_STRING'])) {
                        $url .= '?' . $_SERVER['QUERY_STRING'];
                    }

                    HeaderUtil::redirect($url, true, false);

                    exit;
                }
            } elseif (empty($routeData['controller'])) {
                $routeData['controller'] = 'index';
            }

            $controller = $routeData['controller'];

            if (isset($routeData['className'])) {
                $classData = [
                    'className' => $routeData['className'],
                    'controller' => $routeData['controller'],
                    'pageType' => $routeData['pageType'],
                ];

                unset($routeData['className']);
                unset($routeData['controller']);
                unset($routeData['pageType']);
            } else {
                if (
                    $this->isACPRequest()
                    && ($controller === 'login' || $controller === 'index')
                    && $application !== 'wcf'
                ) {
                    HeaderUtil::redirect(
                        LinkHandler::getInstance()->getLink(\ucfirst($controller)),
                        true,
                        false
                    );

                    exit;
                }

                $classApplication = $application;
                if (
                    !empty($routeData['isDefaultController'])
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
                    $this->redirect($routeData, $application, $classData);
                }
            }

            // handle CMS page meta data
            $metaData = ['isDefaultController' => (!empty($routeData['isDefaultController']))];
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

                unset($routeData['cmsPageID']);
                unset($routeData['cmsPageLanguageID']);
            }

            $this->activeRequest = new Request(
                $classData['className'],
                $classData['controller'],
                $classData['pageType'],
                $metaData
            );

            if (!$this->isACPRequest()) {
                // determine if current request matches the landing page
                if (ControllerMap::getInstance()->isLandingPage($classData, $metaData)) {
                    $this->activeRequest->setIsLandingPage();
                }
            }
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
     * @since 5.5
     */
    private function checkAppEvaluation()
    {
        // check if the controller matches an app that has an expired evaluation date
        [$abbreviation] = \explode('\\', $this->getActiveRequest()->getClassName(), 2);
        if ($abbreviation !== 'wcf') {
            $applicationObject = ApplicationHandler::getInstance()->getApplication($abbreviation);
            $endDate = WCF::getApplicationObject($applicationObject)->getEvaluationEndDate();
            if ($endDate && $endDate < TIME_NOW) {
                $package = $applicationObject->getPackage();

                $pluginStoreFileID = WCF::getApplicationObject($applicationObject)->getEvaluationPluginStoreID();
                $isWoltLab = false;
                if ($pluginStoreFileID === 0 && \strpos($package->package, 'com.woltlab.') === 0) {
                    $isWoltLab = true;
                }

                throw new NamedUserException(WCF::getLanguage()->getDynamicVariable(
                    'wcf.acp.package.evaluation.expired',
                    [
                        'packageName' => $package->getName(),
                        'pluginStoreFileID' => $pluginStoreFileID,
                        'isWoltLab' => $isWoltLab,
                    ]
                ));
            }
        }
    }

    /**
     * Redirects to the actual URL, e.g. controller has been aliased or mistyped (boardlist instead of board-list).
     *
     * @param string[] $routeData
     * @param string $application
     * @param string $controller
     */
    protected function redirect(array $routeData, $application, $controller = null)
    {
        $routeData['application'] = $application;
        if ($controller !== null) {
            $routeData['controller'] = $controller;
        }

        // append the remaining query parameters
        foreach ($_GET as $key => $value) {
            if (!empty($value) && $key != 'controller') {
                $routeData[$key] = $value;
            }
        }

        $redirectURL = LinkHandler::getInstance()->getLink($routeData['controller'], $routeData);
        HeaderUtil::redirect($redirectURL, true, false);

        exit;
    }

    /**
     * Checks page access for possible mandatory redirects.
     *
     * @param string $application
     * @param string[] $routeData
     * @throws  IllegalLinkException
     */
    protected function handleDefaultController($application, array &$routeData)
    {
        if (!RouteHandler::getInstance()->isDefaultController()) {
            return;
        }

        $data = ControllerMap::getInstance()->lookupDefaultController($application);
        if ($data === null) {
            // handle WCF which does not have a default controller
            throw new IllegalLinkException();
        } elseif (!empty($data['redirect'])) {
            // force a redirect
            HeaderUtil::redirect($data['redirect'], true, false);

            exit;
        } elseif (!empty($data['application']) && $data['application'] !== $application) {
            $override = ControllerMap::getInstance()->getApplicationOverride($application, $data['controller']);
            if ($application !== $override) {
                HeaderUtil::redirect(
                    LinkHandler::getInstance()->getLink(
                        ControllerMap::getInstance()->resolve(
                            $data['application'],
                            $data['controller'],
                            false
                        )['controller'],
                        ['application' => $data['application']]
                    ),
                    true,
                    true
                );

                exit;
            }
        }

        // copy route data
        foreach ($data as $key => $value) {
            $routeData[$key] = $value;
        }

        $routeData['isDefaultController'] = true;
    }

    /**
     * Returns the active request object.
     *
     * @return  Request
     */
    public function getActiveRequest()
    {
        return $this->activeRequest;
    }

    /**
     * Returns true if the request is an acp request.
     *
     * @return  bool
     */
    public function isACPRequest()
    {
        return $this->isACPRequest;
    }

    /**
     * Returns true, if current host mismatches any known domain.
     *
     * @return  bool
     */
    public function inRescueMode()
    {
        return $this->inRescueMode;
    }
}
