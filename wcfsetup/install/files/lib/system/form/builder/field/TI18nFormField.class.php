<?php

namespace wcf\system\form\builder\field;

use wcf\data\IStorableObject;
use wcf\data\language\item\LanguageItemList;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\exception\InvalidFormFieldValue;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\IFormDocument;
use wcf\system\form\builder\IFormNode;
use wcf\system\language\I18nHandler;
use wcf\system\language\LanguageFactory;
use wcf\system\Regex;
use wcf\system\WCF;
use wcf\util\ArrayUtil;
use wcf\util\StringUtil;

/**
 * Provides default implementations of `II18nFormField` methods and other i18n-related methods.
 *
 * This trait can only to be used in combination with `TFormField`.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 *
 * @mixin   II18nFormField
 */
trait TI18nFormField
{
    /**
     * `true` if this field supports i18n input and `false` otherwise
     * @var bool
     */
    protected $i18n = false;

    /**
     * `true` if this field requires i18n input and `false` otherwise
     * @var bool
     */
    protected $i18nRequired = false;

    /**
     * pattern for the language item used to save the i18n values
     * @var null|string
     */
    protected $languageItemPattern;

    /**
     * Returns additional template variables used to generate the html representation
     * of this node.
     *
     * @return  array       additional template variables
     */
    public function getHtmlVariables()
    {
        if ($this->isI18n()) {
            I18nHandler::getInstance()->assignVariables();

            return [
                'elementIdentifier' => $this->getPrefixedId(),
                'forceSelection' => $this->isI18nRequired(),
            ];
        }

        return [];
    }

    /**
     * Returns the pattern for the language item used to save the i18n values.
     *
     * @return  string              language item pattern
     *
     * @throws  \BadMethodCallException     if i18n is disabled for this field or no language item has been set
     */
    public function getLanguageItemPattern()
    {
        if (!$this->isI18n()) {
            throw new \BadMethodCallException(
                "You can only get the language item pattern for fields with i18n enabled for field '{$this->getId()}'."
            );
        }

        if ($this->languageItemPattern === null) {
            throw new \BadMethodCallException("Language item pattern has not been set for field '{$this->getId()}'.");
        }

        return $this->languageItemPattern;
    }

    /**
     * Returns the field value saved in the database.
     *
     * This method is useful if the actual returned by `getValue()`
     * cannot be stored in the database as-is. If the return value of
     * `getValue()` is, however, the actual value that should be stored
     * in the database, this method is expected to call `getValue()`
     * internally.
     *
     * @return  mixed
     */
    public function getSaveValue()
    {
        if (!$this->hasSaveValue()) {
            return;
        } elseif ($this->getValue() === null && $this instanceof INullableFormField && !$this->isNullable()) {
            return '';
        }

        return parent::getSaveValue();
    }

    /**
     * Returns the value of this field or `null` if no value has been set.
     *
     * @return  mixed
     */
    public function getValue()
    {
        if ($this->isI18n()) {
            if ($this->hasPlainValue()) {
                return I18nHandler::getInstance()->getValue($this->getPrefixedId());
            } elseif ($this->hasI18nValues()) {
                $values = I18nHandler::getInstance()->getValues($this->getPrefixedId());

                // handle legacy values from the past when multilingual values
                // were available
                if (\count(LanguageFactory::getInstance()->getLanguages()) === 1) {
                    if (isset($values[WCF::getLanguage()->languageID])) {
                        return $values[WCF::getLanguage()->languageID];
                    }

                    return \current($values);
                }

                return $values;
            }

            return '';
        }

        return $this->value;
    }

    /**
     * Returns `true` if the current field value is a i18n value and returns `false`
     * otherwise or if no value has been set.
     *
     * @return  bool
     */
    public function hasI18nValues()
    {
        return I18nHandler::getInstance()->hasI18nValues($this->getPrefixedId());
    }

    /**
     * Returns `true` if the current field value is a plain value and returns `false`
     * otherwise or if no value has been set.
     *
     * @return  bool
     */
    public function hasPlainValue()
    {
        return I18nHandler::getInstance()->isPlainValue($this->getPrefixedId());
    }

