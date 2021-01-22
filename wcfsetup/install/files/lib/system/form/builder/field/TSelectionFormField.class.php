<?php

namespace wcf\system\form\builder\field;

use wcf\data\DatabaseObjectList;
use wcf\data\IObjectTreeNode;
use wcf\data\ITitledObject;
use wcf\system\WCF;
use wcf\util\ClassUtil;

/**
 * Provides default implementations of `ISelectionFormField` methods.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Form\Builder\Field
 * @since   5.2
 */
trait TSelectionFormField
{
    /**
     * structured options array used to generate the form field output
     * @var null|array
     */
    protected $nestedOptions;

    /**
     * possible options to select
     * @var null|array
     */
    protected $options;

    /**
     * Returns a structured array that can be used to generate the form field output.
     *
     * Array elements are `value`, `label`, and `depth`.
     *
     * @return  array
     * @throws  \BadMethodCallException     if nested options are not supported
     */
    public function getNestedOptions()
    {
        if (!$this->supportsNestedOptions()) {
            throw new \BadMethodCallException("Nested options are not supported.");
        }

        return $this->nestedOptions;
    }

    /**
     * Returns the selectable options of this field.
     *
     * @return  array
     *
     * @throws  \BadMethodCallException     if no options have been set
     */
    public function getOptions()
    {
        if ($this->options === null) {
            throw new \BadMethodCallException("No options have been set.");
        }

        return $this->options;
    }

    /**
     * Returns `true` if this node is available and returns `false` otherwise.
     *
     * If the node's availability has not been explicitly set, `true` is returned.
     *
     * @return  bool
     *
     * @see     IFormNode::available()
     */
    public function isAvailable()
    {
        // selections without any possible values are not available
        return !empty($this->options) && parent::isAvailable();
    }

