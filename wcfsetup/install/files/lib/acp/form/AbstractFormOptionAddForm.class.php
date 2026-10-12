<?php

namespace wcf\acp\form;

use wcf\data\DatabaseObject;
use wcf\data\IStorableObject;
use wcf\form\AbstractFormBuilderForm;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\IFormDocument;

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
    use TFormOptionAddForm;

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
                        $formField = $document->getNodeById($parameter);

                        if ($this->isConfigurationValueSet($formField, $parameters['data'][$parameter] ?? null)) {
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
}