    /**
     * Returns `true` if this field provides a value that can simply be stored
     * in a column of the database object's database table and returns `false`
     * otherwise.
     *
     * Note: If `false` is returned, this field should probabily add its own
     * `IFormFieldDataProcessor` object to the form document's data processor.
     * A suitable place to add the processor is the `parent()`
     *
     * @return  bool
     */
    public function hasSaveValue()
    {
        return !$this->isI18n() || $this->hasPlainValue();
    }

    /**
     * Sets whether this field is supports i18n input and returns this field.
     *
     * @param bool $i18n determines if field supports i18n input
     * @return  II18nFormField          this field
     */
    public function i18n($i18n = true)
    {
        $this->i18n = $i18n;

        return $this;
    }

    /**
     * @see IFormField::getJavaScriptDataHandlerModule()
     */
    public function getJavaScriptDataHandlerModule(): string
    {
        if ($this->isI18n() && \count(LanguageFactory::getInstance()->getLanguages()) > 1) {
            return 'WoltLabSuite/Core/Form/Builder/Field/ValueI18n';
        } else {
            return $this->javaScriptDataHandlerModule;
        }
    }

    /**
     * Sets whether this field's value must be i18n input and returns this field.
     *
     * If this method sets that the field's value must be i18n input, it also must
     * ensure that i18n support is enabled.
     *
     * @param bool $i18nRequired determines if field value must be i18n input
     * @return  static                  this field
     */
    public function i18nRequired($i18nRequired = true)
    {
        $this->i18nRequired = $i18nRequired;
        $this->i18n();

        return $this;
    }

    /**
     * Returns `true` if this field supports i18n input and returns `false` otherwise.
     * By default, fields do not support i18n input.
     *
     * @return  bool
     */
    public function isI18n()
    {
        return $this->i18n;
    }

    /**
     * Returns `true` if this field's value must be i18n input and returns `false` otherwise.
     * By default, fields do not support i18n input.
     *
     * @return  bool
     */
    public function isI18nRequired()
    {
        return $this->i18nRequired;
    }

    /**
     * Sets the pattern for the language item used to save the i18n values
     * and returns this field.
     *
     * @param string $pattern language item pattern
     * @return  II18nFormField          this field
     *
     * @throws  \BadMethodCallException     if i18n is disabled for this field
     * @throws  \InvalidArgumentException   if the given pattern is invalid
     */
    public function languageItemPattern($pattern)
    {
        if (!$this->isI18n()) {
            throw new \BadMethodCallException(
                "The language item pattern can only be set for fields with i18n enabled for field '{$this->getId()}'."
            );
        }

        if (!Regex::compile($pattern)->isValid()) {
            throw new \InvalidArgumentException("Given pattern is invalid for field '{$this->getId()}'.");
        }

        $this->languageItemPattern = $pattern;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function updatedObject(array $data, IStorableObject $object, $loadValues = true)
    {
        if ($this instanceof IImmutableFormField && $this->isImmutable()) {
            $loadValues = true;
        }

        if ($loadValues && isset($data[$this->getObjectProperty()])) {
            $value = $data[$this->getObjectProperty()];

            if ($this->isI18n()) {
                // do not use `I18nHandler::setOptions()` because then `I18nHandler` only
                // reads the values when assigning the template variables and the values
                // are not available in this class via `getValue()`
                $this->setStringValue($value);
            } else {
                $this->value = $value;
            }
        }

        return $this;
    }

    /**
     * Is called once after all nodes have been added to the document this node belongs to.
     *
     * This method enables this node to perform actions that require the whole document having
     * finished constructing itself and every parent-child relationship being established.
     *
     * @return  IFormNode           this node
     *
     * @throws  \BadMethodCallException     if this node has already been populated
     */
    public function populate()
    {
        parent::populate();

        if ($this->isI18n()) {
            I18nHandler::getInstance()->unregister($this->getPrefixedId());
            I18nHandler::getInstance()->register($this->getPrefixedId());

            /** @var IFormDocument $document */
            $document = $this->getDocument();
            $document->getDataHandler()->addProcessor(new CustomFormDataProcessor(
                'i18n',
                function (IFormDocument $document, array $parameters) {
                    if ($this->checkDependencies() && $this->hasI18nValues()) {
                        $parameters[$this->getObjectProperty() . '_i18n'] = $this->getValue();
                    }

                    return $parameters;
                }
            ));
        }

        return $this;
    }

    /**
     * Reads the value of this field from request data and return this field.
     *
     * @return  IFormField  this field
     */
    public function readValue()
    {
        if ($this->isI18n()) {
            I18nHandler::getInstance()->readValues($this->getDocument()->getRequestData());
        } elseif ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
            $value = $this->getDocument()->getRequestData($this->getPrefixedId());

            if (\is_string($value)) {
                $this->value = StringUtil::trim($value);
            }
        }

        return $this;
    }

