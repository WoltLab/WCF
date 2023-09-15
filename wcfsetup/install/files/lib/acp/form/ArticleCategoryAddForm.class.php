<?php

namespace wcf\acp\form;

use wcf\data\category\Category;
use wcf\data\IStorableObject;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\form\builder\field\SortOrderFormField;
use wcf\system\form\builder\IFormDocument;

/**
 * Shows the article category add form.
 *
 * @author      Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       3.0
 */
class ArticleCategoryAddForm extends CategoryAddFormBuilderForm
{
    /**
     * @inheritDoc
     */
    public $activeMenuItem = 'wcf.acp.menu.link.article.category.add';

    /**
     * @inheritDoc
     */
    public string $objectTypeName = 'com.woltlab.wcf.article.category';

    /**
     * @inheritDoc
     */
    public $neededModules = ['MODULE_ARTICLE'];

    /**
     * @inheritDoc
     */
    protected function getGeneralFormFields(): array
    {
        $formFields = parent::getGeneralFormFields();

        return [
            ...$formFields,
            SelectFormField::create('sortField')
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
        ];
    }

    /**
     * @inheritDoc
     */
    public function finalizeForm()
    {
        $this->form->getDataHandler()->addProcessor(
            new CustomFormDataProcessor(
                'articleSortFields',
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
                function (IFormDocument $document, array $data, IStorableObject $object) {
                    \assert($object instanceof Category);

                    $sortField = $this->form->getNodeById('sortField');
                    \assert($sortField instanceof SelectFormField);

                    if ($object->sortField && \in_array($object->sortField, \array_keys($sortField->getOptions()))) {
                        $data['sortField'] = $object->sortField;
                    }

                    if ($object->sortOrder) {
                        $data['sortOrder'] = $object->sortOrder;
                    }

                    return $data;
                }
            )
        );

        parent::finalizeForm();
    }
}
