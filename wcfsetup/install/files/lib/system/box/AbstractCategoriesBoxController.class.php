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

    #[\Override]
    protected function loadContent()
    {
        $categoryTree = $this->getNodeTree();
        $categoryList = $categoryTree->getIterator();

        if (\iterator_count($categoryList) > 0) {
            $this->content = WCF::getTPL()->render(
                'wcf',
                'boxCategories',
                [
                    'categoryList' => $categoryList,
                    'activeCategory' => $this->getActiveCategory(),
                    'resetFilterLink' => $this->getResetFilterLink(),
                    'showChildCategories' => $this->showChildCategories,
                ]
            );
        }
    }

    abstract protected function getNodeTree(): CategoryNodeTree;

    protected function getActiveCategory(): ?AbstractDecoratedCategory
    {
        return null;
    }

    protected function getResetFilterLink(): string
    {
        return '';
    }

    #[\Override]
    public function readConditions(): void
    {
        if (!empty($_POST['showChildCategories'])) {
            $this->showChildCategories = true;
        }
    }

    #[\Override]
    public function validateConditions(): void {}

    #[\Override]
    public function getConditionDefinition(): string
    {
        return '';
    }

    #[\Override]
    public function getConditionObjectTypes(): array
    {
        return [];
    }

    #[\Override]
    public function getConditionsTemplate(): string
    {
        return WCF::getTPL()->render('wcf', 'boxCategoryConditions', [
            'showChildCategories' => $this->showChildCategories,
        ]);
    }

    #[\Override]
    protected function getAdditionalData(): array
    {
        return [
            'showChildCategories' => $this->showChildCategories,
        ];
    }

    #[\Override]
    public function setBox(Box $box, bool $setConditionData = true): void
    {
        parent::setBox($box);

        // @phpstan-ignore property.notFound
        if ($setConditionData && $this->box->showChildCategories) {
            $this->showChildCategories = $this->box->showChildCategories;
        }
    }
}
