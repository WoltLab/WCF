<?php

namespace wcf\system;

use wcf\data\application\Application;
use wcf\data\option\OptionEditor;
use wcf\data\package\Package;
use wcf\data\package\PackageCache;
use wcf\data\package\PackageEditor;
use wcf\data\page\Page;
use wcf\system\application\ApplicationHandler;
use wcf\system\application\IApplication;
use wcf\system\box\BoxHandler;
use wcf\system\cache\builder\CoreObjectCacheBuilder;
use wcf\system\cache\builder\PackageUpdateCacheBuilder;
use wcf\system\database\MySQLDatabase;
use wcf\system\event\EventHandler;
use wcf\system\exception\ErrorException;
use wcf\system\exception\IPrintableException;
use wcf\system\exception\ParentClassException;
use wcf\system\exception\SystemException;
use wcf\system\language\LanguageFactory;
use wcf\system\package\command\RebuildBootstrapper;
use wcf\system\package\PackageInstallationDispatcher;
use wcf\system\registry\RegistryHandler;
use wcf\system\request\Request;
use wcf\system\request\RequestHandler;
use wcf\system\session\SessionFactory;
use wcf\system\session\SessionHandler;
use wcf\system\style\StyleHandler;
use wcf\system\template\EmailTemplateEngine;
use wcf\system\template\TemplateEngine;
use wcf\system\user\storage\UserStorageHandler;
use wcf\util\DirectoryUtil;
use wcf\util\FileUtil;
use wcf\util\StringUtil;

// phpcs:disable PSR1.Files.SideEffects

// This is the earliest point that is reliably executed.
if (($error = \error_get_last()) !== null) {
    \define('WCF_STARTUP_ERROR', $error);
}

// try to set a time-limit to infinite
@\set_time_limit(0);

// fix timezone warning issue
if (!\ini_get('date.timezone')) {
    @\date_default_timezone_set('UTC');
}

// Force enable the reporting of all errors, no matter what's
// configured in php.ini.
\error_reporting(\E_ALL);

// Make stack traces more useful with PHP 8.2 (which has SensitiveParameter).
if (\PHP_VERSION_ID >= 80200) {
    @\ini_set('zend.exception_ignore_args', 0);
    @\ini_set('zend.exception_string_param_max_len', 25);
}
@\ini_set('assert.exception', 1);

// setting global gzip compression breaks output buffering
if (\ini_get('zlib.output_compression')) {
    @\ini_set('zlib.output_compression', '0');
}

// Clean out any output buffer that is enabled by default ('output_buffering' ini setting).
while (\ob_get_level()) {
    \ob_end_flush();
}

// Ensure a correct mbstring configuration
\mb_internal_encoding('UTF-8');
if (\function_exists('mb_regex_encoding')) {
    \mb_regex_encoding('UTF-8');
}
\mb_language('uni');

// define current woltlab suite version
\define('WCF_VERSION', '6.1.0 dev 1');

// define current unix timestamp
\define('TIME_NOW', \time());

// wcf imports
require_once(__DIR__ . '/api/autoload.php');

if (!\defined('NO_IMPORTS')) {
    require_once(__DIR__ . '/../core.functions.php');
}

