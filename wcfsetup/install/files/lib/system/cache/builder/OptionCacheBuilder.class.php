<?php

namespace wcf\system\cache\builder;

use wcf\data\option\category\OptionCategory;
use wcf\data\option\Option;
use wcf\system\WCF;

/**
 * Caches options and option categories
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class OptionCacheBuilder extends AbstractCacheBuilder
{
    /**
     * option class name
     * @var string
     */
    protected $optionClassName = Option::class;

    /**
     * database table name
     * @var string
     */
    protected $tableName = 'option';

    /**
     * application
     * @var string
     */
    protected $application = 'wcf';

    /**
     * @inheritDoc
     */
    public function rebuild(array $parameters)
    {
        $data = [
            'categories' => [],
            'options' => [],
            'categoryStructure' => [],
            'optionToCategories' => [],
        ];

        // option categories
        $sql = "SELECT      *
                FROM        " . $this->application . "1_" . $this->tableName . "_category
                ORDER BY    showOrder";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();
        while ($category = $statement->fetchObject(OptionCategory::class)) {
            $data['categories'][$category->categoryName] = $category;
            if (!isset($data['categoryStructure'][$category->parentCategoryName])) {
                $data['categoryStructure'][$category->parentCategoryName] = [];
            }

            $data['categoryStructure'][$category->parentCategoryName][] = $category->categoryName;
        }

        // options
        $sql = "SELECT      *
                FROM        " . $this->application . "1_" . $this->tableName . "
                ORDER BY    showOrder";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute();

        while ($row = $statement->fetchArray()) {
            if ($row['optionType'] === 'BBCodeSelect') {
                $row['defaultValue'] = (!empty($row['defaultValue']) ? ',' : '') . 'html';
            }

            /** @var Option $option */
            $option = new $this->optionClassName(null, $row);
            $data['options'][$option->optionName] = $option;
            if (!isset($data['optionToCategories'][$option->categoryName])) {
                $data['optionToCategories'][$option->categoryName] = [];
            }

            $data['optionToCategories'][$option->categoryName][] = $option->optionName;
        }

        return $data;
    }
}
