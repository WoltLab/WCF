<?php

namespace wcf\system\cache\builder;

use wcf\data\user\option\category\UserOptionCategory;
use wcf\data\user\option\category\UserOptionCategoryCollection;
use wcf\data\user\option\UserOption;
use wcf\data\user\option\UserOptionCollection;

/**
 * Caches user options and categories
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserOptionCacheBuilder extends OptionCacheBuilder
{
    /**
     * @inheritDoc
     */
    protected $optionClassName = UserOption::class;

    /**
     * @inheritDoc
     */
    protected $tableName = 'user_option';

    #[\Override]
    public function rebuild(array $parameters)
    {
        $data = parent::rebuild($parameters);

        // replace the generic option categories and share a single collection
        // so the localized titles of all categories are batch-loaded in one query
        $categories = [];
        foreach ($data['categories'] as $categoryName => $category) {
            $categories[$categoryName] = new UserOptionCategory(null, $category->getData());
        }
        if ($categories !== []) {
            $collection = new UserOptionCategoryCollection(\array_values($categories));
            foreach ($categories as $category) {
                $category->setCollection($collection);
            }
        }
        $data['categories'] = $categories;

        // share a single collection so the localized values of all options are
        // batch-loaded in one query instead of one query per option
        $options = \array_filter(
            \is_array($data['options']) ? $data['options'] : [],
            static fn($option) => $option instanceof UserOption
        );
        if ($options !== []) {
            $collection = new UserOptionCollection(\array_values($options));
            foreach ($options as $option) {
                $option->setCollection($collection);
            }
        }

        return $data;
    }
}