/**
 * WCF is the central class for the WoltLab Suite Core.
 * It holds the database connection, access to template and language engine.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class WCF
{
    /**
     * @var ?string
     * @since 5.3
     */
    public const AVAILABLE_UPGRADE_VERSION = null;

    /**
     * list of currently loaded applications
     * @var Application[]
     */
    protected static $applications = [];

    /**
     * list of currently loaded application objects
     * @var IApplication[]
     */
    protected static $applicationObjects = [];

    /**
     * list of autoload directories
     * @var array
     */
    protected static $autoloadDirectories = [
        'wcf' => WCF_DIR . 'lib/',
    ];

    /**
     * list of unique instances of each core object
     * @var SingletonFactory[]
     */
    protected static $coreObject = [];

    /**
     * list of cached core objects
     * @var string[]
     */
    protected static $coreObjectCache = [];

    /**
     * database object
     * @var MySQLDatabase
     */
    protected static $dbObj;

    /**
     * language object
     * @var \wcf\data\language\Language
     */
    protected static $languageObj;

    /**
     * overrides disabled debug mode
     * @var bool
     */
    protected static $overrideDebugMode = false;

    /**
     * session object
     * @var SessionHandler
     */
    protected static $sessionObj;

    /**
     * template object
     * @var TemplateEngine
     */
    protected static $tplObj;

    /**
     * true if Zend Opcache is loaded and enabled
     * @var bool
     */
    protected static $zendOpcacheEnabled;

    public const BOOTSTRAP_LOADER = \WCF_DIR . '/lib/bootstrap.php';

    /**
     * Calls all init functions of the WCF class.
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
        $this->initApplications();

        $this->runBootstrappers();

        self::getTPL()->assign([
            '__userAuthConfig' => \wcf\system\user\authentication\configuration\UserAuthenticationConfigurationFactory::getInstance()->getConfigration(),
        ]);

        EventHandler::getInstance()->fireAction($this, 'initialized');
    }

    /**
     * @since 6.0
     */
    final protected function runBootstrappers(): void
    {
        try {
            $bootstrappers = require(self::BOOTSTRAP_LOADER);
        } catch (\Exception $e) {
            \wcf\functions\exception\logThrowable($e);

            $command = new RebuildBootstrapper();
            $command();

            $bootstrappers = require(self::BOOTSTRAP_LOADER);
        }

        foreach ($bootstrappers as $bootstrapper) {
            $bootstrapper();
        }
    }

    /**
     * Flushes the output, closes the session, performs background tasks and more.
     *
     * You *must* not create output in here under normal circumstances, as it might get eaten
     * when gzip is enabled.
     */
    public static function destruct()
    {
        try {
            // database has to be initialized
            if (!\is_object(self::$dbObj)) {
                return;
            }

            $debug = self::debugModeIsEnabled(true);
            if (!$debug) {
                // flush output
                if (\ob_get_level()) {
                    \ob_end_flush();
                }
                \flush();

                // close connection if using FPM
                if (\function_exists('fastcgi_finish_request')) {
                    fastcgi_finish_request();
                }
            }

            // update session
            if (\is_object(self::getSession())) {
                self::getSession()->update();
            }

            // execute shutdown actions of storage handlers
            RegistryHandler::getInstance()->shutdown();
            UserStorageHandler::getInstance()->shutdown();
        } catch (\Exception $exception) {
            exit("<pre>WCF::destruct() Unhandled exception: " . $exception->getMessage() . "\n\n" . $exception->getTraceAsString());
        }
    }

    /**
     * Returns the database object.
     *
     * @return  \wcf\system\database\Database
     */
    final public static function getDB()
    {
        return self::$dbObj;
    }

    /**
     * Returns the session object.
     *
     * @return  SessionHandler
     */
    final public static function getSession()
    {
        return self::$sessionObj;
    }

    /**
     * Returns the user object.
     *
     * @return  \wcf\data\user\User
     */
    final public static function getUser()
    {
        return self::getSession()->getUser();
    }

    /**
     * Returns the language object.
     *
     * @return  \wcf\data\language\Language
     */
    final public static function getLanguage()
    {
        return self::$languageObj;
    }

    /**
     * Returns the template object.
     *
     * @return  TemplateEngine
     */
    final public static function getTPL()
    {
        return self::$tplObj;
    }

    /**
     * Calls the show method on the given exception.
     */
    final public static function handleException(\Throwable $e)
    {
        // backwards compatibility
        if ($e instanceof IPrintableException) {
            $e->show();

            exit;
        }

        // discard any output generated before the exception occurred, prevents exception
        // being hidden inside HTML elements and therefore not visible in browser output
        //
        // ob_get_level() can return values > 1, if the PHP setting `output_buffering` is on
        while (\ob_get_level()) {
            \ob_end_clean();
        }

        @\header('HTTP/1.1 500 Internal Server Error');
        try {
            \wcf\functions\exception\printThrowable($e);
        } catch (\Throwable $e2) {
            echo "<pre>An Exception was thrown while handling an Exception:\n\n";
            echo \preg_replace('/Database->__construct\(.*\)/', 'Database->__construct(...)', $e2);
            echo "\n\nwas thrown while:\n\n";
            echo \preg_replace('/Database->__construct\(.*\)/', 'Database->__construct(...)', $e);
            echo "\n\nwas handled.</pre>";

            exit;
        }
    }

    /**
     * Turns PHP errors into an ErrorException.
     *
     * @param int $severity
     * @param string $message
     * @param string $file
     * @param int $line
     * @throws  ErrorException
     */
    final public static function handleError($severity, $message, $file, $line): void
    {
        // this is necessary for the shut-up operator
        if (!(\error_reporting() & $severity)) {
            return;
        }

        throw new ErrorException($message, 0, $severity, $file, $line);
    }

    /**
     * Loads the database configuration and creates a new connection to the database.
     */
    protected function initDB(): void
    {
        // get configuration
        $dbHost = $dbUser = $dbPassword = $dbName = '';
        $dbPort = 0;
        $defaultDriverOptions = [];
        require(WCF_DIR . 'config.inc.php');

        // create database connection
        self::$dbObj = new MySQLDatabase(
            $dbHost,
            $dbUser,
            $dbPassword,
            $dbName,
            $dbPort,
            false,
            false,
            $defaultDriverOptions
        );
    }

    /**
     * Loads the options file, automatically created if not exists.
     */
    protected function loadOptions(): void
    {
        $this->defineLegacyOptions();

        $filename = WCF_DIR . 'options.inc.php';

        // create options file if doesn't exist
        if (!\file_exists($filename) || \filemtime($filename) <= 1) {
            OptionEditor::rebuild();
        }
        require($filename);

        // check if option file is complete and writable
        if (PACKAGE_ID) {
            if (!\is_writable($filename)) {
                FileUtil::makeWritable($filename);

                if (!\is_writable($filename)) {
                    throw new SystemException("The option file '" . $filename . "' is not writable.");
                }
            }

            // check if a previous write operation was incomplete and force rebuilding
            if (!\defined('WCF_OPTION_INC_PHP_SUCCESS')) {
                OptionEditor::rebuild();

                require($filename);
            }

            if (ENABLE_DEBUG_MODE) {
                self::$dbObj->enableDebugMode();

                // zend.assertions can't be enabled at runtime if the value is set to -1, because
                // the necessary opcodes will not be compiled.
                if (\ini_get('zend.assertions') >= 0) {
                    @\ini_set('zend.assertions', 1);
                }

                \spl_autoload_unregister([self::class, 'autoload']);
                \spl_autoload_register([self::class, 'autoloadDebug'], true, true);
            }
        }
    }

    /**
     * Defines constants for obsolete options, which were removed.
     *
     * @since   5.4
     */
    protected function defineLegacyOptions(): void
    {
        // The master password has been removed since 5.5.
        // https://github.com/WoltLab/WCF/issues/3913
        \define('MODULE_MASTER_PASSWORD', 0);

        // The IP address and User Agent blocklist was removed in 5.5.
        // https://github.com/WoltLab/WCF/issues/3914
        \define('BLACKLIST_IP_ADDRESSES', '');
        \define('BLACKLIST_USER_AGENTS', '');

        // The captcha option related to the removed MailForm was removed in 5.5.
        \define('PROFILE_MAIL_USE_CAPTCHA', 1);

        // The censorship module is fully configured by the censored_words option since 5.5.
        // If this option is empty, no censorship will be performed.
        \define('ENABLE_CENSORSHIP', 1);

        // The captcha option related to the removed SearchForm was removed in 5.5.
        \define('SEARCH_USE_CAPTCHA', 0);

        // The prompt for desktop notifications is no longer obtrusive since 5.5.
        // https://github.com/WoltLab/WCF/issues/4806
        \define('ENABLE_DESKTOP_NOTIFICATIONS', 1);

        // Disabling X-Frame-Options is no longer possible since 6.0.
        \define('HTTP_SEND_X_FRAME_OPTIONS', 1);

        // Multi-domain setups were removed in 6.0.
        \define('DESKTOP_NOTIFICATION_PACKAGE_ID', 1);

        // Gravatars were removed in 6.0.
        \define('MODULE_GRAVATAR', 0);
        \define('GRAVATAR_DEFAULT_TYPE', '404');

        // The option for article visit tracking was removed in 6.0.
        // https://github.com/WoltLab/WCF/issues/4965
        \define('ARTICLE_ENABLE_VISIT_TRACKING', 1);

        // The option for the legacy cookie banner was removed in 6.0.
        \define('MODULE_COOKIE_POLICY_PAGE', 0);

        // These options for the legacy google map implementation were removed in 6.0.
        \define('GOOGLE_MAPS_TYPE', 'hybrid');
        \define('GOOGLE_MAPS_ENABLE_SCALE_CONTROL', 0);
        \define('GOOGLE_MAPS_ENABLE_DRAGGING', 1);
        \define('GOOGLE_MAPS_ENABLE_SCROLL_WHEEL_ZOOM', 0);
        \define('GOOGLE_MAPS_ENABLE_DOUBLE_CLICK_ZOOM', 1);
        \define('GOOGLE_MAPS_ACCESS_USER_LOCATION', 1);
    }

    /**
     * Starts the session system.
     */
    protected function initSession(): void
    {
        $factory = new SessionFactory();
        $factory->load();

        self::$sessionObj = SessionHandler::getInstance();
    }

    /**
     * Initialises the language engine.
     */
    protected function initLanguage(): void
    {
        if (isset($_GET['l']) && !self::getUser()->userID) {
            self::getSession()->setLanguageID(\intval($_GET['l']));
        }

        // get language
        self::$languageObj = LanguageFactory::getInstance()->getUserLanguage(self::getSession()->getLanguageID());
    }

    /**
     * Initialises the template engine.
     */
    protected function initTPL(): void
    {
        self::$tplObj = TemplateEngine::getInstance();
        self::getTPL()->setLanguageID(self::getLanguage()->languageID);
        $this->assignDefaultTemplateVariables();

        $this->initStyle();
    }

    /**
     * Initializes the user's style.
     */
    protected function initStyle(): void
    {
        if (self::getSession()->getUser()->userID) {
            $styleID = self::getSession()->getUser()->styleID ?: 0;
        } else {
            $styleID = self::getSession()->getVar('styleID') ?: 0;
        }

        $styleHandler = StyleHandler::getInstance();
        $styleHandler->changeStyle($styleID);
    }

    /**
     * Initializes applications.
     */
    protected function initApplications(): void
    {
        // step 1) load all applications
        $loadedApplications = [];

        // register WCF as application
        self::$applications['wcf'] = ApplicationHandler::getInstance()->getApplicationByID(1);

        if (!\class_exists(WCFACP::class, false)) {
            static::getTPL()->assign('baseHref', self::$applications['wcf']->getPageURL());
        }

        // start main application
        $application = ApplicationHandler::getInstance()->getActiveApplication();
        if ($application->packageID != 1) {
            $loadedApplications[] = $this->loadApplication($application);

            // register primary application
            $abbreviation = ApplicationHandler::getInstance()->getAbbreviation($application->packageID);
            self::$applications[$abbreviation] = $application;
        }

        // start dependent applications
        $applications = ApplicationHandler::getInstance()->getDependentApplications();
        foreach ($applications as $application) {
            if ($application->packageID == 1) {
                // ignore WCF
                continue;
            } elseif ($application->isTainted) {
                // ignore apps flagged for uninstallation
                continue;
            }

            $loadedApplications[] = $this->loadApplication($application, true);
        }

        // step 2) run each application
        if (!\class_exists('wcf\system\WCFACP', false)) {
            /** @var IApplication $application */
            foreach ($loadedApplications as $application) {
                $application->__run();
            }
        }
    }

    /**
     * Loads an application.
     *
     * @return  IApplication
     * @throws  SystemException
     */
    protected function loadApplication(Application $application, bool $isDependentApplication = false)
    {
        $package = PackageCache::getInstance()->getPackage($application->packageID);
        // package cache might be outdated
        if ($package === null) {
            $package = new Package($application->packageID);

            // package cache is outdated, discard cache
            if ($package->packageID) {
                PackageEditor::resetCache();
            } else {
                // package id is invalid
                throw new SystemException("application identified by package id '" . $application->packageID . "' is unknown");
            }
        }

        $abbreviation = ApplicationHandler::getInstance()->getAbbreviation($application->packageID);
        $packageDir = FileUtil::getRealPath(WCF_DIR . $package->packageDir);
        self::$autoloadDirectories[$abbreviation] = $packageDir . 'lib/';

        $className = $abbreviation . '\system\\' . \strtoupper($abbreviation) . 'Core';

        // class was not found, possibly the app was moved, but `packageDir` has not been adjusted
        if (!\class_exists($className)) {
            $coreApp = ApplicationHandler::getInstance()->getApplicationByID(1);

            // resolve the relative path and use it to construct the autoload directory
            $relativePath = FileUtil::getRelativePath($coreApp->domainPath, $application->domainPath);
            if ($relativePath !== './') {
                $packageDir = FileUtil::getRealPath(WCF_DIR . $relativePath);
                self::$autoloadDirectories[$abbreviation] = $packageDir . 'lib/';

                if (\class_exists($className)) {
                    // the class can now be found, update the `packageDir` value
                    (new PackageEditor($package))->update(['packageDir' => $relativePath]);
                }
            }
        }

        if (\class_exists($className) && \is_subclass_of($className, IApplication::class)) {
            // include config file
            $configPath = $packageDir . PackageInstallationDispatcher::CONFIG_FILE;
            if (!\file_exists($configPath)) {
                Package::writeConfigFile($package->packageID);
            }

            if (\file_exists($configPath)) {
                require_once($configPath);
            } else {
                throw new SystemException('Unable to load configuration for ' . $package->package);
            }

            if (\class_exists('wcf\system\WCFACP', false)) {
                // In acp we need to load the application and path into TemplateEngine
                TemplateEngine::getInstance()->addApplication($abbreviation, $packageDir . 'templates/');
            } else {
                // add template path and abbreviation
                static::getTPL()->addApplication($abbreviation, $packageDir . 'templates/');
            }
            EmailTemplateEngine::getInstance()->addApplication($abbreviation, $packageDir . 'templates/');

            // init application and assign it as template variable
            self::$applicationObjects[$application->packageID] = \call_user_func([$className, 'getInstance']);
            static::getTPL()->assign('__' . $abbreviation, self::$applicationObjects[$application->packageID]);
            EmailTemplateEngine::getInstance()->assign(
                '__' . $abbreviation,
                self::$applicationObjects[$application->packageID]
            );
        } else {
            unset(self::$autoloadDirectories[$abbreviation]);
            throw new SystemException("Unable to run '" . $package->package . "', '" . $className . "' is missing or does not implement '" . IApplication::class . "'.");
        }

        // register template path in ACP
        if (\class_exists('wcf\system\WCFACP', false)) {
            static::getTPL()->addApplication($abbreviation, $packageDir . 'acp/templates/');
        } elseif (!$isDependentApplication) {
            // assign base tag
            static::getTPL()->assign('baseHref', $application->getPageURL());
        }

        // register application
        self::$applications[$abbreviation] = $application;

        return self::$applicationObjects[$application->packageID];
    }

    /**
     * Returns the corresponding application object. Does not support the 'wcf' pseudo application.
     *
     * @return  IApplication
     */
    public static function getApplicationObject(Application $application)
    {
        return self::$applicationObjects[$application->packageID] ?? null;
    }

    /**
     * Returns the invoked application.
     *
     * @since   3.1
     */
    public static function getActiveApplication(): Application
    {
        return ApplicationHandler::getInstance()->getActiveApplication();
    }

    /**
     * Loads an application on runtime, do not use this outside the package installation.
     */
    public static function loadRuntimeApplication(int $packageID): void
    {
        $package = new Package($packageID);
        $application = new Application($packageID);

        $abbreviation = Package::getAbbreviation($package->package);
        $packageDir = FileUtil::getRealPath(WCF_DIR . $package->packageDir);
        self::$autoloadDirectories[$abbreviation] = $packageDir . 'lib/';
        self::$applications[$abbreviation] = $application;
        self::getTPL()->addApplication($abbreviation, $packageDir . 'acp/templates/');
    }

    /**
     * Initializes core object cache.
     */
    protected function initCoreObjects(): void
    {
        // ignore core objects if installing WCF
        if (PACKAGE_ID == 0) {
            return;
        }

        self::$coreObjectCache = CoreObjectCacheBuilder::getInstance()->getData();
    }

    /**
     * Assigns some default variables to the template engine.
     */
    protected function assignDefaultTemplateVariables(): void
    {
        $wcf = $this;

        if (ENABLE_ENTERPRISE_MODE) {
            $wcf = new TemplateScriptingCore($wcf);
        }

        self::getTPL()->registerPrefilter(['event', 'hascontent', 'lang', 'jsphrase', 'jslang', 'csrfToken', 'icon']);
        self::getTPL()->assign([
            '__wcf' => $wcf,
        ]);

        $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
        // Execute background queue in this request, if it was requested and AJAX isn't used.
        if (!$isAjax) {
            if (self::getSession()->getVar('forceBackgroundQueuePerform')) {
                self::getTPL()->assign([
                    'forceBackgroundQueuePerform' => true,
                ]);

                self::getSession()->unregister('forceBackgroundQueuePerform');
            }
        }

        EmailTemplateEngine::getInstance()->registerPrefilter(['event', 'hascontent', 'lang', 'jslang']);
        EmailTemplateEngine::getInstance()->assign([
            '__wcf' => $wcf,
        ]);
    }

    /**
     * Wrapper for the getter methods of this class.
     *
     * @return  mixed       value
     * @throws  SystemException
     */
    public function __get(string $name)
    {
        $method = 'get' . \ucfirst($name);
        if (\method_exists($this, $method)) {
            return $this->{$method}();
        }

        throw new SystemException("method '" . $method . "' does not exist in class WCF");
    }

    /**
     * Returns true if current application (WCF) is treated as active and was invoked directly.
     */
    public function isActiveApplication(): bool
    {
        return ApplicationHandler::getInstance()->getActiveApplication()->packageID == 1;
    }

    /**
     * Changes the active language.
     */
    final public static function setLanguage(int $languageID): void
    {
        if (!$languageID || LanguageFactory::getInstance()->getLanguage($languageID) === null) {
            $languageID = LanguageFactory::getInstance()->getDefaultLanguageID();
        }

        self::$languageObj = LanguageFactory::getInstance()->getLanguage($languageID);

        // the template engine may not be available yet, usually happens when
        // changing the user (and thus the language id) during session init
        if (self::$tplObj !== null) {
            self::getTPL()->setLanguageID(self::getLanguage()->languageID);
            EmailTemplateEngine::getInstance()->setLanguageID(self::getLanguage()->languageID);
        }
    }

    /**
     * Autoloads classes within the application directories.
     */
    final public static function autoload(string $className): void
    {
        $className = \strtr($className, '\\', '/');
        if (($slashPos = \strpos($className, '/')) !== null) {
            $applicationPrefix = \substr($className, 0, $slashPos);
            if (isset(self::$autoloadDirectories[$applicationPrefix])) {
                $classPath = self::$autoloadDirectories[$applicationPrefix] . \substr($className, $slashPos + 1) . '.class.php';

                // PHP will implicitly check if the file exists when including it, which means that we can save a
                // redundant syscall/fs access by not checking for existence ourselves. Do not use require_once()!
                @include_once($classPath);
            }
        }
    }

    /**
     * Checks the class name casing after autoloading and does not suppress
     * errors during loading.
     */
    final public static function autoloadDebug(string $className): void
    {
        $originalClassName = $className;

        // This is copy and pasted from self::autoload(). The $classPath calculation
        // logic cannot be moved into a shared function, because it
        // measurably reduced autoloader performance.
        $className = \strtr($className, '\\', '/');
        if (($slashPos = \strpos($className, '/')) !== null) {
            $applicationPrefix = \substr($className, 0, $slashPos);
            if (isset(self::$autoloadDirectories[$applicationPrefix])) {
                $classPath = self::$autoloadDirectories[$applicationPrefix] . \substr($className, $slashPos + 1) . '.class.php';

                if (\file_exists($classPath)) {
                    require_once($classPath);

                    $reflection = new \ReflectionClass($originalClassName);

                    if ($originalClassName !== $reflection->getName()) {
                        throw new \Exception(\sprintf(
                            "Loaded class '%s' with mismatching case '%s'. This will cause issues on case-sensitive file systems.",
                            $reflection->getName(),
                            $originalClassName,
                        ));
                    }
                }
            }
        }
    }

    /**
     * @inheritDoc
     */
    final public function __call(string $name, array $arguments)
    {
        // bug fix to avoid php crash, see http://bugs.php.net/bug.php?id=55020
        if (!\method_exists($this, $name)) {
            return self::__callStatic($name, $arguments);
        }

        throw new \BadMethodCallException("Call to undefined method WCF::{$name}().");
    }

    /**
     * Returns dynamically loaded core objects.
     *
     * @param array $arguments
     * @return  object
     * @throws  SystemException
     */
    final public static function __callStatic(string $name, array $arguments)
    {
        $className = \preg_replace('~^get~', '', $name);

        if (isset(self::$coreObject[$className])) {
            return self::$coreObject[$className];
        }

        $objectName = self::getCoreObject($className);
        if ($objectName === null) {
            throw new SystemException("Core object '" . $className . "' is unknown.");
        }

        if (\class_exists($objectName)) {
            if (!\is_subclass_of($objectName, SingletonFactory::class)) {
                throw new ParentClassException($objectName, SingletonFactory::class);
            }

            self::$coreObject[$className] = \call_user_func([$objectName, 'getInstance']);

            return self::$coreObject[$className];
        }
    }

    /**
     * Searches for cached core object definition.
     *
     * @return  string|null
     */
    final protected static function getCoreObject(string $className)
    {
        return self::$coreObjectCache[$className] ?? null;
    }

    /**
     * Returns true if the debug mode is enabled, otherwise false.
     */
    public static function debugModeIsEnabled(bool $ignoreACP = false): bool
    {
        // ACP override
        if (!$ignoreACP && self::$overrideDebugMode) {
            return true;
        } elseif (\defined('ENABLE_DEBUG_MODE') && ENABLE_DEBUG_MODE) {
            return true;
        }

        return false;
    }

    /**
     * Returns true if benchmarking is enabled, otherwise false.
     */
    public static function benchmarkIsEnabled(): bool
    {
        // benchmarking is enabled by default
        if (!\defined('ENABLE_BENCHMARK') || ENABLE_BENCHMARK) {
            return true;
        }

        return false;
    }

    /**
     * Returns domain path for given application.
     */
    public static function getPath(string $abbreviation = 'wcf'): string
    {
        // workaround during WCFSetup
        if (!PACKAGE_ID) {
            return '../';
        }

        if (!isset(self::$applications[$abbreviation])) {
            $abbreviation = 'wcf';
        }

        return self::$applications[$abbreviation]->getPageURL();
    }

    /**
     * @deprecated 6.0 - This was a workaround for multi-domain setups.
     */
    public static function getActivePath(): string
    {
        if (!PACKAGE_ID) {
            return self::getPath();
        }

        // We cannot rely on the ApplicationHandler's `getActiveApplication()` because
        // it uses the requested controller to determine the namespace. However, starting
        // with WoltLab Suite 5.2, system pages can be virtually assigned to a different
        // app, resolving against the target app without changing the namespace.
        return self::getPath(ApplicationHandler::getInstance()->getAbbreviation(PACKAGE_ID));
    }

    /**
     * @deprecated 5.5 - Put a '#' followed by the fragment as the anchor's href. Make sure to |rawurlencode any variables that may contain special characters.
     */
    public function getAnchor($fragment): string
    {
        return StringUtil::encodeHTML(self::getRequestURI() . '#' . $fragment);
    }

    /**
     * Returns the currently active page or null if unknown.
     */
    public static function getActivePage(): ?Page
    {
        return RequestHandler::getInstance()->getActivePage();
    }

    /**
     * Returns the currently active request.
     */
    public static function getActiveRequest(): ?Request
    {
        return RequestHandler::getInstance()->getActiveRequest();
    }

    /**
     * Returns the URI of the current page.
     */
    public static function getRequestURI(): string
    {
        return \preg_replace(
            '~^(https?://[^/]+)(?:/.*)?$~',
            '$1',
            self::getTPL()->get('baseHref')
        ) . $_SERVER['REQUEST_URI'];
    }

    /**
     * Resets Zend Opcache cache if installed and enabled.
     */
    public static function resetZendOpcache(string $script = ''): void
    {
        if (self::$zendOpcacheEnabled === null) {
            self::$zendOpcacheEnabled = false;

            if (\extension_loaded('Zend Opcache') && \ini_get('opcache.enable')) {
                self::$zendOpcacheEnabled = true;
            }
        }

        if (self::$zendOpcacheEnabled) {
            if (empty($script)) {
                \opcache_reset();
            } else {
                \opcache_invalidate($script, true);
            }
        }
    }

    /**
     * Returns style handler.
     */
    public function getStyleHandler(): StyleHandler
    {
        return StyleHandler::getInstance();
    }

    /**
     * Returns box handler.
     *
     * @since   3.0
     */
    public function getBoxHandler(): BoxHandler
    {
        return BoxHandler::getInstance();
    }

    /**
     * Returns number of available updates.
     */
    public function getAvailableUpdates(): int
    {
        $data = PackageUpdateCacheBuilder::getInstance()->getData();

        return $data['updates'];
    }

    /**
     * Returns a 8 character prefix for editor autosave.
     */
    public function getAutosavePrefix(): string
    {
        return \substr(\sha1(\preg_replace('~^https~', 'http', self::getPath())), 0, 8);
    }

    /**
     * @deprecated 6.0 Use ActiveStyle::getRelativeFavicon() directly.
     */
    public function getFavicon(): string
    {
        $favicon = StyleHandler::getInstance()->getStyle()->getRelativeFavicon();

        return self::getPath() . $favicon;
    }

    /**
     * @deprecated 6.0 This method always returns true.
     */
    public function useDesktopNotifications(): bool
    {
        return true;
    }

    /**
     * Returns a random value that is derived from the given scope and a randomly
     * generated value that remains constant for this request.
     *
     * @since 6.0
     */
    final public static function getRequestNonce(string $scope): string
    {
        static $key = null;

        if ($key === null) {
            $key = \random_bytes(16);
        }

        if (PACKAGE_ID) {
            $prefix = \WCF_UUID . ':' . self::class . ':';
        } else {
            $prefix = '';
        }

        return $scope . '_' . \hash_hmac('md5', $prefix . $scope, $key);
    }

    /**
     * Returns true if currently active request represents the landing page.
     */
    public static function isLandingPage(): bool
    {
        if (self::getActiveRequest() === null) {
            return false;
        }

        return self::getActiveRequest()->isLandingPage();
    }

    /**
     * Checks recursively that the most important system files of `com.woltlab.wcf` are writable.
     *
     * @throws  \RuntimeException   if any relevant file or directory is not writable
     */
    public static function checkWritability(): void
    {
        $nonWritablePaths = [];

        $nonRecursiveDirectories = [
            '',
            'acp/',
            'tmp/',
        ];
        foreach ($nonRecursiveDirectories as $directory) {
            $path = WCF_DIR . $directory;
            if ($path === 'tmp/' && !\is_dir($path)) {
                continue;
            }

            if (!\is_writable($path)) {
                $nonWritablePaths[] = FileUtil::getRelativePath($_SERVER['DOCUMENT_ROOT'], $path);
                continue;
            }

            DirectoryUtil::getInstance($path, false)
                ->executeCallback(static function ($file, \SplFileInfo $fileInfo) use (&$nonWritablePaths) {
                    if ($fileInfo instanceof \DirectoryIterator) {
                        if (!\is_writable($fileInfo->getPath())) {
                            $nonWritablePaths[] = FileUtil::getRelativePath(
                                $_SERVER['DOCUMENT_ROOT'],
                                $fileInfo->getPath()
                            );
                        }
                    } elseif (!\is_writable($fileInfo->getRealPath())) {
                        $nonWritablePaths[] = FileUtil::getRelativePath(
                            $_SERVER['DOCUMENT_ROOT'],
                            $fileInfo->getPath()
                        ) . $fileInfo->getFilename();
                    }
                });
        }

        $recursiveDirectories = [
            'acp/js/',
            'acp/style/',
            'acp/templates/',
            'acp/uninstall/',
            'js/',
            'lib/',
            'log/',
            'style/',
            'templates/',
        ];
        foreach ($recursiveDirectories as $directory) {
            $path = WCF_DIR . $directory;

            if (!\is_writable($path)) {
                $nonWritablePaths[] = FileUtil::getRelativePath($_SERVER['DOCUMENT_ROOT'], $path);
                continue;
            }

            DirectoryUtil::getInstance($path)
                ->executeCallback(static function ($file, \SplFileInfo $fileInfo) use (&$nonWritablePaths) {
                    if ($fileInfo instanceof \DirectoryIterator) {
                        if (!\is_writable($fileInfo->getPath())) {
                            $nonWritablePaths[] = FileUtil::getRelativePath(
                                $_SERVER['DOCUMENT_ROOT'],
                                $fileInfo->getPath()
                            );
                        }
                    } elseif (!\is_writable($fileInfo->getRealPath())) {
                        $nonWritablePaths[] = FileUtil::getRelativePath(
                            $_SERVER['DOCUMENT_ROOT'],
                            $fileInfo->getPath()
                        ) . $fileInfo->getFilename();
                    }
                });
        }

        if (!empty($nonWritablePaths)) {
            $maxPaths = 10;
            throw new \RuntimeException('The following paths are not writable: ' . \implode(
                ',',
                \array_slice(
                    $nonWritablePaths,
                    0,
                    $maxPaths
                )
            ) . (\count($nonWritablePaths) > $maxPaths ? ',' . StringUtil::HELLIP : ''));
        }
    }
}
