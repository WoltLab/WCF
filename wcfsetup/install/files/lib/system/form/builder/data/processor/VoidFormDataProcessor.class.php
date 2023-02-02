<?php

namespace wcf\system\form\builder\data\processor;

use wcf\system\form\builder\IFormDocument;

/**
 * Field data processor implementation that voids a certain data property.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
final class VoidFormDataProcessor extends AbstractFormDataProcessor
{
    /**
     * is `true` if the property is stored in the `data` array
     */
    private bool $isDataProperty;

    /**
     * name of the voided property
     */
    private string $property;

    /**
     * Initializes a new CustomFormFieldDataProcessor object.
     *
     * @param $property name of the voided property
     * @param $isDataProperty is `true` if the property is stored in the `data` array
     */
    public function __construct(string $property, bool $isDataProperty = true)
    {
        $this->property = $property;
        $this->isDataProperty = $isDataProperty;
    }

    /**
     * @inheritDoc
     */
    public function processFormData(IFormDocument $document, array $parameters)
    {
        if ($this->isDataProperty) {
            if (\array_key_exists($this->property, $parameters['data'])) {
                unset($parameters['data'][$this->property]);
            }
        } elseif (\array_key_exists($this->property, $parameters)) {
            unset($parameters[$this->property]);
        }

        return $parameters;
    }
}
