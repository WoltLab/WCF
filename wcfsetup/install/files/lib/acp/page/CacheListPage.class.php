<?php

namespace wcf\acp\page;

use wcf\acp\action\CacheClearAction;
use wcf\page\AbstractPage;
use wcf\system\cache\CacheHandler;
use wcf\system\exception\SystemException;
use wcf\system\Regex;
use wcf\system\request\LinkHandler;
use wcf\system\WCF;
use wcf\util\DirectoryUtil;
use wcf\util\FileUtil;

/**
 * Shows a list of all cache resources.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
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
    public $neededPermissions = ['admin.management.canRebuildData'];

    /**
     * contains a list of cache resources
     * @var array<string, array<string, list<array{
     *  filename: string,
     *  filesize: int,
     *  mtime: int,
     *  perm: string,
     *  writeable: bool,
     * }>>>
     */
    public $caches = [];

    /**
     * contains general cache information
     * @var array{
     *  size: int,
     *  files: int,
     * }|array{}
     */
    public $cacheData = [];

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();
    }

    #[\Override]
    public function readData()
    {
        parent::readData();

        // init cache data
        $this->cacheData = [
            'size' => 0,
            'files' => 0,
        ];

        $this->readCacheFiles('data', FileUtil::unifyDirSeparator(\WCF_DIR . 'cache'));

        $this->readCacheFiles('language', FileUtil::unifyDirSeparator(\WCF_DIR . 'language'));
        $this->readCacheFiles(
            'template',
            FileUtil::unifyDirSeparator(\WCF_DIR . 'templates/compiled'),
            new Regex('\.meta\.php$')
        );
        $this->readCacheFiles(
            'template',
            FileUtil::unifyDirSeparator(\WCF_DIR . 'acp/templates/compiled'),
            new Regex('\.meta\.php$')
        );
        $this->readCacheFiles('style', FileUtil::unifyDirSeparator(\WCF_DIR . 'style'), null, '(css|json)');
        $this->readCacheFiles(
            'style',
            FileUtil::unifyDirSeparator(\WCF_DIR . 'acp/style'),
            new Regex('WCFSetup.css$'),
            'css'
        );
    }

    /**
     * Reads the information of cached files
     *
     * @return void
     */
    protected function readCacheFiles(string $cacheType, string $cacheDir, ?Regex $ignore = null, string $extension = 'php')
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
        foreach ($files as $file) {
            if ($ignore !== null && $ignore->match($file->getPath()) !== 0) {
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

        $this->caches[$cacheType][$cacheDir] = $data;
    }

    #[\Override]
    public function assignVariables()
    {
        parent::assignVariables();

        WCF::getTPL()->assign([
            'caches' => $this->caches,
            'cacheData' => $this->cacheData,
            'cacheClearEndPoint' => LinkHandler::getInstance()->getControllerLink(CacheClearAction::class),
        ]);
    }
}
