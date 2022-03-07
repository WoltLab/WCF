<?php

namespace wcf\system\label\object\type;

use wcf\data\article\category\ArticleCategoryNode;
use wcf\data\article\category\ArticleCategoryNodeTree;
use wcf\system\cache\builder\ArticleCategoryLabelCacheBuilder;

/**
 * Object type handler for article categories.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Label\Object\Type
 * @since       3.1
 */
class ArticleCategoryLabelObjectTypeHandler extends AbstractLabelObjectTypeHandler
{
    /**
     * category list
     * @var \RecursiveIteratorIterator
     */
    public $categoryList;

    /**
     * @inheritDoc
     */
    protected function init()
    {
        $categoryTree = new ArticleCategoryNodeTree('com.woltlab.wcf.article.category');
        $this->categoryList = $categoryTree->getIterator();
    }

    /**
     * @inheritDoc
     */
    public function setObjectTypeID($objectTypeID)
    {
        parent::setObjectTypeID($objectTypeID);

        $this->container = new LabelObjectTypeContainer($this->objectTypeID);
        /** @var ArticleCategoryNode $category */
        foreach ($this->categoryList as $category) {
            $this->container->add(new LabelObjectType(
                $category->getTitle(),
                $category->categoryID,
                $category->getDepth() - 1
            ));
        }
    }

    /**
     * @inheritDoc
     */
    public function save()
    {
        ArticleCategoryLabelCacheBuilder::getInstance()->reset();
    }
}
