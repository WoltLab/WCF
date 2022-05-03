<?php

namespace wcf\system\sitemap\object;

use wcf\data\DatabaseObject;
use wcf\data\page\Page;
use wcf\data\page\PageList;
use wcf\page\AbstractPage;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\PermissionDeniedException;

/**
 * Simple page sitemap implementation.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Sitemap\Object
 * @since   3.1
 */
class SimplePageSitemapObject extends AbstractSitemapObjectObjectType
{
    /**
     * @inheritDoc
     */
    public function getObjectClass()
    {
        return Page::class;
    }

    /**
     * @inheritDoc
     */
    public function getObjectList()
    {
        /** @var $pageList PageList */
        $pageList = parent::getObjectList();
        $pageList->getConditionBuilder()->add('isMultilingual = ?', [0]);
        $pageList->getConditionBuilder()->add('page.allowSpidersToIndex = ?', [1]);

        return $pageList;
    }

    /**
     * @inheritDoc
     */
    public function canView(DatabaseObject $object)
    {
        \assert($object instanceof Page);

        if ($object->requireObjectID) {
            return false;
        }

        if (!$object->isVisible()) {
            return false;
        }

        if (!$object->isAccessible()) {
            return false;
        }

        if (!empty($object->controller)) {
            /** @var $page AbstractPage */
            $page = new $object->controller();

            if ($page->loginRequired) {
                return false;
            }

            try {
                // check modules
                $page->checkModules();

                // check permission
                $page->checkPermissions();
            } catch (PermissionDeniedException $e) {
                return false;
            } catch (IllegalLinkException $e) {
                return false;
            }
        }

        return true;
    }
}
