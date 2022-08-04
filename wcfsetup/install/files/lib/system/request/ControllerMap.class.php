<?php

namespace wcf\system\request;

use Psr\Http\Server\RequestHandlerInterface;
use wcf\page\CmsPage;
use wcf\system\cache\builder\RoutingCacheBuilder;
use wcf\system\exception\SystemException;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\system\WCFACP;

/**
 * Resolves incoming requests and performs lookups for controller to url mappings.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Request
 * @since   3.0
 */
final class ControllerMap extends SingletonFactory
{
    /**
     * @var array
     * @since   5.2
     */
    protected $applicationOverrides;

    /**
     * @var array
     */
    protected $ciControllers;

    /**
     * @var array
     */
    protected $customUrls;

    /**
     * @var string[]
     */
    protected $landingPages;

    /**
     * list of <ControllerName> to <controller-name> mappings
     * @var string[]
     */
    protected $lookupCache = [];

    /**
     * @inheritDoc
     */
    protected function init()
    {
        $this->applicationOverrides = RoutingCacheBuilder::getInstance()->getData([], 'applicationOverrides');
        $this->ciControllers = RoutingCacheBuilder::getInstance()->getData([], 'ciControllers');
        $this->customUrls = RoutingCacheBuilder::getInstance()->getData([], 'customUrls');
        $this->landingPages = RoutingCacheBuilder::getInstance()->getData([], 'landingPages');
    }

    /**
     * Resolves class data for given controller.
     *
     * URL -> Controller
     *
     * @param $skipCustomUrls true if custom url resolution should be suppressed, is always true for ACP requests
     * @return  mixed       array containing className and controller or a string containing the controller name for aliased controllers
     * @throws  SystemException
     */
    public function resolve(string $application, string $controller, bool $isAcpRequest, bool $skipCustomUrls = false)
    {
        // validate controller
        if (!\preg_match('/^[a-z][a-z0-9]+(?:-[a-z][a-z0-9]+)*$/', $controller)) {
            throw new SystemException("Malformed controller name '" . $controller . "'");
        }

        $classData = $this->getLegacyClassData($application, $controller, $isAcpRequest);
        if ($classData === null) {
            $parts = \explode('-', $controller);
            $parts = \array_map('ucfirst', $parts);
            $controller = \implode('', $parts);

            // Map virtual controllers to their true application
            if (isset($this->applicationOverrides['lookup'][$application][$controller])) {
                $application = $this->applicationOverrides['lookup'][$application][$controller];
            }

            if ($classData === null) {
                $classData = $this->getPsr15ClassData($application, $controller, $isAcpRequest);
            }
            if ($classData === null) {
                $classData = $this->getClassData($application, $controller, $isAcpRequest, 'page');
            }
            if ($classData === null) {
                $classData = $this->getClassData($application, $controller, $isAcpRequest, 'form');
            }
            if ($classData === null) {
                $classData = $this->getClassData($application, $controller, $isAcpRequest, 'action');
            }
        }

        if ($classData === null) {
            throw new SystemException("Unknown controller '" . $controller . "'");
        } else {
            // the ACP does not support custom urls at all
            if ($isAcpRequest) {
                $skipCustomUrls = true;
            }

            if (!$skipCustomUrls) {
                // handle controllers with a custom url
                $controller = $classData['controller'];

                if (isset($this->customUrls['reverse'][$application][$controller])) {
                    return $this->customUrls['reverse'][$application][$controller];
                }
            }
        }

        return $classData;
    }

    /**
     * Attempts to resolve a custom controller, will return an empty array
     * regardless if given controller would match an actual controller class.
     *
     * URL -> Controller
     *
     * @return  array       empty array if there is no exact match
     */
    public function resolveCustomController(string $application, string $controller): array
    {
        if ($controller === '') {
            throw new \InvalidArgumentException('The given controller must not be empty.');
        }

        if (isset($this->applicationOverrides['lookup'][$application][$controller])) {
            $application = $this->applicationOverrides['lookup'][$application][$controller];
        }

        if (isset($this->customUrls['lookup'][$application][$controller])) {
            $data = $this->customUrls['lookup'][$application][$controller];
            if (\preg_match('/^__WCF_CMS__(?P<pageID>\\d+)-(?P<languageID>\\d+)$/', $data, $matches)) {
                return [
                    'className' => CmsPage::class,
                    'controller' => 'cms',

                    // CMS page meta data
                    'cmsPageID' => $matches['pageID'],
                    'cmsPageLanguageID' => $matches['languageID'],
                ];
            } else {
                \preg_match('/([^\\\\]+)(Action|Form|Page)$/', $data, $matches);

                return [
                    'className' => $data,
                    'controller' => $matches[1],
                ];
            }
        }

        return [];
    }

