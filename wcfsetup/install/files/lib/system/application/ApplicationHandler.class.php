<?php

namespace wcf\system\application;

use wcf\command\application\SynchronizeCookieDomain;
use wcf\data\application\Application;
use wcf\data\package\Package;
use wcf\system\cache\eager\ApplicationCache;
use wcf\system\cache\eager\data\ApplicationCacheData;
use wcf\system\request\RequestHandler;
use wcf\system\request\RouteHandler;
use wcf\system\SingletonFactory;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\FileUtil;
use wcf\util\StringUtil;
use wcf\util\Url;

/**
 * Handles multi-application environments.
 *
 * @author Alexander Ebert
 * @copyright 2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
final class ApplicationHandler extends SingletonFactory
{
    private ApplicationCacheData $cache;

    /**
     * @var string[]
     */
    private array $pageURLs;

    /**
     * Initializes cache.
     */
    protected function init()
    {
        $this->cache = (new ApplicationCache())->getCache();
    }

    /**
     * Returns an application based upon it's abbreviation. Will return the
     * primary application if the abbreviation is `wcf` or `null` if no such
     * application exists.
     *
     * @param $abbreviation package abbreviation, e.g. `wbb` for `com.woltlab.wbb`
     */
    public function getApplication(string $abbreviation): ?Application
    {
        return $this->cache->getApplicationByAbbreviation($abbreviation);
    }

    /**
     * Returns an application delivered by the package with the given id or `null`
     * if no such application exists.
     */
    public function getApplicationByID(int $packageID): ?Application
    {
        return $this->cache->getApplication($packageID);
    }

    /**
     * Returns pseudo-application representing WCF used for special cases,
     * e.g. cross-domain files requestable through the webserver.
     */
    public function getWCF(): Application
    {
        return $this->getApplicationByID(1);
    }

    /**
     * Returns the currently active application.
     */
    public function getActiveApplication(): Application
    {
        // work-around during WCFSetup
        if (!PACKAGE_ID) {
            $host = \str_replace(RouteHandler::getProtocol(), '', RouteHandler::getHost());
            $documentRoot = FileUtil::addTrailingSlash(FileUtil::unifyDirSeparator(\realpath($_SERVER['DOCUMENT_ROOT'])));

            // always use the core directory
            if (empty($_POST['directories']) || empty($_POST['directories']['wcf'])) {
                // within ACP
                $_POST['directories'] = ['wcf' => $documentRoot . FileUtil::removeLeadingSlash(RouteHandler::getPath(['acp']))];
            }

            $path = FileUtil::addLeadingSlash(FileUtil::addTrailingSlash(FileUtil::unifyDirSeparator(FileUtil::getRelativePath(
                $documentRoot,
                $_POST['directories']['wcf']
            ))));

            return new Application(null, [
                'domainName' => $host,
                'domainPath' => $path,
                'cookieDomain' => $host,
            ]);
        }

        $request = RequestHandler::getInstance()->getActiveRequest();
        if ($request !== null) {
            [$abbreviation] = \explode('\\', $request->getClassName(), 2);

            return $this->getApplication($abbreviation);
        }

        return $this->cache->getApplication(PACKAGE_ID) ?? $this->getWCF();
    }

    /**
     * Returns a list of dependent applications.
     *
     * @return Application[]
     */
    public function getDependentApplications(): array
    {
        $applications = $this->getApplications();
        foreach ($applications as $key => $application) {
            if ($application->packageID == $this->getActiveApplication()->packageID) {
                unset($applications[$key]);
                break;
            }
        }

        return $applications;
    }

    /**
     * Returns a list of all active applications.
     *
     * @return Application[]
     */
    public function getApplications(): array
    {
        return $this->cache->applications;
    }

    /**
     * Returns abbreviation for a given package id or `null` if application is unknown.
     */
    public function getAbbreviation(int $packageID): ?string
    {
        return $this->cache->getAbbreviationByPackageID($packageID);
    }

    /**
     * Returns the list of application abbreviations.
     *
     * @return string[]
     */
    public function getAbbreviations(): array
    {
        return \array_keys($this->cache->abbreviations);
    }

    /**
     * Returns true if given $url is an internal URL.
     */
    public function isInternalURL(string $url): bool
    {
        if (!isset($this->pageURLs)) {
            $internalHostnames = ArrayUtil::trim(\explode("\n", StringUtil::unifyNewlines(\INTERNAL_HOSTNAMES)));

            $this->pageURLs = \array_unique([
                $this->getDomainName(),
                ...$internalHostnames,
            ]);
        }

        $host = Url::parse($url)['host'];

        // Relative URLs are internal.
        if (!$host) {
            return true;
        }

        return Url::getHostnameMatcher($this->pageURLs)($host);
    }

    /**
     * Always returns false.
     *
     * @deprecated  5.4
     */
    public function isMultiDomainSetup(): bool
    {
        return false;
    }

    /**
     * @deprecated 5.5 - This function is a noop. The 'active' status is determined live.
     */
    public function rebuildActiveApplication(): void {}

    /**
     * @since 6.0
     */
    public function getDomainName(): string
    {
        return $this->getApplicationByID(1)->domainName;
    }

    /**
     * Resolve the active package id based on the rewritten URL.
     *
     * @since 6.2
     */
    public function resolveActiveApplication(string $path): void
    {
        $rootApplicationID = $this->cache['rootApplicationID'];
        \assert($rootApplicationID !== null);

        $path = FileUtil::removeLeadingSlash($path);
        $packageID = \array_find_key(
            $this->cache['sortedPaths'],
            static fn($prefix) => \str_starts_with($path, $prefix),
        );

        if ($packageID === null) {
            $packageID = $rootApplicationID;
        } else {
            $prefix = $this->cache['sortedPaths'][$packageID];
            $path = \mb_substr($path, \mb_strlen($prefix));
        }

        RouteHandler::overridePathInfo($path);

        if (!\defined('PACKAGE_ID')) {
            \define('PACKAGE_ID', $packageID);

            if ($packageID !== 1) {
                $application = ApplicationHandler::getInstance()->getApplicationByID($packageID);
                \assert($application !== null);

                // Include the `app.config.inc.php` of the primary app.
                $pathname = FileUtil::addTrailingSlash(
                    FileUtil::getRealPath(
                        \WCF_DIR . $application->getPackage()->packageDir
                    )
                ) . 'app.config.inc.php';

                require_once $pathname;
            }
        }
    }

    /**
     * Rebuilds cookie domain/path for all applications.
     */
    public static function rebuild(): void
    {
        (new SynchronizeCookieDomain())();
    }

    /**
     * Replaces `app1_` in the given string with the correct installation number:
     * `app{WCF_N_}`.
     *
     * This method can either be used for database table names directly or for
     * queries, for example.
     *
     * @param $skipCache if `true`, no caches will be used and relevant application packages will be read from database directly
     */
    public static function insertRealDatabaseTableNames(string $string, bool $skipCache = false): string
    {
        // This method is a no-op if WCF_N is 1 which is also the most common case.
        // Bypass the complete logic, as it can be expensive, especially for the $skipCache = true
        // case used during package installation.
        if (\WCF_N === 1) {
            return $string;
        }

        if ($skipCache) {
            $sql = "SELECT package
                    FROM   wcf" . WCF_N . "_package
                    WHERE  isApplication = ?";
            $statement = WCF::getDB()->prepareUnmanaged($sql);
            $statement->execute([1]);
            $packages = $statement->fetchAll(\PDO::FETCH_COLUMN);

            $abbreviations = \implode(
                '|',
                \array_map(static function (string $package): string {
                    return \preg_quote(Package::getAbbreviation($package), '~');
                }, $packages)
            );
            $regex = "~(\\b(?:{$abbreviations}))1_~";

            $string = \preg_replace(
                $regex,
                '${1}' . WCF_N . '_',
                $string
            );
        } else {
            static $regex = null;

            if ($regex === null) {
                $abbreviations = \implode(
                    '|',
                    \array_map(static function (Application $app): string {
                        return \preg_quote($app->getAbbreviation(), '~');
                    }, static::getInstance()->getApplications())
                );

                $regex = "~(\\b(?:{$abbreviations}))1_~";
            }

            $string = \preg_replace(
                $regex,
                '${1}' . WCF_N . '_',
                $string
            );
        }

        return $string;
    }
}
