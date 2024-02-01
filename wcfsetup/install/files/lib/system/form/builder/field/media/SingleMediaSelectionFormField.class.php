<?php

namespace wcf\system\form\builder\field\media;

use wcf\data\media\ViewableMedia;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\IImmutableFormField;
use wcf\system\form\builder\field\TImmutableFormField;
use wcf\system\form\builder\field\validation\FormFieldValidationError;

/**
 * Implementation of a form field to select a single media file.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
final class SingleMediaSelectionFormField extends AbstractFormField implements IImmutableFormField
{
    use TImmutableFormField;

    /**
     * is `true` if only images can be selected and `false` otherwise
     * @var bool
     */
    protected $imageOnly = false;

    /**
     * media object with the current value as id
     * @var null|ViewableMedia
     */
    protected $media;

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Value';

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_singleMediaSelectionFormField';

    /**
     * Returns the media object with the current value as id.
     *
     * @return  ViewableMedia
     *
     * @throws  \InvalidArgumentException   if no or an invalid media id is set as value
     * @throws  \UnexpectedValueException   if no or an invalid media id is set as value
     */
    public function getMedia()
    {
        if ($this->media === null) {
            if (!$this->getValue()) {
                throw new \BadMethodCallException(
                    "Cannot be media object if no valid media id is set as value for field '{$this->getId()}'."
                );
            }

            $this->media = ViewableMedia::getMedia($this->getValue());
            if ($this->media === null) {
                throw new \UnexpectedValueException(
                    "Cannot load media with id '{$this->getValue()}' for field '{$this->getId()}'."
                );
            }
        }

        return $this->media;
    }

    /**
     * Sets if only images can be selected and returns this field.
     *
     * @param bool $imageOnly
     * @return  static              this field
     */
    public function imageOnly($imageOnly = true)
    {
        $this->imageOnly = $imageOnly;

        return $this;
    }

    /**
     * Returns `true` if only images can be selected and `false` otherwise.
     *
     * By default, all images can be selected.
     *
     * @return  bool
     */
    public function isImageOnly()
    {
        return $this->imageOnly;
    }

    /**
     * @inheritDoc
     */
    public function readValue()
    {
        if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
            $value = $this->getDocument()->getRequestData($this->getPrefixedId());

            if ($value) {
                $this->value = $value;
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        parent::validate();

        try {
            $media = $this->getMedia();
            if (!$media->isAccessible() || ($this->isImageOnly() && !$media->isImage)) {
                $this->value = null;
            }
        } catch (\BadMethodCallException $e) {
            $this->value = null;
        } catch (\UnexpectedValueException $e) {
            $this->value = null;
        }

        if (!$this->getValue() && $this->isRequired()) {
            $this->addValidationError(new FormFieldValidationError('empty'));
        }
    }
}
