<?php

namespace wcf\data\package\update\server;

use Laminas\Diactoros\Uri;
use wcf\data\DatabaseObject;
use wcf\system\cache\builder\PackageUpdateCacheBuilder;
use wcf\system\Regex;
use wcf\system\registry\RegistryHandler;
use wcf\system\WCF;
use wcf\util\FileUtil;
use wcf\util\Url;

/**
 * Represents a package update server.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @property-read   int $packageUpdateServerID      unique id of the package update server
 * @property-read   string $serverURL          url of the package update server
 * @property-read   string $loginUsername          username used to login on the package update server
 * @property-read   string $loginPassword          password used to login on the package update server
 * @property-read   int $isDisabled         is `1` if the package update server is disabled and thus not considered for package updates, otherwise `0`
 * @property-read   int $lastUpdateTime         timestamp at which the data of the package update server has been fetched the last time
 * @property-read   string $status             status of the package update server (`online` or `offline`)
 * @property-read   string $errorMessage           error message if the package update server if offline or empty otherwise
 * @property-read   string $apiVersion         version of the supported package update server api (`2.0`, `2.1`)
 */
class PackageUpdateServer extends DatabaseObject
{
    /**
     * @inheritDoc
     */
    protected static $databaseTableIndexName = 'packageUpdateServerID';

    /**
     * API meta data
     * @var array
     */
    protected $metaData = [];

    /**
     * Restricts the package server selection to include only
     * official package servers in case a secure download is
     * requested.
     */
    private static $secureMode = false;

    /**
     * @inheritDoc
     */
    protected function handleData($data)
    {
        if (!empty($data['metaData'])) {
            $metaData = @\unserialize($data['metaData']);
            if (\is_array($metaData)) {
                $this->metaData = $metaData;
            }

            unset($data['metaData']);
        }

        parent::handleData($data);

        $prefix = ENABLE_ENTERPRISE_MODE ? 'cloud/' : '';
        $officialPath = \wcf\getMinorVersion();
        if (self::isUpgradeOverrideEnabled()) {
            $officialPath = WCF::AVAILABLE_UPGRADE_VERSION;
        }

        if ($this->isWoltLabUpdateServer()) {
            $this->data['serverURL'] = "https://update.woltlab.com/{$prefix}{$officialPath}/";
        }
        if ($this->isWoltLabStoreServer()) {
            $this->data['serverURL'] = "https://store.woltlab.com/{$prefix}{$officialPath}/";
        }
        if ($this->isWoltLabUpdateServer() || $this->isWoltLabStoreServer()) {
            $this->data['isDisabled'] = 0;
        }
    }

    /**
     * Returns all active update package servers sorted by hostname.
     *
     * @param int[] $packageUpdateServerIDs
     * @return  PackageUpdateServer[]
     */
    final public static function getActiveUpdateServers(array $packageUpdateServerIDs = []): array
    {
        if (!empty($packageUpdateServerIDs)) {
            throw new \InvalidArgumentException("Filtering package update servers by ID is no longer supported.");
        }

        $list = new PackageUpdateServerList();
        $list->readObjects();

        $woltlabUpdateServer = null;
        $woltlabStoreServer = null;
        $results = [];
        foreach ($list as $packageServer) {
            if ($packageServer->isWoltLabUpdateServer()) {
                $woltlabUpdateServer = $packageServer;
            } elseif ($packageServer->isWoltLabStoreServer()) {
                $woltlabStoreServer = $packageServer;
            } elseif ($packageServer->isDisabled) {
                continue;
            } elseif (self::$secureMode) {
                // Skip any unofficial servers when the secure mode
                // was requested.
                continue;
            }

            $results[$packageServer->packageUpdateServerID] = $packageServer;
        }

        $officialPath = \wcf\getMinorVersion();
        if (self::isUpgradeOverrideEnabled()) {
            $officialPath = WCF::AVAILABLE_UPGRADE_VERSION;
        }

        if (!$woltlabUpdateServer) {
            $packageServer = PackageUpdateServerEditor::create([
                'serverURL' => "https://update.woltlab.com/{$officialPath}/",
            ]);
            $results[$packageServer->packageUpdateServerID] = $packageServer;
        }
        if (!$woltlabStoreServer) {
            $packageServer = PackageUpdateServerEditor::create([
                'serverURL' => "https://store.woltlab.com/{$officialPath}/",
            ]);
            $results[$packageServer->packageUpdateServerID] = $packageServer;
        }

        if (ENABLE_ENTERPRISE_MODE) {
            return \array_filter($results, static function (self $server) {
                return $server->isWoltLabStoreServer() || $server->isTrustedServer();
            });
        }

        return $results;
    }

