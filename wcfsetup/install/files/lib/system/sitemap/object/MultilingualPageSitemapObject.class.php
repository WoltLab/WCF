<?php

namespace wcf\system\sitemap\object;

use wcf\data\DatabaseObject;
use wcf\data\page\content\PageContent;
use wcf\data\page\content\PageContentList;
use wcf\data\page\Page;
use wcf\page\AbstractPage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\language\LanguageFactory;

/**
 * Multilingual page sitemap implementation.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractSitemapObjectObjectType<PageContent, PageContentList>
 */
class MultilingualPageSitemapObject extends AbstractSitemapObjectObjectType
{
    #[\Override]
    public function getObjectClass()
    {
        return PageContent::class;
    }

    #[\Override]
    public function getObjectList()
    {
        $pageList = parent::getObjectList();

        $pageList->sqlConditionJoins = '
            LEFT JOIN   wcf1_page page
            ON          page_content.pageID = page.pageID';
        $pageList->sqlJoins = '
            LEFT JOIN   wcf1_page page
            ON          page_content.pageID = page.pageID';
        $pageList->getConditionBuilder()->add('page.isMultilingual = ?', [1]);
        $pageList->getConditionBuilder()->add('page.allowSpidersToIndex = ?', [1]);
        $pageList->getConditionBuilder()->add('page_content.languageID IN (?)', [
            \array_keys(LanguageFactory::getInstance()->getLanguages())
        ]);

        return $pageList;
    }

    #[\Override]
    public function canView(DatabaseObject $object)
    {
        $page = new Page($object->pageID);

        if ($page->requireObjectID) {
            return false;
        }

        if (!$page->isVisible()) {
            return false;
        }

        if (!$page->isAccessible()) {
            return false;
        }

        if (!empty($page->controller)) {
            /** @var AbstractPage $pageInstance */
            $pageInstance = new $page->controller();

            if ($pageInstance->loginRequired) {
                return false;
            }

            try {
                // check modules
                $pageInstance->checkModules();

                // check permission
                $pageInstance->checkPermissions();
            } catch (PermissionDeniedException $e) {
                return false;
            } catch (IllegalLinkException $e) {
                return false;
            }
        }

        return true;
    }
}
