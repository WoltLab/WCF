<?php

namespace wcf\acp\form;

use wcf\data\DatabaseObject;
use wcf\data\IStorableObject;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\field\dependency\ValueFormFieldDependency;
use wcf\system\form\builder\field\IFormField;
use wcf\system\form\builder\field\SelectFormField;
use wcf\system\form\builder\IFormDocument;
use wcf\system\form\option\FormOptionHandler;
use wcf\system\form\option\SharedConfigurationFormFields;

/**
 * Default implementation for a form that adds custom options based on the form option system.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 *
 * @template TIStorableObject of IStorableObject|null
 * @extends AbstractFormBuilderForm<TIStorableObject>
 */
abstract class AbstractFormOptionAddForm extends AbstractFormBuilderForm
{
    #[\Override]
    public function finalizeForm()
    {
        parent::finalizeForm();

        $this->form->getDataHandler()->addProcessor(
            new CustomFormDataProcessor(
                'saveOptionProcessor',
                function (IFormDocument $document, array $parameters) {
                    $configuration = [];

                    foreach ($this->getConfigurationFormFieldIds() as $parameter) {
                        if (!empty($parameters['data'][$parameter])) {
                            $configuration[$parameter] = $parameters['data'][$parameter];
                        }
                        if (\array_key_exists($parameter, $parameters['data'])) {
                            unset($parameters['data'][$parameter]);
                        }
                    }

                    $parameters['data']['configuration'] = \json_encode($configuration, \JSON_THROW_ON_ERROR);

                    return $parameters;
                },
                function (IFormDocument $document, array $data, IStorableObject $object) {
                    \assert($object instanceof DatabaseObject);

                    // @phpstan-ignore property.notFound
                    if ($object->configuration) {
                        $data = \array_merge($data, \json_decode($object->configuration, true, flags: \JSON_THROW_ON_ERROR));
                    }

                    return $data;
                }
            )
        );
    }

    /**
     * @return string[]
     */
    protected function getConfigurationFormFieldIds(): array
    {
        $ids = [];

        foreach (FormOptionHandler::getInstance()->getOptions() as $option) {
            foreach ($option->getConfigurationFormFields() as $formFieldId) {
                $ids[] = $formFieldId;
            }
        }

        return \array_unique($ids);
    }

    /**
     * @return list<IFormField>
     */
    protected function getSharedConfigurationFormFields(): array
    {
        $sharedConfigurationFormFields = new SharedConfigurationFormFields();
        $matrix = [];

        foreach (FormOptionHandler::getInstance()->getOptions() as $option) {
            foreach ($option->getConfigurationFormFields() as $formFieldId) {
                if (!isset($matrix[$formFieldId])) {
                    $matrix[$formFieldId] = [];
                }

                $matrix[$formFieldId][] = $option->getId();
            }
        }

        $formFields = [];

        foreach ($matrix as $formFieldId => $dependencies) {
            $formField = $sharedConfigurationFormFields->getFormField($formFieldId);
            \assert($formField !== null);
            $formField->addDependency(
                ValueFormFieldDependency::create($formFieldId . 'OptionTypeDependency')
                    ->fieldId('optionType')
                    ->values($dependencies)
            );
            $formFields[] = $formField;
        }

        return $formFields;
    }

    protected function getOptionTypeFormField(): SelectFormField
    {
        return SelectFormField::create('optionType')
            ->label('wcf.form.option.optionType')
            ->immutable($this->formAction !== 'create')
            ->options(FormOptionHandler::getInstance()->getSortedOptionTypes())
            ->required();
    }
}
