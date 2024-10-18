<?php

namespace wcf\system;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Psr\Http\Message\ResponseInterface;
use wcf\data\language\LanguageEditor;
use wcf\data\language\SetupLanguage;
use wcf\data\package\installation\queue\PackageInstallationQueueEditor;
use wcf\data\user\User;
use wcf\data\user\UserAction;
use wcf\system\cache\builder\LanguageCacheBuilder;
use wcf\system\database\Database;
use wcf\system\database\exception\DatabaseException;
use wcf\system\database\MySQLDatabase;
use wcf\system\database\util\SQLParser;
use wcf\system\devtools\DevtoolsSetup;
use wcf\system\exception\SystemException;
use wcf\system\exception\UserInputException;
use wcf\system\image\adapter\GDImageAdapter;
use wcf\system\image\adapter\ImagickImageAdapter;
use wcf\system\io\Tar;
use wcf\system\language\LanguageFactory;
use wcf\system\package\PackageArchive;
use wcf\system\request\RouteHandler;
use wcf\system\session\ACPSessionFactory;
use wcf\system\session\SessionHandler;
use wcf\system\setup\Installer;
use wcf\system\setup\SetupFileHandler;
use wcf\system\template\SetupTemplateEngine;
use wcf\util\FileUtil;
use wcf\util\HeaderUtil;
use wcf\util\StringUtil;
use wcf\util\UserUtil;
use wcf\util\XML;

// define
\define('PACKAGE_ID', 0);
\define('CACHE_SOURCE_TYPE', 'disk');
\define('ENABLE_DEBUG_MODE', 1);
\define('ENABLE_BENCHMARK', 0);
\define('ENABLE_ENTERPRISE_MODE', 0);

