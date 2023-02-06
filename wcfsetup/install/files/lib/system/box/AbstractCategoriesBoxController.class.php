<?php

namespace wcf\system\box;

use wcf\data\box\Box;
use wcf\data\category\AbstractDecoratedCategory;
use wcf\data\category\CategoryNodeTree;
use wcf\system\WCF;

/**
 * Abstract implmentation for category box.
 *
 * @author  Marcel Werk
 * @copyright   2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   6.0
 */
abstract class AbstractCategoriesBoxController extends AbstractBoxController implements IConditionBoxController
{
    /**
     * @inheritDoc
     */
    protected static $supportedPositions = [
        'footerBoxes',
        'sidebarLeft',
        'sidebarRight',
        'contentTop',
        'contentBottom',
        'footer',
    ];

    protected bool $showChildCategories = false;

    /**
     * @inheritDoc
     */
    protected function loadContent()
    {
        $categoryTree = $this->getNodeTree();
        $categoryList = $categoryTree->getIterator();

        if (\iterator_count($categoryList)) {
            $this->content = WCF::getTPL()->fetch(
                'boxCategories',
                'wcf',
                [
                    'categoryList' => $categoryList,
                    'activeCategory' => $this->getActiveCategory(),
                    'resetLink' => $this->getResetLink(),
                    'showChildCategories' => $this->showChildCategories,
                ],
                true
            );
        }
    }

    protected abstract function getNodeTree(): CategoryNodeTree;

    protected function getActiveCategory(): ?AbstractDecoratedCategory
    {
        return null;
    }

    protected function getResetLink(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function readConditions()
    {
        if (!empty($_POST['showChildCategories'])) {
            $this->showChildCategories = true;
        }
    }

    /**
     * @inheritDoc
     */
    public function validateConditions()
    {
    }

    /**
     * @inheritDoc
     */
    public function getConditionDefinition()
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getConditionObjectTypes()
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getConditionsTemplate()
    {
        return WCF::getTPL()->fetch('boxCategoryConditions', 'wcf', [
            'showChildCategories' => $this->showChildCategories,
        ], true);
    }

    /**
     * @inheritDoc
     */
    protected function getAdditionalData()
    {
        return [
            'showChildCategories' => $this->showChildCategories,
        ];
    }

    /**
     * @inheritDoc
     */
    public function setBox(Box $box, $setConditionData = true)
    {
        parent::setBox($box);

        if ($setConditionData) {
            $this->showChildCategories = $this->box->showChildCategories;
        }
    }
}
