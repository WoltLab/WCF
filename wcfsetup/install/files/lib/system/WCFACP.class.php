<?php

namespace wcf\system;

use Laminas\Diactoros\Uri;
use wcf\system\application\ApplicationHandler;
use wcf\system\cache\builder\ACPSearchProviderCacheBuilder;
use wcf\system\event\EventHandler;
use wcf\system\request\RouteHandler;
use wcf\system\session\ACPSessionFactory;
use wcf\system\session\SessionHandler;
use wcf\system\template\ACPTemplateEngine;
use wcf\util\FileUtil;
use wcf\util\HeaderUtil;

/**
 * Extends WCF class with functions for the ACP.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class WCFACP extends WCF
{
    /**
     * rescue mode
     */
    protected static bool $inRescueMode;

    /**
     * URL to WCF within rescue mode
     */
    protected static string $rescueModePageURL;

    /** @noinspection PhpMissingParentConstructorInspection */

    /**
     * Calls all init functions of the WCF and the WCFACP class.
     */
    public function __construct()
    {
        // define tmp directory
        if (!\defined('TMP_DIR')) {
            \define('TMP_DIR', FileUtil::getTempFolder());
        }

        // start initialization
        $this->initDB();
        $this->loadOptions();
        $this->initSession();
        $this->initLanguage();
        $this->initTPL();
        $this->initCoreObjects();

        // prevent application loading during setup
        if (PACKAGE_ID) {
            $this->initApplications();
        }

        $this->runBootstrappers();

        $this->initAuth();

        EventHandler::getInstance()->fireAction($this, 'initialized');
    }

    /**
     * Returns true if ACP is currently in rescue mode.
     */
    public static function inRescueMode(): bool
    {
        if (!isset(self::$inRescueMode)) {
            self::$inRescueMode = false;

            if (\PACKAGE_ID && isset($_SERVER['HTTP_HOST'])) {
                self::$inRescueMode = true;

                $activeApplication = ApplicationHandler::getInstance()->getApplicationByID(\PACKAGE_ID);
                if ($activeApplication->domainName === $_SERVER['HTTP_HOST']) {
                    self::$inRescueMode = false;
                }

                if (!self::$inRescueMode) {
                    if ($activeApplication->domainPath !== RouteHandler::getPath(['acp'])) {
                        self::$inRescueMode = true;
                    }
                }

                if (self::$inRescueMode) {
                    self::$rescueModePageURL = RouteHandler::getProtocol() . $_SERVER['HTTP_HOST'] . RouteHandler::getPath(['acp']);
                }
            }
        }

        return self::$inRescueMode;
    }

    /**
     * Returns URL for rescue mode page.
     */
    public static function getRescueModePageURL(): string
    {
        if (self::inRescueMode()) {
            return self::$rescueModePageURL;
        }

        return '';
    }

    /**
     * Does the user authentication.
     */
    protected function initAuth(): void
    {
        // this is a work-around since neither RequestHandler
        // nor RouteHandler are populated right now
        $pathInfo = RouteHandler::getPathInfo();

        if (self::inRescueMode()) {
            if (!\preg_match('~^/?rescue-mode/~', $pathInfo)) {
                if (\PACKAGE_ID != 1) {
                    $uri = new Uri(self::$rescueModePageURL);
                    $uri = $uri->withPath(FileUtil::getRealPath($uri->getPath() . 'acp/' . \RELATIVE_WCF_DIR));
                    $pageURL = $uri->__toString();
                } else {
                    $pageURL = self::$rescueModePageURL;
                }

                $redirectURI = $pageURL . 'acp/index.php?rescue-mode/';

                HeaderUtil::redirect($redirectURI);

                exit;
            }
        }
    }

    /**
     * @since 6.0
     */
    public static function overrideDebugMode(): void
    {
        self::$overrideDebugMode = true;
    }

    /**
     * @inheritDoc
     */
    protected function initSession(): void
    {
        self::$sessionObj = SessionHandler::getInstance();

        $factory = new ACPSessionFactory();
        $factory->load();
    }

    /**
     * @inheritDoc
     */
    protected function initTPL(): void
    {
        self::$tplObj = ACPTemplateEngine::getInstance();
        self::getTPL()->setLanguageID(self::getLanguage()->languageID);
        $this->assignDefaultTemplateVariables();
    }

    /**
     * @inheritDoc
     */
    protected function assignDefaultTemplateVariables(): void
    {
        parent::assignDefaultTemplateVariables();

        // base tag is determined on runtime
        $host = RouteHandler::getHost();
        $path = RouteHandler::getPath();

        // available acp search providers
        $availableAcpSearchProviders = [];
        foreach (ACPSearchProviderCacheBuilder::getInstance()->getData() as $searchProvider) {
            $availableAcpSearchProviders[$searchProvider->providerName] = self::getLanguage()->get(
                'wcf.acp.search.provider.' . $searchProvider->providerName
            );
        }
        \asort($availableAcpSearchProviders);

        self::getTPL()->assign([
            'baseHref' => $host . $path,
            'availableAcpSearchProviders' => $availableAcpSearchProviders,
        ]);
    }
}
