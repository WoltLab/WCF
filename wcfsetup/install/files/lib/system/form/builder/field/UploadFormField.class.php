<?php

namespace wcf\system\form\builder\field;

use wcf\data\IStorableObject;
use wcf\system\file\upload\UploadField;
use wcf\system\file\upload\UploadFile;
use wcf\system\file\upload\UploadHandler;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\IFormDocument;
use wcf\util\ImageUtil;

/**
 * Implementation of a form field for to uploads.
 *
 * @author  Florian Gail, Joshua Ruesweg
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
class UploadFormField extends AbstractFormField
{
    use TMaximumFormField {
        maximum as traitMaximum;
    }
    use TMinimumFormField;

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Value';

    /**
     * This flag indicates whether only images can uploaded via this field.
     * <strong>Attention</strong>: SVG images can contain bad code, therefore do not
     * use this option, outside the acp or check the file whether remote code is contained.
     * @var bool
     */
    protected $imageOnly = false;

    /**
     * This flag indicates whether SVG images are treated as image in the image only mode.
     * @var bool
     */
    protected $allowSvgImage = false;

    /**
     * Acceptable file types.
     * @var null|string[]
     * @since 5.3
     */
    protected $acceptableFiles;

    /**
     * maximum filesize for each uploaded file
     * @var null|number
     */
    protected $maximumFilesize;

    /**
     * minimum image width for each uploaded file
     * @var null|number
     */
    protected $minimumImageWidth;

    /**
     * maximum image width for each uploaded file
     * @var null|number
     */
    protected $maximumImageWidth;

    /**
     * minimum image height for each uploaded file
     * @var null|number
     */
    protected $minimumImageHeight;

    /**
     * maximum image height for each uploaded file
     * @var null|number
     */
    protected $maximumImageHeight;

    /**
     * @inheritDoc
     */
    protected $templateName = '__uploadFormField';

    /**
     * Array of temporary values, which are assigned, after the method `populate` are called.
     * @var array
     */
    private $values = [];

    /**
     * Flag whether the field is already unregistered.
     * @var bool
     */
    private $cleaned = false;

    /**
     * Flag whether the field is already registered.
     * @var bool
     * @since 5.4
     */
    private $isRegistered = false;

    /**
     * Unregisters the current field in the upload handler.
     */
    private function unregisterField()
    {
        if (UploadHandler::getInstance()->isRegisteredFieldId($this->getPrefixedId())) {
            UploadHandler::getInstance()->unregisterUploadField($this->getPrefixedId());

            $this->isRegistered = false;
        }
    }

    /**
     * Returns `true` if the current field has already registered with the upload handler.
     *
     * @since 5.4
     */
    protected function isRegistered(): bool
    {
        return $this->isRegistered;
    }

    /**
     * Builds the UploadField class.
     *
     * @return      UploadField
     */
    protected function buildUploadField()
    {
        $uploadField = new UploadField($this->getPrefixedId());
        $uploadField->maxFiles = $this->getMaximum();
        $uploadField->setImageOnly($this->isImageOnly());
        if ($this->isImageOnly()) {
            $uploadField->setAllowSvgImage($this->svgImageAllowed());
        }
        $uploadField->setAcceptableFiles($this->getAcceptableFiles());

        return $uploadField;
    }

    /**
     * @inheritDoc
     * @return      UploadFile[]
     * @throws      \BadMethodCallException         if the method is called, before the field is populated
     */
    public function getValue()
    {
        if (!$this->isPopulated) {
            throw new \BadMethodCallException(
                "The field '{$this->getId()}' must be populated, before calling this method."
            );
        }

        if (!$this->isRegistered()) {
            $this->registerField();
        }

        return UploadHandler::getInstance()->getFilesByFieldId($this->getPrefixedId());
    }

    /**
     * Returns the removed files for the field.
     *
     * @param bool $processFiles
     * @return      UploadFile[]
     * @throws      \BadMethodCallException         if the method is called, before the field is populated
     */
    public function getRemovedFiles($processFiles = false)
    {
        if (!$this->isPopulated) {
            throw new \BadMethodCallException(
                "The field '{$this->getId()}' must be populated, before calling the method."
            );
        }

        if (!$this->isRegistered()) {
            $this->registerField();
        }

        return UploadHandler::getInstance()->getRemovedFilesByFieldId($this->getPrefixedId(), $processFiles);
    }

    /**
     * @inheritDoc
     * @throws      \BadMethodCallException         if the method is called, before the field is populated
     */
    public function readValue()
    {
        if (!$this->isPopulated) {
            throw new \BadMethodCallException(
                "The field '{$this->getId()}' must be populated, before calling the method."
            );
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function validate()
    {
        parent::validate();

        if (empty($this->getValue())) {
            if ($this->isRequired()) {
                $this->addValidationError(new FormFieldValidationError('empty'));
            }
        }

        if ($this->getMinimum() !== null && \count($this->getValue()) < $this->getMinimum()) {
            $this->addValidationError(new FormFieldValidationError(
                'minimum',
                'wcf.form.field.upload.error.minimum',
                ['minimum' => $this->getMinimum()]
            ));
        } elseif ($this->getMaximum() !== null && \count($this->getValue()) > $this->getMaximum()) {
            $this->addValidationError(new FormFieldValidationError(
                'maximum',
                'wcf.form.field.upload.error.maximum',
                ['maximum' => $this->getMaximum()]
            ));
        }

        if ($this->getMaximumFilesize() !== null) {
            foreach ($this->getValue() as $file) {
                $filesize = \filesize($file->getLocation());
                if ($filesize > $this->getMaximumFilesize()) {
                    $this->addValidationError(new FormFieldValidationError(
                        'maximumFilesize',
                        'wcf.form.field.upload.error.maximumFilesize',
                        [
                            'maximumFilesize' => $this->getMaximumFilesize(),
                            'file' => $file,
                        ]
                    ));
                }
            }
        }

        if (
            $this->getMinimumImageWidth() !== null
            || $this->getMaximumImageWidth() !== null
            || $this->getMinimumImageHeight() !== null
            || $this->getMaximumImageHeight() !== null
        ) {
            foreach ($this->getValue() as $file) {
                $imagesize = \getimagesize($file->getLocation());

                if ($this->getMinimumImageWidth() !== null && $this->getMinimumImageWidth() > $imagesize[0]) {
                    $this->addValidationError(new FormFieldValidationError(
                        'minimumImageWidth',
                        'wcf.form.field.upload.error.minimumImageWidth',
                        [
                            'minimumImageWidth' => $this->getMinimumImageWidth(),
                            'file' => $file,
                        ]
                    ));
                } elseif ($this->getMaximumImageWidth() !== null && $imagesize[0] > $this->getMaximumImageWidth()) {
                    $this->addValidationError(new FormFieldValidationError(
                        'maximumImageWidth',
                        'wcf.form.field.upload.error.maximumImageWidth',
                        [
                            'maximumImageWidth' => $this->getMaximumImageWidth(),
                            'file' => $file,
                        ]
                    ));
                }

                if ($this->getMinimumImageHeight() !== null && $this->getMinimumImageHeight() > $imagesize[1]) {
                    $this->addValidationError(new FormFieldValidationError(
                        'minimumImageHeight',
                        'wcf.form.field.upload.error.minimumImageHeight',
                        [
                            'minimumImageHeight' => $this->getMinimumImageHeight(),
                            'file' => $file,
                        ]
                    ));
                } elseif ($this->getMaximumImageHeight() !== null && $imagesize[1] > $this->getMaximumImageHeight()) {
                    $this->addValidationError(new FormFieldValidationError(
                        'maximumImageHeight',
                        'wcf.form.field.upload.error.maximumImageHeight',
                        [
                            'maximumImageHeight' => $this->getMaximumImageHeight(),
                            'file' => $file,
                        ]
                    ));
                }
            }
        }
    }

    /**
     * @inheritDoc
     * @throws      \BadMethodCallException         if the method is called, before the field is populated
     */
    public function getHtml()
    {
        if (!$this->isPopulated) {
            throw new \BadMethodCallException(
                "The field '{$this->getId()}' must be populated, before calling the method."
            );
        }

        if (!$this->isRegistered()) {
            $this->registerField();
        }

        return parent::getHtml();
    }

    /**
     * @inheritDoc
     * @throws      \BadMethodCallException         if the method is called, before the field is populated
     */
    public function getFieldHtml()
    {
        if (!$this->isPopulated) {
            throw new \BadMethodCallException(
                "The field '{$this->getId()}' must be populated, before calling the method."
            );
        }

        if (!$this->isRegistered()) {
            $this->registerField();
        }

        return parent::getFieldHtml();
    }

    /**
     * @inheritDoc
     *
     * @throws \InvalidArgumentException    If the getter for the value or the object's property provides invalid values.
     */
    public function updatedObject(array $data, IStorableObject $object, $loadValues = true)
    {
        if ($loadValues) {
            $propertyName = \ucfirst($this->getObjectProperty());
            $className = \get_class($object);

            if (\method_exists($object, "get{$propertyName}UploadFiles")) {
                try {
                    $value = $this->readUploadFiles(
                        \call_user_func([
                            $object,
                            "get{$propertyName}UploadFiles",
                        ])
                    );
                } catch (\InvalidArgumentException | \TypeError $e) {
                    throw new \InvalidArgumentException(
                        \sprintf(
                            "The method %s must return an array of objects of class '%s' with the valid file locations for field '%s'.",
                            "{$className}::get{$propertyName}UploadFiles()",
                            UploadFile::class,
                            $this->getId()
                        ),
                        0,
                        $e
                    );
                }
            } elseif (\method_exists($object, "get{$propertyName}UploadFileLocations")) {
                try {
                    $value = $this->readUploadLocations(
                        \call_user_func([
                            $object,
                            "get{$propertyName}UploadFileLocations",
                        ])
                    );
                } catch (\InvalidArgumentException | \TypeError $e) {
                    throw new \InvalidArgumentException(
                        \sprintf(
                            "The method %s must return an array of strings with the valid file locations for field '%s'.",
                            "{$className}::get{$propertyName}UploadFileLocations()",
                            $this->getId()
                        ),
                        0,
                        $e
                    );
                }
            } elseif (\method_exists($object, "get{$propertyName}")) {
                try {
                    $value = $this->readUploadLocations(
                        \call_user_func([
                            $object,
                            "get{$propertyName}",
                        ])
                    );
                } catch (\InvalidArgumentException | \TypeError $e) {
                    throw new \InvalidArgumentException(
                        \sprintf(
                            "The method %s must return an array of strings with the valid file locations for field '%s'.",
                            "{$className}::get{$propertyName}()",
                            $this->getId()
                        ),
                        0,
                        $e
                    );
                }
            } else {
                $variableName = $this->getObjectProperty();
                try {
                    $value = $this->readUploadLocations($data[$variableName] ?? null);
                } catch (\InvalidArgumentException | \TypeError $e) {
                    throw new \InvalidArgumentException(
                        \sprintf(
                            "The property %s must contain an array of strings with the valid file locations for field '%s'.",
                            "{$className}::\${$variableName}",
                            $this->getId()
                        ),
                        0,
                        $e
                    );
                }
            }

            $this->value($value);
        }

        return $this;
    }

    /**
     * Reads an array of `UploadFile` objects and validates it.
     *
     * @param UploadFile[] $data
     * @return UploadFile[]
     * @throws \InvalidArgumentException
     * @since 5.5
     */
    private function readUploadFiles(array $data): array
    {
        foreach ($data as $value) {
            if (!($value instanceof UploadFile)) {
                throw new \InvalidArgumentException("At least one value is not an instance of '" . UploadFile::class . "'.");
            }
        }

        return $data;
    }

    /**
     * Reads an array of strings containing file locations and validates it.
     *
     * @param string[] $data
     * @return UploadFile[]
     * @throws \InvalidArgumentException
     * @since 5.5
     */
    private function readUploadLocations(array $data): array
    {
        foreach ($data as $key => $value) {
            if (!\is_string($value)) {
                throw new \InvalidArgumentException('At least one value is not a string.');
            }

            if (!\file_exists($value)) {
                throw new \InvalidArgumentException("File '{$value}' could not be found.");
            }

            $data[$key] = new UploadFile(
                $value,
                \basename($value),
                ImageUtil::isImage($value, \basename($value), $this->svgImageAllowed()),
                true,
                $this->svgImageAllowed()
            );
        }

        return $data;
    }

    /**
     * @inheritDoc
     *
     * @param UploadFile[] $value
     *
     * @throws      \InvalidArgumentException       if the value is not an array
     * @throws      \InvalidArgumentException       if the value contains objects, which are not an instance of UploadFile
     */
    public function value($value)
    {
        if (!\is_array($value)) {
            throw new \InvalidArgumentException("Given value must be an array for field '{$this->getId()}'.");
        }

        foreach ($value as $file) {
            if (!($file instanceof UploadFile)) {
                throw new \InvalidArgumentException(
                    "All given files must be an instance of " . UploadFile::class . " for field '{$this->getId()}'."
                );
            }
        }

        if ($this->isRegistered()) {
            UploadHandler::getInstance()->registerFilesByField($this->getPrefixedId(), $value);
        } else {
            $this->values = $value;
        }
    }

    /**
     * @inheritDoc
     */
    public function hasSaveValue()
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function cleanup(): static
    {
        if (!$this->cleaned) {
            $this->unregisterField();
            $this->cleaned = true;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function populate()
    {
        parent::populate();

        $this->getDocument()->getDataHandler()->addProcessor(new CustomFormDataProcessor(
            'upload',
            function (IFormDocument $document, array $parameters) {
                $parameters[$this->getObjectProperty()] = $this->getValue();
                $parameters[$this->getObjectProperty() . '_removedFiles'] = $this->getRemovedFiles(true);

                return $parameters;
            }
        ));

        return $this;
    }

    /**
     * @since 5.4
     * @throws \BadMethodCallException if the field is already registered
     */
    protected function registerField(): void
    {
        if ($this->isRegistered) {
            throw new \BadMethodCallException("The field '{$this->getId()}' is already registered.");
        }

        if (!$this->isPopulated) {
            throw new \BadMethodCallException("The field '{$this->getId()}' is not populated yet.");
        }

        UploadHandler::getInstance()->registerUploadField(
            $this->buildUploadField(),
            $this->getDocument()->getRequestData()
        );

        if (!empty($this->values)) {
            UploadHandler::getInstance()->registerFilesByField($this->getPrefixedId(), $this->values);
        }

        $this->isRegistered = true;
    }

    /**
     * @inheritDoc
     *
     * @throws      \LogicException       if the field has already been initialized
     */
    public function maximum($maximum = null)
    {
        if ($this->isRegistered()) {
            throw new \LogicException(
                "The field '{$this->getId()}' has already been registered. Therefore no modifications are allowed."
            );
        }

        return $this->traitMaximum($maximum);
    }

    /**
     * Sets the maximum filesize for each upload. If `null` is passed, the
     * maximum filesize is removed.
     *
     * @param null|number $maximumFilesize maximum filesize
     * @return  static                      this field
     *
     * @throws  \InvalidArgumentException   if the given maximum filesize is no number or otherwise invalid
     */
    public function maximumFilesize($maximumFilesize = null)
    {
        if ($maximumFilesize !== null) {
            if (!\is_numeric($maximumFilesize)) {
                throw new \InvalidArgumentException(
                    "Given maximum filesize is no int, '" . \gettype($maximumFilesize) . "' given for field '{$this->getId()}'."
                );
            }
        }

        $this->maximumFilesize = $maximumFilesize;

        return $this;
    }

    /**
     * Returns the maximum filesize of each file or `null` if no maximum filesize
     * has been set.
     *
     * @return  null|number
     */
    public function getMaximumFilesize()
    {
        return $this->maximumFilesize;
    }

    /**
     * Sets the minimum image width for each uploaded file. If `null` is passed, the
     * minimum image width is removed.
     *
     * @param null|number $minimumImageWidth the mimimum image width
     * @return  static                     this field
     *
     * @throws  \InvalidArgumentException   if the given mimimum image width is no number or otherwise invalid
     * @throws  \LogicException                  if the form field is not marked as image only
     */
    public function minimumImageWidth($minimumImageWidth = null)
    {
        if (!$this->isImageOnly()) {
            throw new \LogicException("The field '{$this->getId()}' must be image only to set a minimum image width.");
        }

        if ($minimumImageWidth !== null) {
            if (!\is_numeric($minimumImageWidth)) {
                throw new \InvalidArgumentException(
                    "Given minimum image width is no int, '" . \gettype($minimumImageWidth) . "' given for field '{$this->getId()}'."
                );
            }

            $maximumImageWidth = $this->getMaximumImageWidth();
            if ($maximumImageWidth !== null && $minimumImageWidth > $maximumImageWidth) {
                throw new \InvalidArgumentException(
                    "Minimum image width ({$minimumImageWidth}) cannot be greater than maximum image width ({$maximumImageWidth}) for field '{$this->getId()}'."
                );
            }
        }

        $this->minimumImageWidth = $minimumImageWidth;

        return $this;
    }

    /**
     * Returns the mimimum image width of each file or `null` if no mimimum image width
     * has been set.
     *
     * @return  null|number
     */
    public function getMinimumImageWidth()
    {
        return $this->minimumImageWidth;
    }

    /**
     * Sets the maximum image width for each uploaded file. If `null` is passed, the
     * maximum image width is removed.
     *
     * @param null|number $maximumImageWidth the maximum image width
     * @return  static                     this field
     *
     * @throws  \InvalidArgumentException   if the given mimimum image width is no number or otherwise invalid
     * @throws  \LogicException                  if the form field is not marked as image only
     */
    public function maximumImageWidth($maximumImageWidth = null)
    {
        if (!$this->isImageOnly()) {
            throw new \LogicException("The field '{$this->getId()}' must be image only to set a maximum image width.");
        }

        if ($maximumImageWidth !== null) {
            if (!\is_numeric($maximumImageWidth)) {
                throw new \InvalidArgumentException(
                    "Given maximum image width is no int, '" . \gettype($maximumImageWidth) . "' given for field '{$this->getId()}'."
                );
            }

            $minimumImageWidth = $this->getMinimumImageWidth();
            if ($maximumImageWidth !== null && $minimumImageWidth > $maximumImageWidth) {
                throw new \InvalidArgumentException(
                    "Maximum image width ({$maximumImageWidth}) cannot be smaller than minimum image width ({$minimumImageWidth}) for field '{$this->getId()}'."
                );
            }
        }

        $this->maximumImageWidth = $maximumImageWidth;

        return $this;
    }

    /**
     * Returns the maximum image width of each file or `null` if no maximum image width
     * has been set.
     *
     * @return  null|number
     */
    public function getMaximumImageWidth()
    {
        return $this->maximumImageWidth;
    }

    /**
     * Sets the minimum image height for each uploaded file. If `null` is passed, the
     * minimum image height is removed.
     *
     * @param null|number $minimumImageHeight the mimimum image height
     * @return  static                      this field
     *
     * @throws  \InvalidArgumentException   if the given mimimum image height is no number or otherwise invalid
     * @throws  \LogicException                  if the form field is not marked as image only
     */
    public function minimumImageHeight($minimumImageHeight = null)
    {
        if (!$this->isImageOnly()) {
            throw new \LogicException(
                "The field '{$this->getId()}' must be image only to set a minimum image height."
            );
        }

        if ($minimumImageHeight !== null) {
            if (!\is_numeric($minimumImageHeight)) {
                throw new \InvalidArgumentException(
                    "Given minimum image height is no int, '" . \gettype($minimumImageHeight) . "' given for field '{$this->getId()}'."
                );
            }

            $maximumImageHeight = $this->getMaximumImageHeight();
            if ($maximumImageHeight !== null && $minimumImageHeight > $maximumImageHeight) {
                throw new \InvalidArgumentException(
                    "Minimum image height ({$minimumImageHeight}) cannot be greater than maximum image height ({$maximumImageHeight}) for field '{$this->getId()}'."
                );
            }
        }

        $this->minimumImageHeight = $minimumImageHeight;

        return $this;
    }

    /**
     * Returns the mimimum image height of each file or `null` if no mimimum image height
     * has been set.
     *
     * @return  null|number
     */
    public function getMinimumImageHeight()
    {
        return $this->minimumImageHeight;
    }

    /**
     * Sets the maximum image height for each uploaded file. If `null` is passed, the
     * maximum image height is removed.
     *
     * @param null|number $maximumImageHeight the maximum image height
     * @return  static                     this field
     *
     * @throws  \InvalidArgumentException   if the given mimimum image height is no number or otherwise invalid
     * @throws  \LogicException                  if the form field is not marked as image only
     */
    public function maximumImageHeight($maximumImageHeight = null)
    {
        if (!$this->isImageOnly()) {
            throw new \LogicException(
                "The field '{$this->getId()}' must be image only to set a maximum image height."
            );
        }

        if ($maximumImageHeight !== null) {
            if (!\is_numeric($maximumImageHeight)) {
                throw new \InvalidArgumentException(
                    "Given maximum image height is no int, '" . \gettype($maximumImageHeight) . "' given for field '{$this->getId()}'."
                );
            }

            $minimumImageHeight = $this->getMinimumImageHeight();
            if ($minimumImageHeight !== null && $minimumImageHeight > $maximumImageHeight) {
                throw new \InvalidArgumentException(
                    "Maximum image height ({$maximumImageHeight}) cannot be smaller than minimum image height ({$minimumImageHeight}) for field '{$this->getId()}'."
                );
            }
        }

        $this->maximumImageWidth = $maximumImageHeight;

        return $this;
    }

    /**
     * Returns the maximum image height of each file or `null` if no maximum image height
     * has been set.
     *
     * @return  null|number
     */
    public function getMaximumImageHeight()
    {
        return $this->maximumImageHeight;
    }

    /**
     * Sets the flag for `imageOnly`. This flag indicates whether only images
     * can uploaded via this field. Other file types will be rejected during upload.
     *
     * If set to `true` will also set the acceptable types to `image/*`. If set to
     * false it will clear the acceptable types if they are `image/*`.
     *
     * @param bool $imageOnly
     * @return  static              this field
     *
     * @throws       \InvalidArgumentException         if the field is not set to images only and a minimum/maximum width/height is set
     */
    public function imageOnly($imageOnly = true)
    {
        if (!$imageOnly) {
            if ($this->getMinimumImageWidth() !== null) {
                throw new \InvalidArgumentException(
                    "The field '{$this->getId()}' must be image only, because a minimum image width is set."
                );
            }

            if ($this->getMaximumImageWidth() !== null) {
                throw new \InvalidArgumentException(
                    "The field '{$this->getId()}' must be image only, because a maximum image width is set."
                );
            }

            if ($this->getMinimumImageHeight() !== null) {
                throw new \InvalidArgumentException(
                    "The field '{$this->getId()}' must be image only, because a minimum image height is set."
                );
            }

            if ($this->getMaximumImageHeight() !== null) {
                throw new \InvalidArgumentException(
                    "The field '{$this->getId()}' must be image only, because a maximum image height is set."
                );
            }
        }

        $this->imageOnly = $imageOnly;
        if ($imageOnly) {
            $this->setAcceptableFiles(['image/*']);
        } else {
            // Using == here is safe, because we match a single element array containing
            // a scalar value.
            if ($this->getAcceptableFiles() == ['image/*']) {
                $this->setAcceptableFiles(null);
            }
        }

        return $this;
    }

    /**
     * Sets the flag for `allowSvgImage`. This flag indicates whether
     * SVG images should be handled as image, if the upload field is
     * image only (if this field is not image only, this method will
     * throw an exception).
     *
     * <strong>Attention</strong>: SVG images can contain bad code, therefore do not
     * use this option, outside the acp or check the file whether remote code is contained.
     *
     * @param bool $allowSvgImages
     * @return  static              this field
     *
     * @throws      \BadMethodCallException         if the imageOnly flag isn't set to true
     */
    public function allowSvgImage($allowSvgImages = true)
    {
        if (!$this->isImageOnly()) {
            throw new \BadMethodCallException(
                "Allowing SVG images is only relevant, if the `imageOnly` flag is set to `true` for field '{$this->getId()}'."
            );
        }

        $this->allowSvgImage = $allowSvgImages;

        return $this;
    }

    /**
     * Returns `true` if only images can be uploaded via this field and returns `false` otherwise.
     *
     * @return  bool
     */
    public function isImageOnly()
    {
        return $this->imageOnly;
    }

    /**
     * Returns true, if the field can contain svg images in the image only mode.
     *
     * @return  bool
     */
    public function svgImageAllowed()
    {
        return $this->allowSvgImage;
    }

    /**
     * Specifies acceptable file types. Use null to not specify any restrictions.
     *
     * <strong>Attention</strong>: This feature is used to improve user experience, by removing
     * unacceptable files from the file picker. It does not validate the type of the uploaded
     * file. You are responsible to perform (proper) validation on the server side.
     *
     * Valid values are specified as "Unique file type specifiers":
     * - A case insensitive file extension starting with a dot.
     * - A mime type.
     * - `audio/*`
     * - `image/*`
     * - `video/*`
     *
     * @see         https://developer.mozilla.org/en-US/docs/Web/HTML/Element/input/file#Unique_file_type_specifiers
     * @param string[]|null $acceptableFiles
     * @since       5.3
     */
    public function setAcceptableFiles($acceptableFiles = null)
    {
        $this->acceptableFiles = $acceptableFiles;

        return $this;
    }

    /**
     * Returns the acceptable file types.
     *
     * @return      string[]|null
     * @since       5.3
     */
    public function getAcceptableFiles()
    {
        return $this->acceptableFiles;
    }
}