    /**
     * Sets the possible options of this field and returns this field.
     *
     * Note: If PHP considers the key of the first selectable option to be empty
     * and the this field is nullable, then the save value of that key is `null`
     * instead of the given empty value.
     *
     * If a `callable` is passed, it is expected that it either returns an array
     * or a `DatabaseObjectList` object.
     *
     * If a `DatabaseObjectList` object is passed and `$options->objectIDs === null`,
     * `$options->readObjects()` is called so that the `readObjects()` does not have
     * to be called by the API user.
     *
     * If nested options are passed, the given options must be a array or a
     * callable returning an array. Each array value must be an array with the
     * following entries: `depth`, `label`, and `value`.
     *
     * @param   array|callable|DatabaseObjectList   $options    selectable options or callable returning the options
     * @param   bool                    $nestedOptions  is `true` if the passed options are nested options
     * @param   bool                    $labelLanguageItems is `true` if the labels should be treated as language items if possible
     * @return  static                  this field
     *
     * @throws  \InvalidArgumentException   if given options are no array or callable or otherwise invalid
     * @throws  \UnexpectedValueException   if callable does not return an array
     */
    public function options($options, $nestedOptions = false, $labelLanguageItems = true)
    {
        if ($nestedOptions) {
            if (!\is_array($options) && !($options instanceof \Traversable) && !\is_callable($options)) {
                throw new \InvalidArgumentException(
                    "The given nested options are neither iterable nor a callable, " . \gettype($options) . " given."
                );
            }
        } elseif (!\is_array($options) && !($options instanceof \Traversable) && !\is_callable($options)) {
            throw new \InvalidArgumentException(
                "The given options are neither iterable nor a callable, " . \gettype($options) . " given."
            );
        }

        if (\is_callable($options)) {
            $options = $options();

            if ($nestedOptions) {
                if (!\is_array($options) && !($options instanceof \Traversable)) {
                    throw new \UnexpectedValueException("The nested options callable is expected to return an iterable value, " . \gettype($options) . " returned.");
                }
            } elseif (!\is_array($options) && !($options instanceof \Traversable)) {
                throw new \UnexpectedValueException("The options callable is expected to return an iterable value, " . \gettype($options) . " returned.");
            }

            return $this->options($options, $nestedOptions, $labelLanguageItems);
        } elseif ($options instanceof \Traversable) {
            // automatically read objects
            if ($options instanceof DatabaseObjectList && $options->objectIDs === null) {
                $options->readObjects();
            }

            if ($nestedOptions) {
                $collectedOptions = [];
                foreach ($options as $key => $object) {
                    if (!($object instanceof IObjectTreeNode)) {
                        throw new \InvalidArgumentException(
                            "Nested traversable options must implement '" . IObjectTreeNode::class . "'."
                        );
                    }

                    $collectedOptions[] = [
                        'depth' => $object->getDepth() - 1,
                        'isSelectable' => true,
                        'label' => $object,
                        'value' => $object->getObjectID(),
                    ];
                }

                $options = $collectedOptions;
            } else {
                $options = \iterator_to_array($options);
            }
        }

        $this->options = [];
        if ($nestedOptions) {
            foreach ($options as $key => &$option) {
                if (!\is_array($option)) {
                    throw new \InvalidArgumentException("Nested option with key '{$key}' has is no array.");
                }

                // check if all required elements exist
                foreach (['label', 'value', 'depth'] as $entry) {
                    if (!isset($option[$entry])) {
                        throw new \InvalidArgumentException("Nested option with key '{$key}' has no {$entry} entry.");
                    }
                }

                // validate label
                if (\is_object($option['label'])) {
                    if (\method_exists($option['label'], '__toString')) {
                        $option['label'] = (string)$option['label'];
                    } elseif (
                        $option['label'] instanceof ITitledObject
                        || ClassUtil::isDecoratedInstanceOf($option['label'], ITitledObject::class)
                    ) {
                        $option['label'] = $option['label']->getTitle();
                    } else {
                        throw new \InvalidArgumentException("Nested option with key '{$key}' contain invalid label of type " . \gettype($option['label']) . ".");
                    }
                } elseif (!\is_string($option['label']) && !\is_numeric($option['label'])) {
                    throw new \InvalidArgumentException("Nested option with key '{$key}' contain invalid label of type " . \gettype($option['label']) . ".");
                }

                // resolve language item for label
                if (
                    $labelLanguageItems
                    && \preg_match('~^([a-zA-Z0-9-_]+\.){2,}[a-zA-Z0-9-_]+$~', (string)$option['label'])
                ) {
                    $option['label'] = WCF::getLanguage()->getDynamicVariable($option['label']);
                }

                // validate value
                if (!\is_string($option['value']) && !\is_numeric($option['value'])) {
                    throw new \InvalidArgumentException("Nested option with key '{$key}' contain invalid value of type " . \gettype($option['label']) . ".");
                } elseif (isset($this->options[$option['value']])) {
                    throw new \InvalidArgumentException(
                        "Options values must be unique, but '{$option['value']}' appears at least twice as value."
                    );
                }

                // validate depth
                if (!\is_int($option['depth'])) {
                    throw new \InvalidArgumentException(
                        "Depth of nested option with key '{$key}' is no integer, " . \gettype($options) . " given."
                    );
                }
                if ($option['depth'] < 0) {
                    throw new \InvalidArgumentException("Depth of nested option with key '{$key}' is negative.");
                }

                // set default value of `isSelectable`
                $option['isSelectable'] = $option['isSelectable'] ?? true;

                // save value
                if ($option['isSelectable']) {
                    $this->options[$option['value']] = $option['label'];
                }
            }
            unset($option);

            $this->nestedOptions = $options;
        } else {
            foreach ($options as $value => $label) {
                if (\is_array($label)) {
                    throw new \InvalidArgumentException(
                        "Non-nested options must not contain any array. Array given for value '{$value}'."
                    );
                }

                if (\is_object($label)) {
                    if (\method_exists($label, '__toString')) {
                        $label = (string)$label;
                    } elseif (
                        $label instanceof ITitledObject
                        || ClassUtil::isDecoratedInstanceOf($label, ITitledObject::class)
                    ) {
                        $label = $label->getTitle();
                    } else {
                        throw new \InvalidArgumentException(
                            "Options contain invalid label of type " . \gettype($label) . "."
                        );
                    }
                } elseif (!\is_string($label) && !\is_numeric($label)) {
                    throw new \InvalidArgumentException(
                        "Options contain invalid label of type " . \gettype($label) . "."
                    );
                }

                if (isset($this->options[$value])) {
                    throw new \InvalidArgumentException(
                        "Options values must be unique, but '{$value}' appears at least twice as value."
                    );
                }

                // resolve language item for label
                if ($labelLanguageItems && \preg_match('~^([a-zA-Z0-9-_]+\.){2,}[a-zA-Z0-9-_]+$~', (string)$label)) {
                    $label = WCF::getLanguage()->getDynamicVariable($label);
                }

                $this->options[$value] = $label;
            }

            // ensure that `$this->nestedOptions` is always populated
            // for form field that support nested options
            if ($this->supportsNestedOptions()) {
                $this->nestedOptions = [];

                foreach ($this->options as $value => $label) {
                    $this->nestedOptions[] = [
                        'depth' => 0,
                        'isSelectable' => true,
                        'label' => $label,
                        'value' => $value,
                    ];
                }
            }
        }

        if ($this->nestedOptions === null) {
            $this->nestedOptions = [];
        }

        return $this;
    }

    /**
     * Returns `true` if the field class supports nested options and `false` otherwise.
     *
     * @return  bool
     */
    public function supportsNestedOptions()
    {
        return true;
    }
}