    /**
     * Sets the value of this form field based on the given value.
     * If the value is a language item matching the language item pattern,
     * the relevant language items are loaded and their values are used as
     * field values.
     *
     * @param string $value set value
     */
    protected function setStringValue($value)
    {
        if (Regex::compile('^' . $this->getLanguageItemPattern() . '$')->match($value)) {
            $languageItemList = new LanguageItemList();
            $languageItemList->getConditionBuilder()->add('languageItem = ?', [$value]);
            $languageItemList->readObjects();

            $values = [];
            foreach ($languageItemList as $languageItem) {
                $values[$languageItem->languageID] = $languageItem->languageItemValue;
            }

            if ($values === []) {
                // The value should be an i18n value but the phrases are missing
                // for an unknown reason. Recovery by forcing the value to be
                // treated as plain text. See https://github.com/WoltLab/WCF/issues/5622
                I18nHandler::getInstance()->setValue($this->getPrefixedId(), $value, true);
            } else {
                I18nHandler::getInstance()->setValues($this->getPrefixedId(), $values);
            }
        } else {
            I18nHandler::getInstance()->setValue($this->getPrefixedId(), $value, !$this->isI18nRequired());
        }
    }

    /**
     * Sets the value of this field and returns this field.
     *
     * @param string|string[] $value new field value
     * @return  static                  this field
     *
     * @throws  \InvalidArgumentException       if the given value is of an invalid type or otherwise is invalid
     */
    public function value($value)
    {
        if ($this->isI18n()) {
            if (\is_string($value) || \is_numeric($value)) {
                $this->setStringValue($value);
            } elseif (\is_array($value)) {
                if (!empty($value)) {
                    I18nHandler::getInstance()->setValues($this->getPrefixedId(), $value);
                }
            } else {
                throw new InvalidFormFieldValue($this, 'string/number/array', \gettype($value));
            }
        } else {
            if (!\is_string($value) && !\is_numeric($value)) {
                throw new InvalidFormFieldValue($this, 'string/number', \gettype($value));
            }

            return parent::value($value);
        }

        return $this;
    }

    /**
     * Validates the node.
     *
     * Note: A `IFormParentNode` object may only return `true` if all of its child
     * nodes are valid. A `IFormField` object is valid if its value is valid.
     */
    public function validate()
    {
        // If i18n is required for a non-required field and the field is
        // empty in all languages, `I18nHandler::validateValue()` will mark
        // as invalid even though it is a valid state for this form field,
        // thus the additional condition.
        if ($this->isI18n() && (!empty(ArrayUtil::trim($this->getValue())) || $this->isRequired())) {
            if (
                !I18nHandler::getInstance()->validateValue(
                    $this->getPrefixedId(),
                    $this->isI18nRequired(),
                    !$this->isRequired()
                )
            ) {
                if ($this->hasPlainValue()) {
                    $this->addValidationError(new FormFieldValidationError('empty'));
                } else {
                    $this->addValidationError(new FormFieldValidationError('multilingual'));
                }
            }
        }
    }
}
