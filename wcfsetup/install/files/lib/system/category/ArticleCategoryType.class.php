<?php

namespace wcf\system\category;

use wcf\data\article\ArticleAction;
use wcf\data\article\ArticleList;
use wcf\data\category\CategoryEditor;
use wcf\system\WCF;

/**
 * Category type implementation for article categories.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
class ArticleCategoryType extends AbstractCategoryType
{
    /**
     * @inheritDoc
     */
    protected $langVarPrefix = 'wcf.article.category';

    /**
     * @inheritDoc
     */
    protected $forceDescription = false;

    /**
     * @inheritDoc
     */
    protected $maximumNestingLevel = 9;

    /**
     * @inheritDoc
     */
    protected $objectTypes = [
        'com.woltlab.wcf.acl' => 'com.woltlab.wcf.article.category',
        'com.woltlab.wcf.user.objectWatch' => 'com.woltlab.wcf.article.category',
    ];

    /**
     * @inheritDoc
     */
    public function afterDeletion(CategoryEditor $categoryEditor)
    {
        // delete articles with no categories
        $eventList = new ArticleList();
        $eventList->getConditionBuilder()->add("article.categoryID IS NULL");
        $eventList->readObjects();

        if (\count($eventList)) {
            $eventAction = new ArticleAction($eventList->getObjects(), 'delete');
            $eventAction->executeAction();
        }

        parent::afterDeletion($categoryEditor);
    }

    /**
     * @inheritDoc
     */
    public function canAddCategory()
    {
        return $this->canEditCategory();
    }

    /**
     * @inheritDoc
     */
    public function canDeleteCategory()
    {
        return $this->canEditCategory();
    }

    /**
     * @inheritDoc
     */
    public function canEditCategory()
    {
        return WCF::getSession()->getPermission('admin.content.article.canManageCategory');
    }

    /**
     * @inheritDoc
     * @since   5.2
     */
    public function supportsHtmlDescription()
    {
        return true;
    }
}
