<?php

namespace wcf\acp\form;

use wcf\data\user\option\category\UserOptionCategory;
use wcf\http\Helper;

/**
 * Shows the form for editing user option categories.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class UserOptionCategoryEditForm extends UserOptionCategoryAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.user.option.category.list';

    /**
     * @inheritDoc
     */
    public string $formAction = 'edit';

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        $this->formObject = Helper::fetchObjectFromQueryParameter(UserOptionCategory::class);
    }

    #[\Override]
    protected function getCategories(): array
    {
        return \array_filter(
            parent::getCategories(),
            fn(int $key) => $key !== $this->formObject->getObjectID(),
            \ARRAY_FILTER_USE_KEY
        );
    }
}
