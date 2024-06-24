<?php

namespace wcf\acp\page;

use wcf\data\application\Application;
use wcf\page\AbstractPage;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\Environment;
use wcf\system\exception\SystemException;
use wcf\system\registry\RegistryHandler;
use wcf\system\request\RouteHandler;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * @author      Alexander Ebert
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.2
 */
class SystemCheckPage extends AbstractPage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.systemCheck';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.configuration.package.canInstallPackage'];

    /**
     * A list of directories that need to be writable at all times, grouped by their application
     * identifier. Only the directory itself is checked, unless the path ends with `/*`.
     * @var string[][]
     */
    public $directories = [
        'wcf' => [
            '/',
            '/acp/style',
            '/acp/templates/compiled',
            '/attachments/*',
            '/cache',
            '/images/*',
            '/language',
            '/log',
            '/media_files/*',
            '/sitemaps',
            '/style',
            '/templates/compiled',
            '/tmp',
        ],
    ];

    public $mysqlVersions = [
        'mysql' => [
            '8' => '8.0.30',
        ],
        'mariadb' => [
            '10' => '10.5.15',
        ],
    ];

    public $phpExtensions = [
        'ctype',
        'dom',
        'exif',
        'gd',
        'intl',
        'libxml',
        'mbstring',
        'pdo_mysql',
        'pdo',
        'zlib',
    ];

    public $phpMemoryLimit = 128 * 1024 * 1024;

    public $phpVersions = [
        'minimum' => '8.1.2',
        'deprecated' => [],
        'sufficient' => ['8.1'],
        'recommended' => ['8.2', '8.3'],
    ];

    public $foreignKeys = [
        'wcf' . WCF_N . '_user' => [
            'avatarID' => [
                'referenceTable' => 'wcf' . WCF_N . '_user_avatar',
                'referenceColumn' => 'avatarID',
            ],
        ],
        'wcf' . WCF_N . '_comment' => [
            'userID' => [
                'referenceTable' => 'wcf' . WCF_N . '_user',
                'referenceColumn' => 'userID',
            ],
            'objectTypeID' => [
                'referenceTable' => 'wcf' . WCF_N . '_object_type',
                'referenceColumn' => 'objectTypeID',
            ],
        ],
        'wcf' . WCF_N . '_moderation_queue' => [
            'objectTypeID' => [
                'referenceTable' => 'wcf' . WCF_N . '_object_type',
                'referenceColumn' => 'objectTypeID',
            ],
            'assignedUserID' => [
                'referenceTable' => 'wcf' . WCF_N . '_user',
                'referenceColumn' => 'userID',
            ],
            'userID' => [
                'referenceTable' => 'wcf' . WCF_N . '_user',
                'referenceColumn' => 'userID',
            ],
        ],
    ];

    public $results = [
        'directories' => [],
        'mysql' => [
            'innodb' => false,
            'mariadb' => false,
            'result' => false,
            'version' => '0.0.0',
            'foreignKeys' => false,
            'mysqlnd' => false,
            'bufferPool' => [
                'result' => false,
                'value' => '0',
            ],
        ],
        'php' => [
            'gd' => [
                'jpeg' => false,
                'png' => false,
                'webp' => false,
                'result' => false,
            ],
            'extension' => [],
            'memoryLimit' => [
                'required' => '0',
                'result' => false,
                'value' => '0',
            ],
            'opcache' => null,
            'version' => [
                'result' => 'unsupported',
                'value' => '0.0.0',
            ],
            'x64' => false,
        ],
        'web' => [
            'https' => false,
        ],
        'status' => [
            'directories' => false,
            'mysql' => false,
            'php' => false,
            'web' => false,
        ],
    ];

    /**
     * indicates that this page is only accessible to owners in enterprise mode
     */
    const BLACKLISTED_IN_ENTERPRISE_MODE = true;

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        if (IMAGE_ADAPTER_TYPE === 'imagick' && !\in_array('imagick', $this->phpExtensions)) {
            $this->phpExtensions[] = 'imagick';
        }

        if (CACHE_SOURCE_TYPE === 'redis' && !\in_array('redis', $this->phpExtensions)) {
            $this->phpExtensions[] = 'redis';
        }

        $this->validateMysql();
        $this->validatePhpExtensions();
        $this->validatePhpMemoryLimit();
        $this->validatePhpX64();
        $this->validatePhpVersion();
        $this->validatePhpGdSupport();
        $this->validateWritableDirectories();
        $this->validateWebHttps();

        if (
            $this->results['status']['mysql']
            && $this->results['status']['php']
            && $this->results['status']['directories']
        ) {
            RegistryHandler::getInstance()->set(
                'com.woltlab.wcf',
                Environment::SYSTEM_ID_REGISTRY_KEY,
                Environment::getSystemId()
            );
        }
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'mysqlVersions' => $this->mysqlVersions,
            'phpExtensions' => $this->phpExtensions,
            'phpMemoryLimit' => $this->phpMemoryLimit,
            'phpVersions' => $this->phpVersions,
            'results' => $this->results,
        ]);
    }

    protected function validateMysql()
    {
        // check sql version
        $sqlVersion = WCF::getDB()->getVersion();
        $compareSQLVersion = \preg_replace('/^(\d+\.\d+\.\d+).*$/', '\\1', $sqlVersion);
        // Do not use the "raw" version, it usually contains a lot of noise.
        $this->results['mysql']['version'] = $compareSQLVersion;
        if (\stripos($sqlVersion, 'MariaDB') !== false) {
            $this->results['mysql']['mariadb'] = true;

            $this->results['mysql']['result'] = (\version_compare(
                $compareSQLVersion,
                $this->mysqlVersions['mariadb']['10']
            ) >= 0);
        } else {
            $this->results['mysql']['result'] = (\version_compare(
                $compareSQLVersion,
                $this->mysqlVersions['mysql']['8']
            ) >= 0);
        }

        // check for MySQL Native driver
        $sql = "SELECT 1";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute();
        $this->results['mysql']['mysqlnd'] = ($statement->fetchSingleColumn() === 1);

        // check innodb support
        $sql = "SHOW ENGINES";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute();
        while ($row = $statement->fetchArray()) {
            if ($row['Engine'] == 'InnoDB' && \in_array($row['Support'], ['DEFAULT', 'YES'])) {
                $this->results['mysql']['innodb'] = true;
                break;
            }
        }

        // validate foreign keys
        $expectedForeignKeyCount = 0;
        $conditionBuilder = new PreparedStatementConditionBuilder(true, 'OR');
        foreach ($this->foreignKeys as $table => $keys) {
            foreach ($keys as $column => $reference) {
                $innerConditionBuilder = new PreparedStatementConditionBuilder(false);
                $innerConditionBuilder->add('REFERENCED_TABLE_SCHEMA = ?', [WCF::getDB()->getDatabaseName()]);
                $innerConditionBuilder->add('REFERENCED_TABLE_NAME = ?', [$reference['referenceTable']]);
                $innerConditionBuilder->add('REFERENCED_COLUMN_NAME = ?', [$reference['referenceColumn']]);
                $innerConditionBuilder->add('TABLE_NAME = ?', [$table]);
                $innerConditionBuilder->add('COLUMN_NAME = ?', [$column]);

                $conditionBuilder->add('(' . $innerConditionBuilder . ')', $innerConditionBuilder->getParameters());

                $expectedForeignKeyCount++;
            }
        }

        $sql = "SELECT  COUNT(*)
                FROM    INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute($conditionBuilder->getParameters());

        $this->results['mysql']['foreignKeys'] = $statement->fetchSingleColumn() == $expectedForeignKeyCount;

        $sql = "SELECT  @@innodb_buffer_pool_size";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute();
        $this->results['mysql']['bufferPool']['value'] = $statement->fetchSingleColumn();
        if ($this->results['mysql']['bufferPool']['value'] > 134217728) {
            // More than 134217728 bytes indicates that the web hoster at
            // the very least touched the MySQL configuration once.
            $this->results['mysql']['bufferPool']['result'] = 'recommended';
        } elseif ($this->results['mysql']['bufferPool']['value'] == 134217728) {
            // The default of 134217728 bytes is okay, but the web hoster did not care.
            $this->results['mysql']['bufferPool']['result'] = 'sufficient';
        } else {
            // The web hoster messed up the configuration.
            $this->results['mysql']['bufferPool']['result'] = false;
        }

        if (
            $this->results['mysql']['result']
            && $this->results['mysql']['mysqlnd']
            && $this->results['mysql']['innodb']
            && $this->results['mysql']['foreignKeys']
            && $this->results['mysql']['bufferPool']['result']
        ) {
            $this->results['status']['mysql'] = true;
        }
    }

    protected function validatePhpExtensions()
    {
        foreach ($this->phpExtensions as $phpExtension) {
            $result = \extension_loaded($phpExtension);
            if (!$result) {
                $this->results['php']['extension'][] = $phpExtension;
            }
        }

        try {
            // Attempt to reset ourselves to perform a functional check.
            WCF::resetZendOpcache(__FILE__);

            if (\extension_loaded('Zend Opcache') && \ini_get('opcache.enable')) {
                $this->results['php']['opcache'] = \function_exists('opcache_reset') && \function_exists('opcache_invalidate');
            }
        } catch (\Exception $e) {
            $this->results['php']['opcache'] = false;
        }

        $this->results['status']['php'] = empty($this->results['php']['extension'])
            && $this->results['php']['opcache'] !== false;
    }

    protected function validatePhpMemoryLimit()
    {
        $this->results['php']['memoryLimit']['required'] = $this->phpMemoryLimit;

        $memoryLimit = FileUtil::getMemoryLimit();

        // Memory is not limited through PHP.
        if ($memoryLimit == -1) {
            $this->results['php']['memoryLimit']['value'] = "\u{221E}";
            $this->results['php']['memoryLimit']['result'] = true;
        } else {
            $this->results['php']['memoryLimit']['value'] = FileUtil::formatFilesizeBinary($memoryLimit);
            $this->results['php']['memoryLimit']['result'] = ($memoryLimit >= $this->phpMemoryLimit);
        }

        $this->results['status']['php'] = $this->results['status']['php'] && $this->results['php']['memoryLimit']['result'];
    }

    protected function validatePhpX64()
    {
        $this->results['php']['x64'] = \PHP_INT_SIZE == 8;

        $this->results['status']['php'] = $this->results['status']['php'] && $this->results['php']['x64'];
    }

    protected function validatePhpVersion()
    {
        $phpVersion = \PHP_VERSION;
        $comparePhpVersion = \preg_replace('/^(\d+\.\d+\.\d+).*$/', '\\1', $phpVersion);
        // Do not use the "raw" version, it usually contains a lot of noise.
        $this->results['php']['version']['value'] = $comparePhpVersion;
        if (\version_compare($comparePhpVersion, $this->phpVersions['minimum']) >= 0) {
            $majorMinor = \preg_replace('~^(\d+\.\d+).*$~', '\\1', $phpVersion);
            foreach (['recommended', 'sufficient', 'deprecated'] as $type) {
                foreach ($this->phpVersions[$type] as $version) {
                    if ($majorMinor === $version) {
                        $this->results['php']['version']['result'] = $type;
                        break 2;
                    }
                }
            }
        } else {
            $this->results['php']['version']['result'] = 'unsupported';
        }

        $this->results['status']['php'] = $this->results['status']['php'] && ($this->results['php']['version']['result'] !== 'unsupported');
    }

    protected function validatePhpGdSupport()
    {
        if (!\function_exists('\gd_info')) {
            $this->results['status']['php'] = false;

            return;
        }

        $gdInfo = \gd_info();
        $this->results['php']['gd']['jpeg'] = !empty($gdInfo['JPEG Support']);
        $this->results['php']['gd']['png'] = !empty($gdInfo['PNG Support']);
        $this->results['php']['gd']['webp'] = !empty($gdInfo['WebP Support']);

        $this->results['php']['gd']['result'] = $this->results['php']['gd']['jpeg']
            && $this->results['php']['gd']['png']
            && $this->results['php']['gd']['webp'];

        $this->results['status']['php'] = $this->results['status']['php'] && $this->results['php']['gd']['result'];
    }

    protected function validateWritableDirectories()
    {
        foreach ($this->directories as $abbreviation => $directories) {
            $basePath = Application::getDirectory($abbreviation);
            foreach ($directories as $directory) {
                foreach (\glob($basePath . FileUtil::removeLeadingSlash($directory)) as $file) {
                    if (\is_dir($file) && !\is_writable($file)) {
                        $this->makeDirectoryWritable($file);
                    }
                }
            }
        }

        $this->results['status']['directories'] = empty($this->results['directories']);
    }

    protected function checkDirectory($path)
    {
        if (!$this->createDirectoryIfNotExists($path)) {
            $this->results['directories'][] = FileUtil::unifyDirSeparator($path);

            return false;
        }

        return $this->makeDirectoryWritable($path);
    }

    protected function createDirectoryIfNotExists($path)
    {
        if (!\file_exists($path) && !FileUtil::makePath($path)) {
            // FileUtil::makePath() returns false if either the directory cannot be created
            // or if it cannot be made writable.
            if (!\file_exists($path)) {
                return false;
            }
        }

        return true;
    }

    protected function makeDirectoryWritable($path)
    {
        try {
            FileUtil::makeWritable($path);
        } catch (SystemException $e) {
            $this->results['directories'][] = FileUtil::unifyDirSeparator($path);

            return false;
        }

        return true;
    }

    /**
     * @since 6.1
     */
    protected function validateWebHttps(): void
    {
        $this->results['web']['https'] = RouteHandler::secureContext();

        $this->results['status']['web'] = $this->results['web']['https'];
    }
}
