<?php

namespace wcf\system\label\object\type;

use wcf\data\article\category\ArticleCategoryNodeTree;
use wcf\data\object\type\ObjectType;
use wcf\system\cache\builder\ArticleCategoryLabelCacheBuilder;

/**
 * Object type handler for article categories.
 *
 * @author      Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       3.1
 */
class ArticleCategoryLabelObjectTypeHandler extends AbstractLabelObjectTypeHandler
{
    #[\Override]
    public function getContainerForObjectType(ObjectType $objectType): LabelObjectTypeContainer
    {
        $container = new LabelObjectTypeContainer(
            $objectType->objectTypeID,
        );
        $categoryTree = new ArticleCategoryNodeTree('com.woltlab.wcf.article.category');
        $categoryList = $categoryTree->getIterator();

        foreach ($categoryList as $category) {
            $container->add(
                new LabelObjectType(
                    $category->getTitle(),
                    $category->categoryID,
                    $category->getDepth() - 1
                )
            );
        }

        return $container;
    }

    #[\Override]
    public function save()
    {
        ArticleCategoryLabelCacheBuilder::getInstance()->reset();
    }
}
