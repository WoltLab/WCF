<?php

namespace wcf\system\form\builder\field\tag;

use wcf\data\IStorableObject;
use wcf\data\tag\Tag;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\exception\InvalidFormFieldValue;
use wcf\system\form\builder\field\AbstractFormField;
use wcf\system\form\builder\field\IAttributeFormField;
use wcf\system\form\builder\field\TDefaultIdFormField;
use wcf\system\form\builder\field\TInputAttributeFormField;
use wcf\system\form\builder\IFormDocument;
use wcf\system\form\builder\IObjectTypeFormNode;
use wcf\system\form\builder\TObjectTypeFormNode;
use wcf\system\tagging\TagEngine;
use wcf\util\ArrayUtil;

/**
 * Implementation of a form field for tags.
 *
 * This field uses the `wcf.tagging.tags` and `wcf.tagging.tags.description` language
 * item as the default form field label and description, respectively. The default id
 * of fields of this class is `tags`.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
final class TagFormField extends AbstractFormField implements IAttributeFormField, IObjectTypeFormNode
{
    use TInputAttributeFormField;
    use TDefaultIdFormField;
    use TObjectTypeFormNode;

    /**
     * @inheritDoc
     */
    protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Tag';

    /**
     * @inheritDoc
     */
    protected $templateName = 'shared_tagFormField';

    /**
     * Creates a new instance of `TagFormField`.
     */
    public function __construct()
    {
        $this->description('wcf.tagging.tags.description');
        $this->label('wcf.tagging.tags');
    }

    /**
     * @inheritDoc
     */
    public function getObjectTypeDefinition()
    {
        return 'com.woltlab.wcf.tagging.taggableObject';
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
    public function updatedObject(array $data, IStorableObject $object, $loadValues = true)
    {
        if ($loadValues) {
            if (isset($data[$this->getObjectProperty()])) {
                $this->value($data[$this->getObjectProperty()]);
            } else {
                $objectID = $object->{$object::getDatabaseTableIndexName()};

                if ($objectID === null) {
                    throw new \UnexpectedValueException(
                        "Cannot read object id from object of class '" . \get_class($object) . "' for field '{$this->getId()}'."
                    );
                }

                if ($this->getObjectType() === null) {
                    throw new \UnexpectedValueException("Missing taggable object type for field '{$this->getId()}'.");
                }

                $languageIDs = [];

                /** @noinspection PhpUndefinedFieldInspection */
                if (isset($data['languageID'])) {
                    $languageIDs[] = $data['languageID'];
                }

                $tags = TagEngine::getInstance()->getObjectTags(
                    $this->getObjectType()->objectType,
                    $objectID,
                    $languageIDs
                );

                $this->value = [];
                foreach ($tags as $tag) {
                    $this->value[] = $tag->name;
                }
            }
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
            'tags',
            function (IFormDocument $document, array $parameters) {
                if ($this->checkDependencies() && $this->getValue() !== null && !empty($this->getValue())) {
                    $parameters[$this->getObjectProperty()] = $this->getValue();
                }

                return $parameters;
            }
        ));

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function readValue()
    {
        if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
            $value = $this->getDocument()->getRequestData($this->getPrefixedId());

            if (\is_array($value)) {
                $this->value = ArrayUtil::trim($value);
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function value($value)
    {
        if (!\is_array($value)) {
            throw new InvalidFormFieldValue($this, 'array', \gettype($value));
        }

        $stringTags = [];
        $stringValues = null;

        foreach ($value as $tag) {
            if (\is_string($tag)) {
                if ($stringValues === null) {
                    $stringValues = true;
                }

                if ($stringValues === false) {
                    throw new \InvalidArgumentException(
                        "Given value array contains mixed values, all values have to be either strings or `" . Tag::class . "` objects for field '{$this->getId()}'."
                    );
                }

                $stringTags[] = $tag;
            } elseif ($tag instanceof Tag) {
                if ($stringValues === null) {
                    $stringValues = false;
                }

                if ($stringValues === true) {
                    throw new \InvalidArgumentException(
                        "Given value array contains mixed values, all values have to be either strings or `" . Tag::class . "` objects for field '{$this->getId()}'."
                    );
                }

                $stringTags[] = $tag->name;
            } else {
                throw new \InvalidArgumentException(
                    "Given value array contains invalid value of type " . \gettype($tag) . " for field '{$this->getId()}'."
                );
            }
        }

        return parent::value($stringTags);
    }

    /**
     * @inheritDoc
     */
    protected static function getDefaultId()
    {
        return 'tags';
    }
}
