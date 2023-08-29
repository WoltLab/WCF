<?php

namespace wcf\system\request;

use wcf\data\DatabaseObjectDecorator;
use wcf\data\IIDObject;
use wcf\data\page\PageCache;
use wcf\system\application\ApplicationHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\Regex;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\StringUtil;

/**
 * Handles relative links within the wcf.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class LinkHandler extends SingletonFactory
{
    /**
     * regex object to extract controller data from controller class name
     * @var     Regex
     * @since   5.2
     */
    protected $controllerRegex;

    /**
     * title search strings
     * @var string[]
     */
    protected $titleSearch = [];

    /**
     * title replacement strings
     * @var string[]
     */
    protected $titleReplace = [];

    /**
     * @inheritDoc
     */
    protected function init()
    {
        $this->controllerRegex = new Regex(
            '^(?P<application>[a-z][a-z0-9]*)\\\\(?P<isAcp>acp\\\\)?.+\\\\(?P<controller>[^\\\\]+)(?:Action|Form|Page)$'
        );

        if (\defined('URL_TITLE_COMPONENT_REPLACEMENT') && URL_TITLE_COMPONENT_REPLACEMENT) {
            $replacements = \explode(
                "\n",
                StringUtil::unifyNewlines(StringUtil::trim(URL_TITLE_COMPONENT_REPLACEMENT))
            );
            foreach ($replacements as $replacement) {
                if (\strpos($replacement, '=') === false) {
                    continue;
                }
                $components = \explode('=', $replacement);
                $this->titleSearch[] = $components[0];
                $this->titleReplace[] = $components[1];
            }
        }
    }

    /**
     * Returns in internal link based on the given fully qualified controller
     * class name.
     *
     * Important: The controller class is not checked if it actually exists.
     * That check happens during the runtime.
     *
     * @throws  \InvalidArgumentException   if the passed string is no controller class name
     * @since   5.2
     */
    public function getControllerLink(string $controllerClass, array $parameters = [], string $url = ''): string
    {
        if (!$this->controllerRegex->match($controllerClass)) {
            throw new \InvalidArgumentException("Invalid controller '{$controllerClass}' passed.");
        }

        $matches = $this->controllerRegex->getMatches();

        // important: matches cannot overwrite explicitly set parameters
        $parameters['application'] = $parameters['application'] ?? $matches['application'];
        $parameters['isACP'] = $parameters['isACP'] ?? $matches['isAcp'];
        $parameters['forceFrontend'] = $parameters['forceFrontend'] ?? !$matches['isAcp'];

        return $this->getLink($matches['controller'], $parameters, $url);
    }

    /**
     * Returns a relative link.
     */
    public function getLink(?string $controller = null, array $parameters = [], string $url = ''): string
    {
        $abbreviation = 'wcf';
        $anchor = '';
        $isACP = RequestHandler::getInstance()->isACPRequest();
        $isRaw = false;
        $encodeTitle = true;

        // enforce a certain level of sanitation and protection for links embedded in emails
        if (isset($parameters['isEmail'])) {
            if ((bool)$parameters['isEmail']) {
                if (!isset($parameters['isACP']) || !(bool)$parameters['isACP']) {
                    $parameters['forceFrontend'] = true;
                }
            }

            unset($parameters['isEmail']);
        }

        if (isset($parameters['application'])) {
            $abbreviation = $parameters['application'];
        }
        if (isset($parameters['isRaw'])) {
            $isRaw = $parameters['isRaw'];
            unset($parameters['isRaw']);
        }
        if (isset($parameters['isACP'])) {
            $isACP = (bool)$parameters['isACP'];
            unset($parameters['isACP']);
        }
        if (isset($parameters['forceFrontend'])) {
            if ($parameters['forceFrontend'] && $isACP) {
                $isACP = false;
            }
            unset($parameters['forceFrontend']);
        }
        if (isset($parameters['encodeTitle'])) {
            $encodeTitle = $parameters['encodeTitle'];
            unset($parameters['encodeTitle']);
        }

        /** @deprecated 3.0 */
        unset($parameters['appendSession']);
        /** @deprecated 3.0 */
        unset($parameters['forceWCF']);

        // remove anchor before parsing
        if (($pos = \strpos($url, '#')) !== false) {
            $anchor = \substr($url, $pos);
            $url = \substr($url, 0, $pos);
        }

        // build route
        if ($controller === null) {
            if ($isACP) {
                $controller = 'Index';
                if ($abbreviation !== 'wcf') {
                    throw new \InvalidArgumentException("A 'controller' must be specified for non-'wcf' links in ACP.");
                }
            } else {
                if ($abbreviation !== 'wcf') {
                    $application = ApplicationHandler::getInstance()->getApplication($abbreviation);
                    if ($application === null) {
                        throw new \RuntimeException("Unknown abbreviation '" . $abbreviation . "'.");
                    }

                    $landingPage = PageCache::getInstance()->getPage($application->landingPageID);
                    if ($landingPage === null) {
                        $landingPage = PageCache::getInstance()
                            ->getPageByController(WCF::getApplicationObject($application)->getPrimaryController());
                    }

                    if ($landingPage !== null) {
                        return $landingPage->getLink();
                    }
                }

                return PageCache::getInstance()->getLandingPage()->getLink();
            }
        }

        // handle object
        if (isset($parameters['object'])) {
            if (
                !($parameters['object'] instanceof IRouteController)
                && $parameters['object'] instanceof DatabaseObjectDecorator
                && $parameters['object']->getDecoratedObject() instanceof IRouteController
            ) {
                $parameters['object'] = $parameters['object']->getDecoratedObject();
            }

            if ($parameters['object'] instanceof IRouteController) {
                $parameters['id'] = $parameters['object']->getObjectID();
                $parameters['title'] = $parameters['object']->getTitle();
            } elseif ($parameters['object'] instanceof IIDObject) {
                $parameters['id'] = $parameters['object']->getObjectID();
            }
        }
        unset($parameters['object']);

        if (isset($parameters['title'])) {
            // component replacement
            if ($this->titleSearch !== []) {
                $parameters['title'] = \str_replace($this->titleSearch, $this->titleReplace, $parameters['title']);
            }

            // remove illegal characters
            $parameters['title'] = \trim(
                \preg_replace('/[^\p{L}\p{N}]+/u', '-', $parameters['title']),
                '-'
            );

            // trim to 80 characters
            $parameters['title'] = \rtrim(\mb_substr($parameters['title'], 0, 80), '-');
            $parameters['title'] = \mb_strtolower($parameters['title']);

            // encode title
            if ($encodeTitle) {
                $parameters['title'] = \rawurlencode($parameters['title']);
            }
        }

        $parameters['controller'] = $controller;
        if (!$isACP) {
            $abbreviation = ControllerMap::getInstance()->getApplicationOverride($abbreviation, $controller);
        }
        $routeURL = RouteHandler::getInstance()->buildRoute($abbreviation, $parameters, $isACP);
        if (!$isRaw && $url !== '') {
            $routeURL .= \str_contains($routeURL, '?') ? '&' : '?';
        }

        // encode certain characters
        if ($url !== '') {
            $url = \str_replace(['[', ']'], ['%5B', '%5D'], $url);
        }

        $url = $routeURL . $url;

        // handle applications
        if (!PACKAGE_ID) {
            $url = RouteHandler::getHost() . RouteHandler::getPath(['acp']) . ($isACP ? 'acp/' : '') . $url;
        } else {
            $application = ApplicationHandler::getInstance()->getApplication($abbreviation);
            if ($application === null) {
                throw new \InvalidArgumentException("Unknown application identifier '{$abbreviation}'.");
            }

            $pageURL = $application->getPageURL();

            $url = $pageURL . ($isACP ? 'acp/' : '') . $url;
        }

        // append previously removed anchor
        $url .= $anchor;

        return $url;
    }

    /**
     * Returns the full URL to a CMS page. The `$languageID` parameter is optional and if not
     * present (or the integer value `-1` is given) will cause the handler to pick the correct
     * language version based upon the user's language.
     *
     * Passing in an illegal page id will cause this method to fail silently, returning an
     * empty string.
     *
     * @param int $pageID page id
     * @param int $languageID language id, optional
     * @return  string      full URL of empty string if `$pageID` is invalid
     * @since   3.0
     */
    public function getCmsLink($pageID, $languageID = -1): string
    {
        $data = null;

        // use current language
        if ($languageID === -1) {
            $data = ControllerMap::getInstance()->lookupCmsPage($pageID, WCF::getLanguage()->languageID);

            // no result, attempt to use the default language instead
            $defaultLanguageID = LanguageFactory::getInstance()->getDefaultLanguageID();
            if ($data === null && $defaultLanguageID != WCF::getLanguage()->languageID) {
                $data = ControllerMap::getInstance()->lookupCmsPage($pageID, $defaultLanguageID);
            }

            // no result, possibly this is a non-multilingual page
            if ($data === null) {
                $data = ControllerMap::getInstance()->lookupCmsPage($pageID, null);
            }
        } else {
            $data = ControllerMap::getInstance()->lookupCmsPage($pageID, $languageID);
        }

        // no result, page does not exist or at least not in the given language
        if ($data === null) {
            return '';
        }

        return $this->getLink($data['controller'], [
            'application' => $data['application'],
            'forceFrontend' => true,
        ]);
    }
}