    /**
     * Transforms given controller into its url representation.
     *
     * Controller -> URL
     *
     * @param $controller controller class, e.g. 'MembersList'
     * @param bool $forceFrontend force transformation for frontend
     * @return  string      url representation of controller, e.g. 'members-list'
     */
    public function lookup(string $application, string $controller, $forceFrontend = null)
    {
        if ($forceFrontend === null) {
            $forceFrontend = !\class_exists(WCFACP::class, false);
        }

        $lookupKey = ($forceFrontend ? '' : 'acp-') . $application . '-' . $controller;

        if (isset($this->lookupCache[$lookupKey])) {
            return $this->lookupCache[$lookupKey];
        }

        if (
            $forceFrontend
            && isset($this->customUrls['reverse'][$application][$controller])
        ) {
            $urlController = $this->customUrls['reverse'][$application][$controller];
        } else {
            $urlController = self::transformController($controller);
        }

        $this->lookupCache[$lookupKey] = $urlController;

        return $urlController;
    }

    /**
     * Looks up a cms page URL, returns an array containing the application identifier
     * and url controller name or null if there was no match.
     *
     * @param int $pageID page id
     * @param int $languageID content language id
     * @return  string[]|null
     */
    public function lookupCmsPage($pageID, $languageID)
    {
        $key = '__WCF_CMS__' . $pageID . '-' . ($languageID ?: 0);
        foreach ($this->customUrls['reverse'] as $application => $reverseURLs) {
            if (isset($reverseURLs[$key])) {
                return [
                    'application' => $application,
                    'controller' => $reverseURLs[$key],
                ];
            }
        }

        return null;
    }

    /**
     * Lookups default controller for given application.
     *
     * @return  string[]   default controller
     * @throws  SystemException
     */
    public function lookupDefaultController(string $application): array
    {
        $routePart = $this->landingPages[$application]['routePart'];

        if (\preg_match('/^__WCF_CMS__(?P<pageID>\\d+)$/', $routePart, $matches)) {
            $cmsPageData = $this->lookupCmsPage($matches['pageID'], 0);
            if ($cmsPageData === null) {
                // page is multilingual

                $cmsPageData = $this->lookupCmsPage($matches['pageID'], WCF::getLanguage()->languageID);
                if ($cmsPageData === null) {
                    throw new SystemException("Unable to resolve CMS page");
                }
            }

            return $this->resolveCustomController($cmsPageData['application'], $cmsPageData['controller']);
        }

        $parts = \explode('\\', $this->landingPages[$application]['className']);
        $application = \array_shift($parts);
        if ($application === '') {
            $application = \array_shift($parts);
        }

        return [
            'application' => $application,
            'controller' => $routePart,
        ];
    }

    /**
     * Returns true if given controller is the application's default.
     */
    public function isDefaultController(string $application, string $controller): bool
    {
        $customController = $this->resolveCustomController($application, $controller);

        if ($customController !== []) {
            if ($customController['className'] === CmsPage::class) {
                // i18n CMS pages must preserve the controller within the URL.
                if ($customController['cmsPageLanguageID']) {
                    return false;
                }

                return $this->landingPages[$application]['controller'] === "__WCF_CMS__{$customController['cmsPageID']}";
            } else {
                return $this->landingPages[$application]['controller'] === $customController['controller'];
            }
        }

        return $this->landingPages[$application]['controller'] === $controller;
    }

