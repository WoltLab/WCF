<?php

namespace wcf\acp\page;

use wcf\page\AbstractPage;
use wcf\system\cache\CacheHandler;
use wcf\system\exception\SystemException;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\DirectoryUtil;
use wcf\util\FileUtil;

/**
 * Shows a list of all cache resources.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Acp\Page
 */
class CacheListPage extends AbstractPage
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.maintenance.cache';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.configuration.canManageApplication'];

    /**
     * indicates if cache was cleared
     * @var int
     */
    public $cleared = 0;

    /**
     * contains a list of cache resources
     * @var array
     */
    public $caches = [];

    /**
     * contains general cache information
     * @var array
     */
    public $cacheData = [];

    /**
     * @inheritDoc
     */
    public function readParameters()
    {
        parent::readParameters();

        if (isset($_REQUEST['cleared'])) {
            $this->cleared = \intval($_REQUEST['cleared']);
        }
    }

    /**
     * @inheritDoc
     */
    public function readData()
    {
        parent::readData();

        // init cache data
        $this->cacheData = [
            'source' => \get_class(CacheHandler::getInstance()->getCacheSource()),
            'version' => '',
            'size' => 0,
            'files' => 0,
        ];

        switch ($this->cacheData['source']) {
            case 'wcf\system\cache\source\DiskCacheSource':
                // set version
                $this->cacheData['version'] = WCF_VERSION;

                $this->readCacheFiles('data', WCF_DIR . 'cache');
                break;

            case 'wcf\system\cache\source\MemcachedCacheSource':
                // set version
                /** @noinspection PhpUndefinedMethodInspection */
                $this->cacheData['version'] = CacheHandler::getInstance()->getCacheSource()->getMemcachedVersion();
                break;

            case 'wcf\system\cache\source\RedisCacheSource':
                // set version
                /** @noinspection PhpUndefinedMethodInspection */
                $this->cacheData['version'] = CacheHandler::getInstance()->getCacheSource()->getRedisVersion();
                break;
        }

        $this->readCacheFiles('language', FileUtil::unifyDirSeparator(WCF_DIR . 'language'));
        $this->readCacheFiles(
            'template',
            FileUtil::unifyDirSeparator(WCF_DIR . 'templates/compiled'),
            new Regex('\.meta\.php$')
        );
        $this->readCacheFiles(
            'template',
            FileUtil::unifyDirSeparator(WCF_DIR . 'acp/templates/compiled'),
            new Regex('\.meta\.php$')
        );
        $this->readCacheFiles('style', FileUtil::unifyDirSeparator(WCF_DIR . 'style'), null, '(css|json)');
        $this->readCacheFiles(
            'style',
            FileUtil::unifyDirSeparator(WCF_DIR . 'acp/style'),
            new Regex('WCFSetup.css$'),
            'css'
        );
    }

    /**
     * Reads the information of cached files
     *
     * @param string $cacheType
     * @param string $cacheDir
     * @param Regex $ignore
     * @param string $extension
     */
    protected function readCacheFiles($cacheType, $cacheDir, ?Regex $ignore = null, $extension = 'php')
    {
        if (!isset($this->cacheData[$cacheType])) {
            $this->cacheData[$cacheType] = [];
        }

        // get files in cache directory
        try {
            $directoryUtil = DirectoryUtil::getInstance($cacheDir);
        } catch (SystemException $e) {
            return;
        }

        $files = $directoryUtil->getFileObjects(\SORT_ASC, new Regex('\.' . $extension . '$'));

        // get additional file information
        $data = [];
        if (\is_array($files)) {
            /** @var \SplFileInfo $file */
            foreach ($files as $file) {
                if ($ignore !== null && $ignore->match($file->getPath())) {
                    continue;
                }

                $data[] = [
                    'filename' => $file->getBasename(),
                    'filesize' => $file->getSize(),
                    'mtime' => $file->getMTime(),
                    'perm' => \substr(\sprintf('%o', $file->getPerms()), -3),
                    'writable' => $file->isWritable(),
                ];

                $this->cacheData['files']++;
                $this->cacheData['size'] += $file->getSize();
            }
        }

        $this->caches[$cacheType][$cacheDir] = $data;
    }

    /**
     * @inheritDoc
     */
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'caches' => $this->caches,
            'cacheData' => $this->cacheData,
            'cleared' => $this->cleared,
        ]);
    }
}
