<?php

namespace wcf\acp\form;

use wcf\data\DatabaseObject;
use wcf\data\DatabaseObjectBuilder;
use wcf\form\AbstractDatabaseObjectBuilderForm;
use wcf\system\form\builder\field\IFormField;

/**
 * Default implementation for a form that adds custom options based on the form
 * option system and persists them through a `DatabaseObjectBuilder`.
 *
 * The values of the shared configuration form fields are collected when the
 * option type is written into the builder, see `applyOptionType()`.
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 *
 * @template TDatabaseObject of DatabaseObject|null
 * @template TDatabaseObjectBuilder of DatabaseObjectBuilder
 * @extends AbstractDatabaseObjectBuilderForm<TDatabaseObject, TDatabaseObjectBuilder>
 */
abstract class AbstractFormOptionBuilderForm extends AbstractDatabaseObjectBuilderForm
{
    use TFormOptionAddForm;

    /**
     * Writes the option type and the configuration of the option into the builder.
     *
     * @param TDatabaseObjectBuilder $builder
     * @param array<string, mixed> $configuration
     */
    abstract protected function applyOptionType(
        DatabaseObjectBuilder $builder,
        string $optionType,
        array $configuration
    ): void;

    #[\Override]
    protected function finalizeForm(): void
    {
        parent::finalizeForm();

        $this->form->getFormField('optionType')?->saveValueCallback(
            function (DatabaseObjectBuilder $builder, IFormField $formField) {
                $this->applyOptionType($builder, (string)$formField->getSaveValue(), $this->getConfigurationValues());
            }
        );

        foreach ($this->getConfigurationFormFieldIds() as $formFieldId) {
            $this->form->getFormField($formFieldId)?->loadValueCallback(
                static function (DatabaseObject $object, IFormField $formField) {
                    // @phpstan-ignore property.notFound
                    $configuration = ($object->configuration ?? '') !== ''
                        ? \json_decode($object->configuration, true, flags: \JSON_THROW_ON_ERROR)
                        : [];

                    if (isset($configuration[$formField->getId()])) {
                        $formField->value($configuration[$formField->getId()]);
                    }
                }
            );
        }
    }

    /**
     * Returns the values of the configuration form fields that apply to the
     * selected option type.
     *
     * @return array<string, mixed>
     */
    private function getConfigurationValues(): array
    {
        $configuration = [];

        foreach ($this->getConfigurationFormFieldIds() as $formFieldId) {
            $formField = $this->form->getFormField($formFieldId);
            if ($formField === null || !$formField->isAvailable() || !$formField->checkDependencies()) {
                continue;
            }

            $value = $formField->getSaveValue();
            if ($this->isConfigurationValueSet($formField, $value)) {
                $configuration[$formFieldId] = $value;
            }
        }

        return $configuration;
    }
}