    /**
     * Returns true if currently active request represents the global landing page.
     *
     * @param array $metaData
     */
    public function isLandingPage(string $className, array $metaData): bool
    {
        if ($className !== $this->landingPages['wcf']['className']) {
            return false;
        }

        if ($className === CmsPage::class) {
            // check if page id matches
            if ($this->landingPages['wcf']['routePart'] !== '__WCF_CMS__' . $metaData['cms']['pageID']) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns the virtual application abbreviation for the provided controller, returns the
     * given application as-is if no override exists.
     */
    public function getApplicationOverride(string $application, string $controller): string
    {
        if (isset($this->applicationOverrides['reverse'][$application][$controller])) {
            return $this->applicationOverrides['reverse'][$application][$controller];
        }

        return $application;
    }

    /**
     * Lookups the list of legacy controller names that violate the name
     * schema, e.g. are named 'BBCodeList' instead of `BbCodeList`.
     *
     * @return      string[]|null   className and controller, or null if this is not a legacy controller name
     */
    protected function getLegacyClassData(string $application, string $controller, bool $isAcpRequest)
    {
        $environment = $isAcpRequest ? 'acp' : 'frontend';
        if (isset($this->ciControllers[$application][$environment][$controller])) {
            $className = $this->ciControllers[$application][$environment][$controller];

            if (\preg_match('/\\\\(?P<controller>[^\\\\]+)(Action|Form|Page)$/', $className, $matches)) {
                return [
                    'className' => $className,
                    'controller' => $matches['controller'],
                ];
            }
        }

        return null;
    }

    /**
     * Returns the class data for the active request or `null` if no proper class exists
     * for the given configuration.
     *
     * @param $pageType page type, e.g. 'form' or 'action'
     * @return  string[]|null   className and controller
     */
    protected function getClassData(string $application, string $controller, bool $isAcpRequest, string $pageType)
    {
        $className = $application . '\\' . ($isAcpRequest ? 'acp\\' : '') . $pageType . '\\' . $controller . \ucfirst($pageType);
        if (!\class_exists($className)) {
            return null;
        }

        // Verify that the class can be instantiated. This excludes
        // abstract classes, interfaces, classes with a private constructor
        // and more.
        $reflectionClass = new \ReflectionClass($className);
        if (!$reflectionClass->isInstantiable()) {
            return null;
        }

        return [
            'className' => $className,
            'controller' => $controller,
        ];
    }

    private function getPsr15ClassData(string $application, string $controller, bool $isAcpRequest)
    {
        $className = $application . '\\http\\controller\\' . ($isAcpRequest ? 'acp\\' : '') . $controller;
        if (!\class_exists($className)) {
            return null;
        }

        // Verify that the class can be instantiated. This excludes
        // abstract classes, interfaces, classes with a private constructor
        // and more.
        $reflectionClass = new \ReflectionClass($className);
        if (!$reflectionClass->isInstantiable()) {
            return null;
        }
        if (!$reflectionClass->implementsInterface(RequestHandlerInterface::class)) {
            return null;
        }

        return [
            'className' => $className,
            'controller' => $controller,
        ];
    }

    /**
     * Transforms a controller (e.g. BoardList) into its URL representation (e.g. board-list).
     */
    public static function transformController(string $controller): string
    {
        // work-around for broken controllers that violate the strict naming rules
        if (\preg_match('/[A-Z]{2,}/', $controller)) {
            $parts = \preg_split(
                '/([A-Z][a-z0-9]+)/',
                $controller,
                -1,
                \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY
            );

            // fix for invalid pages that would cause single character fragments
            $sanitizedParts = [];
            $tmp = '';
            foreach ($parts as $part) {
                if (\strlen($part) === 1) {
                    $tmp .= $part;
                    continue;
                }

                $sanitizedParts[] = $tmp . $part;
                $tmp = '';
            }
            if ($tmp) {
                $sanitizedParts[] = $tmp;
            }
            $parts = $sanitizedParts;
        } else {
            $parts = \preg_split(
                '/([A-Z][a-z0-9]+)/',
                $controller,
                -1,
                \PREG_SPLIT_DELIM_CAPTURE | \PREG_SPLIT_NO_EMPTY
            );
        }

        return \strtolower(\implode('-', $parts));
    }
}
