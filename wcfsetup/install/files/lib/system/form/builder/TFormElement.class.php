<?php

namespace wcf\system\form\builder;

use wcf\system\WCF;
use wcf\util\HtmlString;

/**
 * Provides default implementations of `IFormElement` methods.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 */
trait TFormElement
{
    use TFormNode;

    /**
     * description of this element
     * @var ?string
     */
    protected $description;

    /**
     * label of this element
     * @var ?string
     */
    protected $label;

    /**
     * Sets the description of this element and returns this element. If `null`
     * is passed, the element description is removed.
     *
     * The description is HTML. A string is a phrase name, resolved with
     * `$variables`, and the resolved phrase is trusted HTML; a string that is
     * no phrase name is used as is. An `HtmlString` is used as is and never
     * resolved as a phrase. Plain text must be wrapped with `HtmlString::fromText()`.
     *
     * @param array<string, mixed> $variables additional variables used when resolving the phrase
     * @return static this element
     *
     * @throws \InvalidArgumentException if variables are given together with an `HtmlString` or `null`
     */
    public function description(HtmlString|string|null $languageItem = null, array $variables = [])
    {
        if ($languageItem === null) {
            if ($variables !== []) {
                throw new \InvalidArgumentException(
                    "Cannot use variables when unsetting description of element '{$this->getId()}'."
                );
            }

            $this->description = null;
        } elseif ($languageItem instanceof HtmlString) {
            if ($variables !== []) {
                throw new \InvalidArgumentException(
                    "Cannot use variables with an HTML string as description of element '{$this->getId()}'."
                );
            }

            $this->description = (string)$languageItem;
        } else {
            $this->description = WCF::getLanguage()->getDynamicVariable($languageItem, $variables);
        }

        return $this;
    }

    /**
     * Returns the description of this element or `null` if no description has been set.
     *
     * @return ?string element description
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the label of this element or `null` if no label has been set.
     *
     * @return ?string element label
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Sets the label of this element and returns this element. If `null` is
     * passed, the element label is removed.
     *
     * The label is HTML. A string is a phrase name, resolved with `$variables`,
     * and the resolved phrase is trusted HTML; a string that is no phrase name
     * is used as is. An `HtmlString` is used as is and never resolved as a
     * phrase. Plain text must be wrapped with `HtmlString::fromText()`.
     *
     * @param array<string, mixed> $variables additional variables used when resolving the phrase
     * @return static this element
     *
     * @throws \InvalidArgumentException if variables are given together with an `HtmlString` or `null`
     */
    public function label(HtmlString|string|null $languageItem = null, array $variables = [])
    {
        if ($languageItem === null) {
            if ($variables !== []) {
                throw new \InvalidArgumentException(
                    "Cannot use variables when unsetting label of element '{$this->getId()}'."
                );
            }

            $this->label = null;
        } elseif ($languageItem instanceof HtmlString) {
            if ($variables !== []) {
                throw new \InvalidArgumentException(
                    "Cannot use variables with an HTML string as label of element '{$this->getId()}'."
                );
            }

            $this->label = (string)$languageItem;
        } else {
            $this->label = WCF::getLanguage()->getDynamicVariable($languageItem, $variables);
        }

        return $this;
    }

    /**
     * Returns `true` if this element requires a label to be set.
     *
     * @return bool
     */
    public function requiresLabel()
    {
        // by default, form elements do not require a label
        return false;
    }
}
