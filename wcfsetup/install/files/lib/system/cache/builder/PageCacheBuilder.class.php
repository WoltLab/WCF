<?php

namespace wcf\system\cache\builder;

use wcf\data\page\Page;
use wcf\data\page\PageList;
use wcf\system\WCF;

/**
 * Caches the page data.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
class PageCacheBuilder extends AbstractCacheBuilder
{
    /**
     * @inheritDoc
     */
    public function rebuild(array $parameters)
    {
        $data = [
            'identifier' => [],
            'controller' => [],
            'pages' => [],
            'pageTitles' => [],
            'landingPage' => null,
            'pageMetaDescriptions' => [],
        ];

        $pageList = new PageList();
        $pageList->readObjects();
        $data['pages'] = $pageList->getObjects();

        // get page titles
        $sql = "SELECT  pageID, languageID, title, metaDescription
                FROM    wcf1_page_content";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
        while ($row = $statement->fetchArray()) {
            $pageID = $row['pageID'];

            if (!isset($data['pageTitles'])) {
                $data['pageTitles'][$pageID] = [];
            }

            $data['pageTitles'][$pageID][$row['languageID'] ?: 0] = $row['title'];

            if (!isset($data['pageMetaDescriptions'])) {
                $data['pageMetaDescriptions'][$pageID] = [];
            }
            $data['pageMetaDescriptions'][$pageID][$row['languageID'] ?: 0] = $row['metaDescription'];
        }

        $sql = "SELECT  landingPageID
                FROM    wcf1_application
                WHERE   packageID = ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([1]);
        $landingPageID = $statement->fetchSingleColumn();

        // build lookup table
        /** @var Page $page */
        foreach ($pageList as $page) {
            $data['identifier'][$page->identifier] = $page->pageID;
            $data['controller'][$page->controller] = $page->pageID;

            if ($page->pageID == $landingPageID || ($data['landingPage'] === null && $page->identifier === 'com.woltlab.wcf.ArticleList')) {
                $data['landingPage'] = $page;
            }
        }

        return $data;
    }
}
