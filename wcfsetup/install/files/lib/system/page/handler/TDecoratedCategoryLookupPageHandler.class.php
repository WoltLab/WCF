<?php

namespace wcf\system\page\handler;

use wcf\data\category\AbstractDecoratedCategory;
use wcf\data\IAccessibleObject;
use wcf\data\ILinkableObject;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\database\util\PreparedStatementConditionBuilder;
use wcf\system\exception\ImplementationException;
use wcf\system\exception\ParentClassException;
use wcf\system\WCF;

/**
 * Provides the `isValid` and `lookup` methods for looking up decorated categories.
 *
 * Note: This only works in the class extends `AbstractDecoratedCategory` and defines a
 * constant `OBJECT_TYPE_NAME` with the name of the `com.woltlab.wcf.category` object type.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   3.0
 */
trait TDecoratedCategoryLookupPageHandler
{
    /**
     * Returns the name of the decorated class name.
     *
     * @return  string
     */
    abstract protected function getDecoratedCategoryClass();

    /**
     * Returns the link for a page with an object id.
     *
     * @param int $objectID page object id
     * @return  string      page url
     * @see ILookupPageHandler::getLink()
     */
    public function getLink($objectID)
    {
        $className = $this->getDecoratedCategoryClass();

        /** @var AbstractDecoratedCategory $category */
        /** @noinspection PhpUndefinedMethodInspection */
        $category = $className::getCategory($objectID);

        if ($category instanceof ILinkableObject) {
            return $category->getLink();
        }

        throw new \LogicException("If '" . $className . "' does not implement '" . ILinkableObject::class . "', the 'getLink' method needs to be overwritten.");
    }

    /**
     * Returns true if provided object id exists and is valid.
     *
     * @param int $objectID page object id
     * @return  bool        true if object id is valid
     * @see ILookupPageHandler::isValid()
     */
    public function isValid($objectID = null)
    {
        $className = $this->getDecoratedCategoryClass();
        /** @noinspection PhpUndefinedMethodInspection */
        $category = $className::getCategory($objectID);
        if ($category === null) {
            return false;
        }

        if ($category instanceof IAccessibleObject && !$category->isAccessible()) {
            return false;
        }

        return true;
    }

    /**
     * Performs a search for pages using a query string, returning an array containing
     * an `objectID => title` relation.
     *
     * @param string $searchString search string
     * @return  string[]
     * @see ILookupPageHandler::lookup()
     */
    public function lookup($searchString)
    {
        $className = $this->getDecoratedCategoryClass();
        if (!\is_subclass_of($className, AbstractDecoratedCategory::class)) {
            throw new ParentClassException($className, AbstractDecoratedCategory::class);
        }
        if (!\is_subclass_of($className, ILinkableObject::class)) {
            throw new ImplementationException($className, ILinkableObject::class);
        }
        if (!\defined($className . '::OBJECT_TYPE_NAME')) {
            throw new \LogicException("Class '{$className}' has no constant 'OBJECT_TYPE_NAME'.");
        }

        $conditionBuilder = new PreparedStatementConditionBuilder();
        /** @noinspection PhpUndefinedFieldInspection */
        $conditionBuilder->add(
            'category.objectTypeID = ?',
            [
                ObjectTypeCache::getInstance()
                    ->getObjectTypeIDByName('com.woltlab.wcf.category', $className::OBJECT_TYPE_NAME),
            ]
        );
        $conditionBuilder->add(
            '(category.title LIKE ? OR language_item.languageItemValue LIKE ?)',
            ['%' . $searchString . '%', '%' . $searchString . '%']
        );
        $sql = "SELECT      DISTINCT categoryID
                FROM        wcf1_category category
                LEFT JOIN   wcf1_language_item language_item
                ON          language_item.languageItem = category.title
                " . $conditionBuilder;
        $statement = WCF::getDB()->prepare($sql, 10);
        $statement->execute($conditionBuilder->getParameters());
        $results = [];
        while ($categoryID = $statement->fetchColumn()) {
            /** @var AbstractDecoratedCategory|ILinkableObject $category */
            /** @noinspection PhpUndefinedMethodInspection */
            $category = $className::getCategory($categoryID);

            // build hierarchy
            $description = '';
            foreach ($category->getParentCategories() as $parentCategory) {
                $description .= $parentCategory->getTitle() . ' &raquo; ';
            }

            $results[] = [
                'description' => $description,
                'image' => ['folder', false],
                'link' => $category->getLink(),
                'objectID' => $categoryID,
                'title' => $category->getTitle(),
            ];
        }

        return $results;
    }
}
