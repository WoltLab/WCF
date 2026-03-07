<?php

namespace wcf\acp\form;

use CuyZ\Valinor\Mapper\MappingError;
use wcf\data\user\option\category\UserOptionCategory;
use wcf\form\AbstractFormBuilderForm;
use wcf\http\Helper;
use wcf\system\exception\IllegalLinkException;
use wcf\system\language\I18nHandler;

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
    public $formAction = 'edit';

    #[\Override]
    public function readParameters()
    {
        parent::readParameters();

        try {
            $queryParameters = Helper::mapQueryParameters(
                $_GET,
                <<<'EOT'
                    array {
                        id: positive-int
                    }
                    EOT
            );
            $this->formObject = new UserOptionCategory($queryParameters['id']);

            if (!$this->formObject->getObjectID()) {
                throw new IllegalLinkException();
            }
        } catch (MappingError) {
            throw new IllegalLinkException();
        }
    }

    #[\Override]
    public function saved()
    {
        I18nHandler::getInstance()->save(
            'categoryName',
            'wcf.user.option.category.' . $this->formObject->categoryName,
            'wcf.user.option'
        );

        AbstractFormBuilderForm::saved();
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
