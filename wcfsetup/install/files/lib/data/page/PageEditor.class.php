<?php

namespace wcf\data\page;

use wcf\data\DatabaseObjectEditor;
use wcf\data\IEditableCachedObject;
use wcf\data\package\PackageCache;
use wcf\system\cache\builder\MenuCacheBuilder;
use wcf\system\cache\builder\PageCacheBuilder;
use wcf\system\cache\builder\RoutingCacheBuilder;
use wcf\system\request\ControllerMap;
use wcf\system\WCF;
use wcf\util\FileUtil;

/**
 * Provides functions to edit pages.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 *
 * @method static Page    create(array $parameters = [])
 * @method      Page    getDecoratedObject()
 * @mixin       Page
 */
class PageEditor extends DatabaseObjectEditor implements IEditableCachedObject
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = Page::class;

    /**
     * Creates or updates the page's template file.
     *
     * @param int $languageID language id or `null`
     * @param string $content template content
     */
    public function updateTemplate($languageID, $content)
    {
        if ($this->pageType !== 'tpl') {
            throw new \RuntimeException("Only tpl-type pages support template files.");
        }

        $filename = WCF_DIR . 'templates/' . $this->getTplName(($languageID ?: null)) . '.tpl';
        \file_put_contents($filename, $content);
        WCF::resetZendOpcache($filename);
    }

    /**
     * @inheritDoc
     */
    public static function resetCache()
    {
        RoutingCacheBuilder::getInstance()->reset();
        PageCacheBuilder::getInstance()->reset();
        MenuCacheBuilder::getInstance()->reset();
    }

    /**
     * Returns true if given custom url is unique.
     *
     * @param string $customURL
     * @param int $packageID
     *
     * @return      bool
     */
    public static function isUniqueCustomUrl($customURL, $packageID = 1)
    {
        // check controller
        $package = PackageCache::getInstance()->getPackage($packageID);
        $packageDir = FileUtil::addTrailingSlash(FileUtil::getRealPath(WCF_DIR . $package->packageDir));

        $files = \array_merge(
            \glob($packageDir . 'lib/action/*.php'),
            \glob($packageDir . 'lib/form/*.php'),
            \glob($packageDir . 'lib/page/*.php')
        );
        foreach ($files as $file) {
            $filename = \preg_replace('/(Action|Page|Form)(\.class)?\.php$/', '', \basename($file));
            if ($customURL == ControllerMap::transformController($filename)) {
                return false;
            }
        }

        // check custom controller urls
        $sql = "SELECT  COUNT(*) AS count
                FROM    wcf1_page
                WHERE   controllerCustomURL = ?
                    AND applicationPackageID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$customURL, $packageID]);
        if ($statement->fetchSingleColumn()) {
            return false;
        }

        // check custom urls
        $sql = "SELECT  COUNT(*) AS count
                FROM    wcf1_page_content
                WHERE   customURL = ?
                    AND pageID IN (
                        SELECT  pageID
                        FROM    wcf1_page
                        WHERE   applicationPackageID = ?
                    )";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([$customURL, $packageID]);
        if ($statement->fetchSingleColumn()) {
            return false;
        }

        return true;
    }
}
