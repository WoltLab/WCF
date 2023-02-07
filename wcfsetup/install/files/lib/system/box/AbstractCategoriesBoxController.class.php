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
                    'resetFilterLink' => $this->getResetFilterLink(),
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

    protected function getResetFilterLink(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function readConditions(): void
    {
        if (!empty($_POST['showChildCategories'])) {
            $this->showChildCategories = true;
        }
    }

    /**
     * @inheritDoc
     */
    public function validateConditions(): void
    {
    }

    /**
     * @inheritDoc
     */
    public function getConditionDefinition(): string
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function getConditionObjectTypes(): array
    {
        return [];
    }

    /**
     * @inheritDoc
     */
    public function getConditionsTemplate(): string
    {
        return WCF::getTPL()->fetch('boxCategoryConditions', 'wcf', [
            'showChildCategories' => $this->showChildCategories,
        ], true);
    }

    /**
     * @inheritDoc
     */
    protected function getAdditionalData(): array
    {
        return [
            'showChildCategories' => $this->showChildCategories,
        ];
    }

    /**
     * @inheritDoc
     */
    public function setBox(Box $box, $setConditionData = true): void
    {
        parent::setBox($box);

        if ($setConditionData && $this->box->showChildCategories) {
            $this->showChildCategories = $this->box->showChildCategories;
        }
    }
}
