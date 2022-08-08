<?php

namespace wcf\system;

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
 * @package WoltLabSuite\Core\System
 */
class WCFACP extends WCF
{
    /**
     * rescue mode
     * @var bool
     */
    protected static $inRescueMode;

    /**
     * URL to WCF within rescue mode
     * @var string
     */
    protected static $rescueModePageURL;

    /** @noinspection PhpMissingParentConstructorInspection */

    /**
     * Calls all init functions of the WCF and the WCFACP class.
     */
    public function __construct()
    {
        // add autoload directory
        self::$autoloadDirectories['wcf'] = WCF_DIR . 'lib/';

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
        $this->initCronjobs();
        $this->initCoreObjects();

        // prevent application loading during setup
        if (PACKAGE_ID) {
            $this->initApplications();
        }

        $this->initAuth();

        EventHandler::getInstance()->fireAction($this, 'initialized');
    }

    /**
     * Returns true if ACP is currently in rescue mode.
     *
     * @return  bool
     */
    public static function inRescueMode()
    {
        if (self::$inRescueMode === null) {
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
     *
     * @return  string
     */
    public static function getRescueModePageURL()
    {
        if (self::inRescueMode()) {
            return self::$rescueModePageURL;
        }

        return '';
    }

    /**
     * Does the user authentication.
     */
    protected function initAuth()
    {
        // this is a work-around since neither RequestHandler
        // nor RouteHandler are populated right now
        $pathInfo = RouteHandler::getPathInfo();

        if (self::inRescueMode()) {
            if (!\preg_match('~^/?rescue-mode/~', $pathInfo)) {
                $redirectURI = self::$rescueModePageURL . 'acp/index.php?rescue-mode/';

                HeaderUtil::redirect($redirectURI);

                exit;
            }
        }
    }

    /**
     * @since 6.0
     */
    public static function overrideDebugMode()
    {
        self::$overrideDebugMode = true;
    }

    /**
     * @inheritDoc
     */
    protected function initSession()
    {
        self::$sessionObj = SessionHandler::getInstance();

        $factory = new ACPSessionFactory();
        $factory->load();
    }

    /**
     * @inheritDoc
     */
    protected function initTPL()
    {
        self::$tplObj = ACPTemplateEngine::getInstance();
        self::getTPL()->setLanguageID(self::getLanguage()->languageID);
        $this->assignDefaultTemplateVariables();
    }

    /**
     * @inheritDoc
     */
    protected function assignDefaultTemplateVariables()
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

    /**
     * @deprecated 5.4 The master password is deprecated in favor of proper MFA (https://github.com/WoltLab/WCF/issues/3698).
     */
    public static function checkMasterPassword()
    {
        // Does nothing. The master password has been removed since version 5.5.
    }
}
