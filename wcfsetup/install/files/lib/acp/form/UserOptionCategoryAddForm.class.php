<?php

namespace wcf\acp\form;

use wcf\data\IStorableObject;
use wcf\data\user\option\category\UserOptionCategory;
use wcf\data\user\option\category\UserOptionCategoryAction;
use wcf\data\user\option\category\UserOptionCategoryEditor;
use wcf\data\user\option\category\UserOptionCategoryList;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\field\ShowOrderFormField;
use wcf\system\form\builder\field\TextFormField;
use wcf\system\form\builder\IFormDocument;
use wcf\system\language\I18nHandler;

/**
 * Shows the form for adding new user option categories.
 *
 * @author      Olaf Braun, Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 *
 * @extends AbstractFormBuilderForm<UserOptionCategory>
 */
class UserOptionCategoryAddForm extends AbstractFormBuilderForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.user.option.category.add';

    /**
     * @inheritDoc
     */
    public $neededPermissions = ['admin.user.canManageUserOption'];

    /**
     * @inheritDoc
     */
    public $objectActionClass = UserOptionCategoryAction::class;

    /**
     * @inheritDoc
     */
    public $objectEditLinkController = UserOptionCategoryEditForm::class;

    #[\Override]
    protected function createForm()
    {
        parent::createForm();

        $this->form->appendChildren([
            FormContainer::create('general')
                ->appendChildren([
                    TextFormField::create('categoryName')
                        ->required()
                        ->label('wcf.global.name')
                        ->i18n()
                        ->i18nRequired()
                        ->languageItemPattern('wcf.user.option.category.(category\d+|[\w\.]+)'),
                    ShowOrderFormField::create()
                        ->options(function () {
                            $categoryList = new UserOptionCategoryList();
                            $categoryList->getConditionBuilder()->add('parentCategoryName = ?', ['profile']);
                            $categoryList->readObjects();
                            $categories = [];

                            foreach ($categoryList->getObjects() as $category) {
                                $categories[$category->categoryID] = $category->getTitle();
                            }

                            return $categories;
                        }),
                ]),
        ]);
    }

    #[\Override]
    protected function finalizeForm()
    {
        parent::finalizeForm();

        $this->form->getDataHandler()
            ->addProcessor(
                new CustomFormDataProcessor(
                    'categoryName',
                    function (IFormDocument $document, array $parameters) {
                        // These category name is unconditionally stored in a
                        // phrase and never in actual columns as it is usually
                        // the case with the `I18nHandler`.
                        unset($parameters['data']['categoryName']);

                        return $parameters;
                    },
                    function (IFormDocument $document, array $data, IStorableObject $object) {
                        \assert($object instanceof UserOptionCategory);
                        $data['categoryName'] = 'wcf.user.option.category.' . $object->categoryName;

                        return $data;
                    }
                ),
            );
    }

    #[\Override]
    public function save()
    {
        if ($this->formAction === 'create') {
            $this->additionalFields['parentCategoryName'] = 'profile';
        }

        parent::save();
    }

    #[\Override]
    public function saved()
    {
        $returnValues = $this->objectAction->getReturnValues();

        $categoryID = $returnValues['returnValues']->categoryID;
        I18nHandler::getInstance()->save(
            'categoryName',
            'wcf.user.option.category.category' . $categoryID,
            'wcf.user.option'
        );
        $categoryEditor = new UserOptionCategoryEditor($returnValues['returnValues']);
        $categoryEditor->update([
            'categoryName' => 'category' . $categoryID,
        ]);

        parent::saved();
    }
}