    final public static function getPluginStoreServer(): self
    {
        $pluginStoreServer = \array_filter(self::getActiveUpdateServers(), static function (self $updateServer) {
            return $updateServer->isWoltLabStoreServer();
        });

        return \current($pluginStoreServer);
    }

    final public static function getWoltLabUpdateServer(): self
    {
        $pluginStoreServer = \array_filter(self::getActiveUpdateServers(), static function (self $updateServer) {
            return $updateServer->isWoltLabUpdateServer();
        });

        return \current($pluginStoreServer);
    }

    /**
     * Restricts the available sources to official package
     * servers when a secure download is requested.
     */
    final public static function enableSecureMode(): void
    {
        self::$secureMode = true;
    }

    /**
     * @deprecated 6.0 This method was only used in PackageUpdateServerAddForm.
     */
    public static function isValidServerURL($serverURL): bool
    {
        $parsedURL = Url::parse($serverURL);

        return \in_array($parsedURL['scheme'], ['https']) && $parsedURL['host'] !== '';
    }

    /**
     * Returns stored auth data of this update server.
     *
     * @return  string[]
     */
    public function getAuthData()
    {
        if (ENABLE_ENTERPRISE_MODE && \defined('ENTERPRISE_MODE_AUTH_DATA')) {
            $host = Url::parse($this->serverURL)['host'];
            if (!empty(ENTERPRISE_MODE_AUTH_DATA[$host])) {
                return ENTERPRISE_MODE_AUTH_DATA[$host];
            }
        }

        $authData = [];
        // database data
        if ($this->loginUsername != '' && $this->loginPassword != '') {
            $authData = [
                'username' => $this->loginUsername,
                'password' => $this->loginPassword,
            ];
        }

        // session data
        $packageUpdateAuthData = WCF::getSession()->getVar('packageUpdateAuthData');
        if ($packageUpdateAuthData !== null) {
            $packageUpdateAuthData = @\unserialize($packageUpdateAuthData);
            if ($packageUpdateAuthData !== null && isset($packageUpdateAuthData[$this->packageUpdateServerID])) {
                $authData = $packageUpdateAuthData[$this->packageUpdateServerID];
            }
        }

        return $authData;
    }

    /**
     * Stores auth data for a package update server.
     *
     * @param int $packageUpdateServerID
     * @param string $username
     * @param string $password
     * @param bool $saveCredentials
     */
    public static function storeAuthData(
        $packageUpdateServerID,
        $username,
        #[\SensitiveParameter]
        $password,
        $saveCredentials = false
    ): void {
        $packageUpdateAuthData = @\unserialize(WCF::getSession()->getVar('packageUpdateAuthData'));
        if ($packageUpdateAuthData === null || !\is_array($packageUpdateAuthData)) {
            $packageUpdateAuthData = [];
        }

        $packageUpdateAuthData[$packageUpdateServerID] = [
            'username' => $username,
            'password' => $password,
        ];

        WCF::getSession()->register('packageUpdateAuthData', \serialize($packageUpdateAuthData));

        if ($saveCredentials) {
            $serverAction = new PackageUpdateServerAction([$packageUpdateServerID], 'update', [
                'data' => [
                    'loginUsername' => $username,
                    'loginPassword' => $password,
                ],
            ]);
            $serverAction->executeAction();
        }
    }

    /**
     * Returns true if update server requires license data instead of username/password.
     */
    final public function requiresLicense(): bool
    {
        return !!Regex::compile('^https?://update.woltlab.com/')->match($this->serverURL);
    }

