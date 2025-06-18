<?php

namespace wcf\system\form\builder\field;

use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\IFormDocument;

/**
 * Implementation of a form field for selecting map coordinates.
 *
 * @author      Marcel Werk
 * @copyright   2001-2025 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.2
 */
final class GoogleMapsFormField extends AbstractFormField implements
    IAttributeFormField,
    IAutoFocusFormField,
    ICssClassFormField,
    IImmutableFormField,
    IPlaceholderFormField
{
    use TAttributeFormField;
    use TAutoFocusFormField;
    use TCssClassFormField;
    use TImmutableFormField;
    use TPlaceholderFormField;

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/GoogleMaps';

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_googleMapsFormField';

    private float $latitude = 0;
    private float $longitude = 0;

    public function __construct()
    {
        $this->addFieldClass('long');
    }

    #[\Override]
    public function readValue()
    {
        if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
            $this->value = $this->getDocument()->getRequestData($this->getPrefixedId());
        }

        if ($this->getDocument()->hasRequestData($this->getPrefixedId() . '_coordinates')) {
            $coordinates = explode(',', $this->getDocument()->getRequestData(
                $this->getPrefixedId() . '_coordinates'
            ));
            if (\count($coordinates) === 2) {
                $this->latitude = \floatval($coordinates[0]);
                $this->longitude = \floatval($coordinates[1]);
            }
        }

        return $this;
    }

    #[\Override]
    public function populate()
    {
        parent::populate();

        $this->getDocument()->getDataHandler()->addProcessor(new CustomFormDataProcessor(
            'coordinates',
            function (IFormDocument $document, array $parameters) {
                if ($this->getValue()) {
                    $parameters[$this->getPrefixedId() . '_coordinates'] = [
                        'latitude' => $this->getLatitude(),
                        'longitude' => $this->getLongitude(),
                    ];
                }

                return $parameters;
            }
        ));

        return $this;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function coordinates(float $latitude, float $longitude): static
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;

        return $this;
    }
}
