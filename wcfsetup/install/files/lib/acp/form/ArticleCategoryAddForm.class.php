<?php

namespace wcf\acp\form;

use wcf\data\category\Category;
use wcf\data\IStorableObject;
use wcf\system\form\builder\container\FormContainer;
use wcf\system\form\builder\container\TabFormContainer;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\field\SingleSelectionFormField;
use wcf\system\form\builder\field\SortOrderFormField;
use wcf\system\form\builder\IFormDocument;

/**
 * Shows the article category add form.
 *
 * @author      Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package     WoltLabSuite\Core\Acp\Form
 * @since       3.0
 */
class ArticleCategoryAddForm extends AbstractCategoryAddForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.article.category.add';

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_ARTICLE'];

    /**
     * @inheritDoc
     */
    public $objectEditLinkController = ArticleCategoryEditForm::class;

    /**
     * @inheritDoc
     */
    public function getObjectTypeName(): string
    {
        return 'com.woltlab.wcf.article.category';
    }

    /**
     * @inheritDoc
     */
    protected function createForm()
    {
        parent::createForm();

        /** @var TabFormContainer $tabContainer */
        $tabContainer = $this->form->getNodeById('appearanceTab');
        $tabContainer->appendChild(
            FormContainer::create('sorting')
                ->label('wcf.acp.article.category.sorting')
                ->appendChildren([
                    SingleSelectionFormField::create('sortField')
                        ->label('wcf.acp.article.category.sortField')
                        ->required()
                        ->options([
                            'publicationDate' => 'wcf.acp.article.category.sortField.publicationDate',
                            'title' => 'wcf.acp.article.category.sortField.title',
                        ])
                        ->value('publicationDate'),
                    SortOrderFormField::create()
                        ->required()
                        ->value('DESC'),
                ])
        );
    }

    /**
     * @inheritDoc
     */
    public function buildForm()
    {
        parent::buildForm();

        $this->form->getDataHandler()->addProcessor(
            new CustomFormDataProcessor(
                'articleAdditionalData',
                static function (IFormDocument $document, array $parameters) {
                    if (!isset($parameters['additionalData'])) {
                        $parameters['additionalData'] = [];
                    }

                    // move sorting data to additional data
                    $parameters['additionalData']['sortField'] = $parameters['data']['sortField'];
                    unset($parameters['data']['sortField']);
                    $parameters['additionalData']['sortOrder'] = $parameters['data']['sortOrder'];
                    unset($parameters['data']['sortOrder']);

                    return $parameters;
                },
                static function (IFormDocument $document, array $data, IStorableObject $object) {
                    \assert($object instanceof Category);

                    /** @var SingleSelectionFormField $sortField */
                    $sortField = $this->form->getNodeById('sortField');
                    if (isset($object->sortField) && \in_array($object->sortField, \array_keys($sortField->getOptions()))) {
                        $data['sortField'] = $object->sortField;
                    }

                    if (isset($object->sortOrder)) {
                        $data['sortOrder'] = $object->sortOrder;
                    }

                    return $data;
                }
            )
        );
    }
}
