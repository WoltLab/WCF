<?php

namespace wcf\system\box;

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
abstract class AbstractCategoriesBoxController extends AbstractBoxController
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
}