/**
 * Executes the installation of the basic WCF systems.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class WCFSetup extends WCF
{
    /**
     * list of available languages
     * @var string[]
     */
    protected static $availableLanguages = [];

    /**
     * language code of selected installation language
     * @var string
     */
    protected static $selectedLanguageCode = 'en';

    /**
     * list of installed files
     * @var string[]
     */
    protected static $installedFiles = [];

    /**
     * indicates if developer mode is used to install
     * @var bool
     */
    protected static $developerMode = 0;

    /** @noinspection PhpMissingParentConstructorInspection */

    /**
     * Calls all init functions of the WCFSetup class and starts the setup process.
     */
    public function __construct()
    {
        @\set_time_limit(0);

        static::getDeveloperMode();
        static::getLanguageSelection();
        $this->initLanguage();
        $this->initTPL();

        $emitter = new SapiEmitter();
        $response = $this->dispatch();
        $response = HeaderUtil::withNoCacheHeaders($response);
        $response = $response->withHeader('x-frame-options', 'SAMEORIGIN');
        $emitter->emit($response);
    }

    /**
     * Sets the status of the developer mode.
     */
    protected static function getDeveloperMode(): void
    {
        if (isset($_GET['dev'])) {
            self::$developerMode = \intval($_GET['dev']);
        } elseif (isset($_POST['dev'])) {
            self::$developerMode = \intval($_POST['dev']);
        }
    }

    /**
     * Sets the selected language.
     */
    protected static function getLanguageSelection(): void
    {
        self::$availableLanguages = self::getAvailableLanguages();

        if (isset($_REQUEST['languageCode']) && isset(self::$availableLanguages[$_REQUEST['languageCode']])) {
            self::$selectedLanguageCode = $_REQUEST['languageCode'];
        } else {
            self::$selectedLanguageCode = LanguageFactory::getPreferredLanguage(
                \array_keys(self::$availableLanguages),
                self::$selectedLanguageCode
            );
        }
    }

    /**
     * Initialises the language engine.
     */
    protected function initLanguage(): void
    {
        self::$languageObj = new SetupLanguage(self::$selectedLanguageCode);
    }

    /**
     * Initialises the template engine.
     */
    protected function initTPL(): void
    {
        self::$tplObj = SetupTemplateEngine::getInstance();
        self::getTPL()->setLanguageID((self::$selectedLanguageCode == 'en' ? 0 : 1));
        self::getTPL()->setCompileDir(TMP_DIR);
        self::getTPL()->addApplication('wcf', TMP_DIR);
        self::getTPL()->assign([
            '__wcf' => $this,
            'tmpFilePrefix' => TMP_FILE_PREFIX,
            'languageCode' => self::$selectedLanguageCode,
            'developerMode' => self::$developerMode,

            'setupAssets' => [
                'WCFSetup.css' => \sprintf(
                    'data:text/css;base64,%s',
                    \base64_encode(\file_get_contents(TMP_DIR . 'install/files/acp/style/setup/WCFSetup.css'))
                ),
                'woltlabSuite.png' => \sprintf(
                    'data:image/png;base64,%s',
                    \base64_encode(\file_get_contents(TMP_DIR . 'install/files/acp/images/woltlabSuite.png'))
                ),
            ],
        ]);
    }

    /**
     * Returns all languages from WCFSetup.tar.gz.
     *
     * @return  string[]
     */
    protected static function getAvailableLanguages(): array
    {
        $languages = [];
        foreach (\glob(TMP_DIR . 'setup/lang/*.xml') as $file) {
            $xml = new XML();
            $xml->load($file);
            $languageCode = LanguageEditor::readLanguageCodeFromXML($xml);
            $languageName = LanguageEditor::readLanguageNameFromXML($xml);

            $languages[$languageCode] = $languageName;
        }

        // sort languages by language name
        \asort($languages);

        return $languages;
    }

    /**
     * Calculates the current state of the progress bar.
     */
    protected function calcProgress(int $currentStep): void
    {
        $lastStep = \intval(\file_get_contents(\TMP_DIR . 'lastStep'));
        if ($lastStep > $currentStep) {
            throw new \Exception('Refusing to step back to a previous step.');
        }
        if ($lastStep !== $currentStep - 1 && $lastStep !== $currentStep) {
            throw new \Exception('Refusing to skip a step.');
        }

        \file_put_contents(\TMP_DIR . 'lastStep', $currentStep);

        // calculate progress
        $progress = \round((100 / 22) * ++$currentStep, 0);
        self::getTPL()->assign(['progress' => $progress]);
    }

    /**
     * Throws an exception if it appears that the 'unzipFiles' step already ran.
     */
    protected function assertNotUnzipped(): void
    {
        if (
            \is_file(INSTALL_SCRIPT_DIR . 'lib/system/WCF.class.php')
            || \is_file(INSTALL_SCRIPT_DIR . 'global.php')
        ) {
            throw new \Exception(
                'Target directory seems to be an existing installation of WoltLab Suite Core, refusing to continue.'
            );
        }
    }

    /**
     * Executes the setup steps.
     */
    protected function dispatch(): ResponseInterface
    {
        // get current step
        if (isset($_POST['step'])) {
            $step = $_POST['step'];
        } else {
            $step = 'selectSetupLanguage';
        }

        \header('set-cookie: wcfsetup_cookietest=' . TMP_FILE_PREFIX . '; domain=' . \str_replace(
            RouteHandler::getProtocol(),
            '',
            RouteHandler::getHost()
        ) . (RouteHandler::secureConnection() ? '; secure' : ''));

        // execute current step
        switch ($step) {
            case 'selectSetupLanguage':
                $this->calcProgress(0);
                $this->assertNotUnzipped();

                return $this->selectSetupLanguage();

            case 'showLicense':
                $this->calcProgress(1);
                $this->assertNotUnzipped();

                return $this->showLicense();

            case 'showSystemRequirements':
                $this->calcProgress(2);
                $this->assertNotUnzipped();

                return $this->showSystemRequirements();

            case 'configureDB':
                $this->calcProgress(3);
                $this->assertNotUnzipped();

                return $this->configureDB();

            case 'createDB':
                $currentStep = 4;
                if (isset($_POST['offset'])) {
                    $currentStep += \intval($_POST['offset']);
                }

                $this->calcProgress($currentStep);
                $this->assertNotUnzipped();

                return $this->createDB();

            case 'unzipFiles':
                $this->calcProgress(18);
                $this->assertNotUnzipped();

                return $this->unzipFiles();

            case 'installLanguage':
                $this->calcProgress(19);

                return $this->installLanguage();

            case 'createUser':
                $this->calcProgress(20);

                return $this->createUser();

            case 'installPackages':
                $this->calcProgress(21);

                return $this->installPackages();
        }
    }

    /**
     * Shows the first setup page.
     */
    protected function selectSetupLanguage(): ResponseInterface
    {
        if (self::$developerMode) {
            return $this->gotoNextStep('showLicense');
        }

        return new HtmlResponse(
            WCF::getTPL()->fetchStream(
                'stepSelectSetupLanguage',
                'wcf',
                [
                    'availableLanguages' => self::$availableLanguages,
                    'nextStep' => 'showLicense',
                ]
            )
        );
    }

    /**
     * Shows the license agreement.
     */
    protected function showLicense(): ResponseInterface
    {
        if (self::$developerMode) {
            return $this->gotoNextStep('showSystemRequirements');
        }

        $missingAcception = false;

        if (isset($_POST['send'])) {
            if (isset($_POST['accepted'])) {
                return $this->gotoNextStep('showSystemRequirements');
            } else {
                $missingAcception = true;
            }
        }

        if (\file_exists(TMP_DIR . 'setup/license/license_' . self::$selectedLanguageCode . '.txt')) {
            $license = \file_get_contents(TMP_DIR . 'setup/license/license_' . self::$selectedLanguageCode . '.txt');
        } else {
            $license = \file_get_contents(TMP_DIR . 'setup/license/license_en.txt');
        }

        return new HtmlResponse(
            WCF::getTPL()->fetchStream(
                'stepShowLicense',
                'wcf',
                [
                    'license' => $license,
                    'missingAcception' => $missingAcception,
                    'nextStep' => 'showLicense',
                ]
            )
        );
    }

    /**
     * Shows the system requirements.
     */
    protected function showSystemRequirements(): ResponseInterface
    {
        $phpVersionLowerBound = '8.1.2';
        $phpVersionUpperBound = '8.3.x';
        $system = [];

        // php version
        $system['phpVersion']['value'] = \PHP_VERSION;
        $comparePhpVersion = \preg_replace('/^(\d+\.\d+\.\d+).*$/', '\\1', $system['phpVersion']['value']);
        $system['phpVersion']['result'] = \version_compare($comparePhpVersion, $phpVersionLowerBound, '>=')
            && \version_compare($comparePhpVersion, \str_replace('x', '999', $phpVersionUpperBound), '<=');

        $system['x64']['result'] = \PHP_INT_SIZE == 8;

        // sql
        $system['sql']['result'] = MySQLDatabase::isSupported();

        // upload_max_filesize
        $system['uploadMaxFilesize']['value'] = \min(\ini_get('upload_max_filesize'), \ini_get('post_max_size'));
        $system['uploadMaxFilesize']['result'] = (\intval($system['uploadMaxFilesize']['value']) > 0);

        // graphics library
        $system['graphicsLibrary']['result'] = false;
        $system['graphicsLibrary']['value'] = '';
        if (
            ImagickImageAdapter::isSupported()
            && ImagickImageAdapter::supportsAnimatedGIFs(ImagickImageAdapter::getVersion())
            && ImagickImageAdapter::supportsWebp()
        ) {
            $system['graphicsLibrary'] = [
                'result' => true,
                'value' => 'ImageMagick',
            ];
        } elseif (GDImageAdapter::isSupported()) {
            $system['graphicsLibrary'] = [
                'result' => GDImageAdapter::supportsWebp(),
                'value' => 'GD Library',
            ];
        }

        // memory limit
        $system['memoryLimit']['value'] = FileUtil::getMemoryLimit();
        $system['memoryLimit']['result'] = $system['memoryLimit']['value'] === -1 || $system['memoryLimit']['value'] >= 128 * 1024 * 1024;

        // openssl extension
        $system['openssl']['result'] = \extension_loaded('openssl');

        // curl
        $system['curl']['result'] = \extension_loaded('curl');

        // misconfigured reverse proxy / cookies
        $system['hostname']['result'] = true;
        [$system['hostname']['value']] = \explode(':', $_SERVER['HTTP_HOST'], 2);
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $refererHostname = \parse_url($_SERVER['HTTP_REFERER'], \PHP_URL_HOST);
            $system['hostname']['result'] = $_SERVER['HTTP_HOST'] == $refererHostname;
        }

        $system['cookie']['result'] = !empty($_COOKIE['wcfsetup_cookietest']) && $_COOKIE['wcfsetup_cookietest'] == TMP_FILE_PREFIX;

        $system['tls']['result'] = RouteHandler::secureContext();

        foreach ($system as $result) {
            if (!$result['result']) {
                return new HtmlResponse(
                    WCF::getTPL()->fetchStream(
                        'stepShowSystemRequirements',
                        'wcf',
                        [
                            'system' => $system,
                            'nextStep' => 'configureDB',
                            'phpVersionLowerBound' => $phpVersionLowerBound,
                            'phpVersionUpperBound' => $phpVersionUpperBound,
                        ]
                    )
                );
            }
        }

        // If all system requirements are met, directly go to next step.
        return $this->gotoNextStep('configureDB');
    }

    /**
     * Shows the page for configuring the database connection.
     */
    protected function configureDB(): ResponseInterface
    {
        $attemptConnection = isset($_POST['send']);

        if (self::$developerMode && isset($_ENV['WCFSETUP_DBHOST'])) {
            $dbHost = $_ENV['WCFSETUP_DBHOST'];
            $dbUser = $_ENV['WCFSETUP_DBUSER'];
            $dbPassword = $_ENV['WCFSETUP_DBPASSWORD'];
            $dbName = $_ENV['WCFSETUP_DBNAME'];

            $attemptConnection = true;
        } elseif (self::$developerMode && ($config = DevtoolsSetup::getInstance()->getDatabaseConfig()) !== null) {
            $dbHost = $config['host'];
            $dbUser = $config['username'];
            $dbPassword = $config['password'];
            $dbName = $config['dbName'];

            if ($config['auto']) {
                $attemptConnection = true;
            }
        } else {
            $dbHost = 'localhost';
            $dbUser = 'root';
            $dbPassword = '';
            $dbName = 'wcf';
        }

        if ($attemptConnection) {
            if (isset($_POST['dbHost'])) {
                $dbHost = $_POST['dbHost'];
            }
            if (isset($_POST['dbUser'])) {
                $dbUser = $_POST['dbUser'];
            }
            if (isset($_POST['dbPassword'])) {
                $dbPassword = $_POST['dbPassword'];
            }
            if (isset($_POST['dbName'])) {
                $dbName = $_POST['dbName'];
            }

            // get port
            $dbHostWithoutPort = $dbHost;
            $dbPort = 0;
            if (\preg_match('/^(.+?):(\d+)$/', $dbHost, $match)) {
                $dbHostWithoutPort = $match[1];
                $dbPort = \intval($match[2]);
            }

            // test connection
            try {
                // check connection data
                /** @var \wcf\system\database\Database $db */
                try {
                    $db = new MySQLDatabase(
                        $dbHostWithoutPort,
                        $dbUser,
                        $dbPassword,
                        $dbName,
                        $dbPort,
                        true,
                        !!(self::$developerMode)
                    );
                } catch (DatabaseException $e) {
                    switch ($e->getPrevious()->getCode()) {
                        case 1049: // try to manually create non-existing database
                            try {
                                $db = new MySQLDatabase(
                                    $dbHostWithoutPort,
                                    $dbUser,
                                    $dbPassword,
                                    $dbName,
                                    $dbPort,
                                    true,
                                    true
                                );
                            } catch (DatabaseException $e) {
                                throw new SystemException("Unknown database '{$dbName}'. Please create the database manually.");
                            }

                            break;

                        case 1115: // work-around for older MySQL versions that don't know utf8mb4
                            throw new SystemException("Insufficient MySQL version. Version '8.0.30' or greater is needed.");
                            break;

                        default:
                            throw $e;
                    }
                }

                // check sql version
                $sqlVersion = $db->getVersion();
                $compareSQLVersion = \preg_replace('/^(\d+\.\d+\.\d+).*$/', '\\1', $sqlVersion);
                if (\stripos($sqlVersion, 'MariaDB')) {
                    if (!(\version_compare($compareSQLVersion, '10.5.15') >= 0)) {
                        throw new SystemException("Insufficient MariaDB version '" . $compareSQLVersion . "'. Version '10.5.15' or greater is needed.");
                    }
                } else {
                    if (!(\version_compare($compareSQLVersion, '8.0.30') >= 0)) {
                        throw new SystemException("Insufficient MySQL version '" . $compareSQLVersion . "'. Version '8.0.30' or greater is needed.");
                    }
                }

                // check innodb support
                $sql = "SHOW ENGINES";
                $statement = $db->prepare($sql);
                $statement->execute();
                $hasInnoDB = false;
                while ($row = $statement->fetchArray()) {
                    if ($row['Engine'] == 'InnoDB' && \in_array($row['Support'], ['DEFAULT', 'YES'])) {
                        $hasInnoDB = true;
                        break;
                    }
                }

                if (!$hasInnoDB) {
                    throw new SystemException("Support for InnoDB is missing.");
                }

                // check for PHP's MySQL native driver
                $sql = "SELECT 1";
                $statement = $db->prepare($sql);
                $statement->execute();
                // MySQL native driver understands data types, libmysqlclient does not
                if ($statement->fetchSingleColumn() !== 1) {
                    throw new SystemException("MySQL Native Driver is not being used for database communication.");
                }

                // check for table conflicts
                $conflictedTables = $this->getConflictedTables($db);

                if (empty($conflictedTables)) {
                    // connection successfully established
                    // write configuration to config.inc.php
                    \file_put_contents(
                        WCF_DIR . 'config.inc.php',
                        \sprintf(
                            <<<'CONFIG'
                            <?php
                            $dbHost = %s;
                            $dbPort = %s;
                            $dbUser = %s;
                            $dbPassword = %s;
                            $dbName = %s;
                            if (!defined('WCF_N')) define('WCF_N', 1);
                            CONFIG,
                            \var_export($dbHostWithoutPort, true),
                            \var_export($dbPort, true),
                            \var_export($dbUser, true),
                            \var_export($dbPassword, true),
                            \var_export($dbName, true)
                        )
                    );

                    return $this->gotoNextStep('createDB');
                } else {
                    // show configure template again
                    WCF::getTPL()->assign(['conflictedTables' => $conflictedTables]);
                }
            } catch (SystemException $e) {
                WCF::getTPL()->assign(['exception' => $e]);
            }
        }

        return new HtmlResponse(
            WCF::getTPL()->fetchStream(
                'stepConfigureDB',
                'wcf',
                [
                    'dbHost' => $dbHost,
                    'dbUser' => $dbUser,
                    'dbPassword' => $dbPassword,
                    'dbName' => $dbName,
                    'nextStep' => 'configureDB',
                ]
            )
        );
    }

    /**
     * Checks if in the chosen database are tables in conflict with the wcf tables
     * which will be created in the next step.
     *
     * @return  list<string>    list of already existing tables
     */
    protected function getConflictedTables(Database $db): array
    {
        // get content of the sql structure file
        $sql = \file_get_contents(TMP_DIR . 'setup/db/install.sql');

        // get all tablenames which should be created
        \preg_match_all("%CREATE\\s+TABLE\\s+(\\w+)%", $sql, $matches);

        // get all installed tables from chosen database
        $existingTables = $db->getEditor()->getTableNames();

        // check if existing tables are in conflict with wcf tables
        $conflictedTables = [];
        foreach ($existingTables as $existingTableName) {
            foreach ($matches[1] as $wcfTableName) {
                if ($existingTableName == $wcfTableName) {
                    $conflictedTables[] = $wcfTableName;
                }
            }
        }

        return $conflictedTables;
    }

    /**
     * Creates the database structure of the wcf.
     */
    protected function createDB(): ResponseInterface
    {
        $this->initDB();

        // get content of the sql structure file
        $sql = \file_get_contents(TMP_DIR . 'setup/db/install.sql');

        // split by offsets
        $sqlData = \explode('/* SQL_PARSER_OFFSET */', $sql);
        $offset = isset($_POST['offset']) ? \intval($_POST['offset']) : 0;
        if (!isset($sqlData[$offset])) {
            throw new SystemException("Offset for SQL parser is out of bounds, " . $offset . " was requested, but there are only " . \count($sqlData) . " sections");
        }
        $sql = $sqlData[$offset];

        // execute sql queries
        $parser = new SQLParser($sql);
        $parser->execute();

        // log sql queries
        \preg_match_all("~CREATE\\s+TABLE\\s+(\\w+)~i", $sql, $matches);

        $sql = "INSERT INTO wcf1_package_installation_sql_log
                            (packageID, sqlTable)
                VALUES      (?, ?)";
        $statement = self::getDB()->prepare($sql);
        foreach ($matches[1] as $tableName) {
            $statement->execute([1, $tableName]);
        }

        if ($offset < (\count($sqlData) - 1)) {
            WCF::getTPL()->assign([
                '__additionalParameters' => [
                    'offset' => $offset + 1,
                ],
            ]);

            return $this->gotoNextStep('createDB');
        } else {
            /*
             * Manually install PIPPackageInstallationPlugin since install.sql content is not escaped resulting
            * in different behaviour in MySQL and MSSQL. You SHOULD NOT move this into install.sql!
            */
            $sql = "INSERT INTO wcf1_package_installation_plugin
                                (packageID, pluginName, priority, className)
                    VALUES      (?, ?, ?, ?)";
            $statement = self::getDB()->prepare($sql);
            $statement->execute([
                1,
                'packageInstallationPlugin',
                1,
                'wcf\system\package\plugin\PIPPackageInstallationPlugin',
            ]);

            return $this->gotoNextStep('unzipFiles');
        }
    }

    /**
     * Unzips the files of the wcfsetup tar archive.
     */
    protected function unzipFiles(): ResponseInterface
    {
        $this->initDB();

        $fileHandler = new SetupFileHandler();
        new Installer(WCF_DIR, SETUP_FILE, $fileHandler, 'install/files/');

        // Create initial bootstrap.php including WCF's bootstrap script.
        \file_put_contents(
            WCF_DIR . 'lib/bootstrap.php',
            <<<'EOT'
            <?php

            return (function() {
                return [
                    require(__DIR__ . '/bootstrap/com.woltlab.wcf.php'),
                ];
            })();
            EOT
        );

        return $this->gotoNextStep('installLanguage');
    }

    /**
     * Installs the selected languages.
     */
    protected function installLanguage(): ResponseInterface
    {
        $this->initDB();

        $languageCodes = \array_keys(self::$availableLanguages);
        foreach ($languageCodes as $language) {
            // get language.xml file name
            $filename = TMP_DIR . 'install/lang/' . $language . '.xml';

            // check the file
            if (!\file_exists($filename)) {
                throw new SystemException("unable to find language file '" . $filename . "'");
            }

            // open the file
            $xml = new XML();
            $xml->load($filename);

            // import xml
            LanguageEditor::importFromXML($xml, 1);
        }

        // set default language
        $language = LanguageFactory::getInstance()->getLanguageByCode(
            \in_array(
                self::$selectedLanguageCode,
                $languageCodes
            ) ? self::$selectedLanguageCode : $languageCodes[0]
        );
        LanguageFactory::getInstance()->makeDefault($language->languageID);

        // rebuild language cache
        LanguageCacheBuilder::getInstance()->reset();

        return $this->gotoNextStep('createUser');
    }

    /**
     * Shows the page for creating the admin account.
     */
    protected function createUser(): ResponseInterface
    {
        $errorType = $errorField = $username = $email = $confirmEmail = $password = $confirmPassword = '';

        $username = '';
        $email = $confirmEmail = '';
        $password = $confirmPassword = '';

        if (isset($_POST['send']) || self::$developerMode) {
            if (isset($_POST['send'])) {
                if (isset($_POST['username'])) {
                    $username = StringUtil::trim($_POST['username']);
                }
                if (isset($_POST['email'])) {
                    $email = StringUtil::trim($_POST['email']);
                }
                if (isset($_POST['confirmEmail'])) {
                    $confirmEmail = StringUtil::trim($_POST['confirmEmail']);
                }
                if (isset($_POST['password'])) {
                    $password = $_POST['password'];
                }
                if (isset($_POST['confirmPassword'])) {
                    $confirmPassword = $_POST['confirmPassword'];
                }
            } else {
                $username = 'dev';
                $password = $confirmPassword = 'root';
                $email = $confirmEmail = 'wsc-developer-mode@example.com';
            }

            // error handling
            try {
                // username
                if (empty($username)) {
                    throw new UserInputException('username');
                }
                if (!UserUtil::isValidUsername($username)) {
                    throw new UserInputException('username', 'invalid');
                }

                // e-mail address
                if (empty($email)) {
                    throw new UserInputException('email');
                }
                if (!UserUtil::isValidEmail($email)) {
                    throw new UserInputException('email', 'invalid');
                }

                // confirm e-mail address
                if ($email != $confirmEmail) {
                    throw new UserInputException('confirmEmail', 'notEqual');
                }

                // password
                if (empty($password)) {
                    throw new UserInputException('password');
                }

                // confirm e-mail address
                if ($password != $confirmPassword) {
                    throw new UserInputException('confirmPassword', 'notEqual');
                }

                // no errors
                // init database connection
                $this->initDB();

                // get language id
                $languageID = 0;
                $sql = "SELECT  languageID
                        FROM    wcf1_language
                        WHERE   languageCode = ?";
                $statement = self::getDB()->prepare($sql);
                $statement->execute([self::$selectedLanguageCode]);
                $row = $statement->fetchArray();
                if (isset($row['languageID'])) {
                    $languageID = $row['languageID'];
                }

                if (!$languageID) {
                    $languageID = LanguageFactory::getInstance()->getDefaultLanguageID();
                }

                // create user
                $data = [
                    'data' => [
                        'userID' => 1,
                        'email' => $email,
                        'languageID' => $languageID,
                        'password' => $password,
                        'username' => $username,
                        'signature' => '',
                        'signatureEnableHtml' => 1,
                    ],
                    'groups' => [
                        1,
                        3,
                        4,
                    ],
                    'languages' => [
                        $languageID,
                    ],
                ];

                $userAction = new UserAction([], 'create', $data);
                $userAction->executeAction();

                return $this->gotoNextStep('installPackages');
            } catch (UserInputException $e) {
                $errorField = $e->getField();
                $errorType = $e->getType();
            }
        }

        return new HtmlResponse(
            WCF::getTPL()->fetchStream(
                'stepCreateUser',
                'wcf',
                [
                    'errorField' => $errorField,
                    'errorType' => $errorType,
                    'username' => $username,
                    'email' => $email,
                    'confirmEmail' => $confirmEmail,
                    'password' => $password,
                    'confirmPassword' => $confirmPassword,
                    'nextStep' => 'createUser',
                ]
            )
        );
    }

    /**
     * Registers with wcf setup delivered packages in the package installation queue.
     */
    protected function installPackages(): ResponseInterface
    {
        // init database connection
        $this->initDB();

        // get admin account
        $admin = new User(1);

        // get delivered packages
        $wcfPackageFile = '';
        $otherPackages = [];
        $tar = new Tar(SETUP_FILE);
        foreach ($tar->getContentList() as $file) {
            if ($file['type'] != 'folder' && \str_starts_with($file['filename'], 'install/packages/')) {
                $packageFile = \basename($file['filename']);

                // ignore any files which aren't an archive
                if (\preg_match('~\.(tar\.gz|tgz|tar)$~', $packageFile)) {
                    $packageName = \preg_replace('!\.(tar\.gz|tgz|tar)$!', '', $packageFile);

                    if ($packageName == 'com.woltlab.wcf') {
                        $wcfPackageFile = $packageFile;
                    } else {
                        $otherPackages[$packageName] = $packageFile;
                    }
                }
            }
        }
        $tar->close();

        // delete install files
        \unlink(\INSTALL_SCRIPT);
        \unlink(\SETUP_FILE);
        if (\file_exists(\INSTALL_SCRIPT_DIR . 'test.php')) {
            \unlink(\INSTALL_SCRIPT_DIR . 'test.php');
        }

        // register packages in queue
        $processNo = 1;

        if (empty($wcfPackageFile)) {
            throw new SystemException('the essential package com.woltlab.wcf is missing.');
        }

        $from = TMP_DIR . 'install/packages/' . $wcfPackageFile;
        $to = WCF_DIR . 'tmp/' . TMP_FILE_PREFIX . '-' . $wcfPackageFile;

        \rename($from, $to);

        // register essential wcf package
        $queue = PackageInstallationQueueEditor::create([
            'queueID' => 1,
            'processNo' => $processNo,
            'userID' => $admin->userID,
            'package' => 'com.woltlab.wcf',
            'packageName' => 'WoltLab Suite Core',
            'archive' => $to,
            'isApplication' => 1,
        ]);
        if ($queue->queueID !== 1) {
            throw new \LogicException("Failed to register queue for 'com.woltlab.wcf'.");
        }

        // register all other delivered packages
        \asort($otherPackages);
        foreach ($otherPackages as $packageName => $packageFile) {
            $from = TMP_DIR . 'install/packages/' . $packageFile;
            $to = WCF_DIR . 'tmp/' . TMP_FILE_PREFIX . '-' . $packageFile;

            // extract packageName from archive's package.xml
            $archive = new PackageArchive($from);
            $archive->openArchive();

            \rename($from, $to);

            /** @noinspection PhpUndefinedVariableInspection */
            $queue = PackageInstallationQueueEditor::create([
                'parentQueueID' => $queue->queueID,
                'processNo' => $processNo,
                'userID' => $admin->userID,
                'package' => $packageName,
                'packageName' => $archive->getLocalizedPackageInfo('packageName'),
                'archive' => $to,
                'isApplication' => 1,
            ]);
        }

        // determine the (randomized) cookie prefix
        $useRandomCookiePrefix = true;
        if (self::$developerMode && DevtoolsSetup::getInstance()->forceStaticCookiePrefix()) {
            $useRandomCookiePrefix = false;
        }

        $prefix = 'wsc_';
        if ($useRandomCookiePrefix) {
            $cookieNames = \array_keys($_COOKIE);
            while (true) {
                $prefix = 'wsc_' . \bin2hex(\random_bytes(3)) . '_';
                $isValid = true;
                foreach ($cookieNames as $cookieName) {
                    if (\strpos($cookieName, $prefix) === 0) {
                        $isValid = false;
                        break;
                    }
                }

                if ($isValid) {
                    break;
                }
            }

            // the options have not been imported yet
            \file_put_contents(WCF_DIR . 'cookiePrefix.txt', $prefix);
        }

        \define('COOKIE_PREFIX', $prefix);

        // Generate the output. This must happen before the session updates, because the
        // language won't work correctly otherwise.
        $output = new HtmlResponse(
            WCF::getTPL()->fetchStream(
                'stepInstallPackages',
                'wcf',
                [
                    'wcfAcp' => \RELATIVE_WCF_DIR . 'acp/index.php',
                ]
            )
        );

        // Set up the session and login as the administrator.
        $factory = new ACPSessionFactory();
        $factory->load();

        SessionHandler::getInstance()->changeUser($admin);
        SessionHandler::getInstance()->register('__wcfSetup_developerMode', self::$developerMode);
        SessionHandler::getInstance()->registerReauthentication();
        SessionHandler::getInstance()->update();

        // Delete tmp files
        foreach (new \DirectoryIterator(\INSTALL_SCRIPT_DIR) as $fileInfo) {
            if ($fileInfo->isDot() || !$fileInfo->isDir()) {
                continue;
            }

            if (!\preg_match('/^WCFSetup-[0-9a-f]{16}$/', $fileInfo->getBasename())) {
                continue;
            }

            $tmpDirectory = $fileInfo->getPathname();

            $tmpDirectoryIterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(
                    $tmpDirectory,
                    \RecursiveDirectoryIterator::CURRENT_AS_FILEINFO | \RecursiveDirectoryIterator::SKIP_DOTS
                ),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($tmpDirectoryIterator as $tmpFile) {
                if ($tmpFile->isDir()) {
                    \rmdir($tmpFile);
                } else {
                    \unlink($tmpFile);
                }
            }
            \rmdir($tmpDirectory);
        }

        return $output;
    }

    /**
     * Goes to the next step.
     */
    protected function gotoNextStep(string $nextStep): ResponseInterface
    {
        return new HtmlResponse(
            WCF::getTPL()->fetchStream(
                'stepNext',
                'wcf',
                [
                    'nextStep' => $nextStep,
                ]
            )
        );
    }
}
