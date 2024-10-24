<?php

namespace wcf\system\category;

use wcf\data\category\CategoryEditor;
use wcf\data\user\trophy\UserTrophyAction;
use wcf\data\user\trophy\UserTrophyList;
use wcf\system\WCF;

/**
 * Trophy category type.
 *
 * @author  Joshua Ruesweg
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.1
 */
class TrophyCategoryType extends AbstractCategoryType
{
    /**
     * @inheritDoc
     */
    protected $langVarPrefix = 'wcf.trophy.category';

    /**
     * @inheritDoc
     */
    protected $maximumNestingLevel = 0;

    /**
     * @inheritDoc
     */
    protected $forceDescription = false;

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
        return WCF::getSession()->getPermission('admin.trophy.canManageTrophy');
    }

    /**
     * @inheritDoc
     */
    public function beforeDeletion(CategoryEditor $categoryEditor)
    {
        // update user trophyPoints
        $userTrophyList = new UserTrophyList();
        if (!empty($userTrophyList->sqlJoins)) {
            $userTrophyList->sqlJoins .= ' ';
        }
        $userTrophyList->sqlJoins .= '
            LEFT JOIN   wcf1_trophy trophy
            ON          user_trophy.trophyID = trophy.trophyID
            LEFT JOIN   wcf1_category category
            ON          trophy.categoryID = category.categoryID';

        $userTrophyList->getConditionBuilder()->add('trophy.isDisabled = ?', [0]);
        $userTrophyList->getConditionBuilder()->add('category.isDisabled = ?', [0]);
        $userTrophyList->getConditionBuilder()->add('category.categoryID = ?', [$categoryEditor->categoryID]);
        $userTrophyList->readObjects();

        $userTrophyAction = new UserTrophyAction($userTrophyList->getObjects(), 'delete');
        $userTrophyAction->executeAction();
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