    /**
     * Returns the highlighted server URL.
     */
    public function getHighlightedURL(): string
    {
        $host = Url::parse($this->serverURL)['host'];

        return \str_replace($host, '<strong>' . $host . '</strong>', $this->serverURL);
    }

    /**
     * Returns the list endpoint for package servers.
     */
    public function getListURL(): string
    {
        $url = new Uri($this->serverURL);

        if ($url->getHost() !== 'localhost') {
            $url = $url->withScheme('https');
        }

        if ($this->apiVersion == '2.0') {
            return (string)$url;
        }

        return FileUtil::addTrailingSlash((string)$url) . 'list/' . WCF::getLanguage()->getFixedLanguageCode() . '.xml';
    }

    /**
     * Returns the download endpoint for package servers.
     */
    public function getDownloadURL(): string
    {
        $url = new Uri($this->serverURL);

        if ($url->getHost() !== 'localhost') {
            $url = $url->withScheme('https');
        }

        return (string)$url;
    }

    /**
     * Returns API meta data.
     *
     * @return  array
     */
    public function getMetaData()
    {
        return $this->metaData;
    }

    /**
     * @deprecated 6.0 This method always returns true. Package servers must use TLS.
     */
    public function attemptSecureConnection(): bool
    {
        return true;
    }

    /**
     * Returns whether the current user may delete this update server.
     *
     * @since       5.3
     */
    final public function canDelete(): bool
    {
        return !$this->isWoltLabUpdateServer() && !$this->isWoltLabStoreServer();
    }

    /**
     * Returns whether the current user may disable this update server.
     *
     * @since       5.3
     */
    final public function canDisable(): bool
    {
        return !$this->isWoltLabUpdateServer() && !$this->isWoltLabStoreServer();
    }

    /**
     * Returns true if the host is `update.woltlab.com`.
     */
    final public function isWoltLabUpdateServer(): bool
    {
        return Url::parse($this->serverURL)['host'] === 'update.woltlab.com';
    }

    /**
     * Returns true if the host is `store.woltlab.com`.
     */
    final public function isWoltLabStoreServer(): bool
    {
        return Url::parse($this->serverURL)['host'] === 'store.woltlab.com';
    }

    /**
     * Returns true if this server is trusted and is therefore allowed to distribute
     * official updates for packages whose identifier starts with "com.woltlab.".
     */
    final public function isTrustedServer(): bool
    {
        return $this->isWoltLabUpdateServer();
    }

    /**
     * Returns whether the official update servers will point to WCF::AVAILABLE_UPGRADE_VERSION.
     *
     * @since 5.3
     */
    final public static function isUpgradeOverrideEnabled(): bool
    {
        if (WCF::AVAILABLE_UPGRADE_VERSION === null) {
            return false;
        }

        $overrideKey = \sprintf(
            "%s\0upgradeOverride_%s",
            self::class,
            WCF::AVAILABLE_UPGRADE_VERSION,
        );

        $override = RegistryHandler::getInstance()->get('com.woltlab.wcf', $overrideKey);

        if (!$override) {
            return false;
        }

        if ($override < TIME_NOW - 86400) {
            RegistryHandler::getInstance()->delete('com.woltlab.wcf', $overrideKey);

            // Clear package list cache to actually stop the upgrade from happening.
            self::resetAll();

            return false;
        }

        return true;
    }

    /**
     * Resets all update servers into their original state and purges
     * the package cache.
     */
    public static function resetAll(): void
    {
        // purge package cache
        WCF::getDB()->prepareStatement("DELETE FROM wcf" . WCF_N . "_package_update")->execute();

        PackageUpdateCacheBuilder::getInstance()->reset();

        // reset servers into their original state
        $sql = "UPDATE  wcf" . WCF_N . "_package_update_server
                SET     lastUpdateTime = ?,
                        status = ?,
                        errorMessage = ?,
                        apiVersion = ?,
                        metaData = ?";
        $statement = WCF::getDB()->prepareStatement($sql);
        $statement->execute([
            0,
            'online',
            '',
            '2.0',
            null,
        ]);
    }
}
