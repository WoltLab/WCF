<?php

namespace wcf\system\form\container;

use wcf\system\form\element\AbstractNamedFormElement;
use wcf\system\form\IFormElement;
use wcf\system\form\IFormElementContainer;
use wcf\util\StringUtil;

/**
 * Basic implementation for form element containers.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class AbstractFormElementContainer implements IFormElementContainer
{
    /**
     * list of IFormElement objects
     * @var IFormElement[]
     */
    protected $children = [];

    /**
     * element description or help text
     * @var string
     */
    protected $description = '';

    /**
     * element label
     * @var string
     */
    protected $label = '';

    #[\Override]
    public function setDescription(string $description)
    {
        $this->description = StringUtil::trim($description);
    }

    #[\Override]
    public function getDescription()
    {
        return $this->description;
    }

    #[\Override]
    public function setLabel(string $label)
    {
        $this->label = StringUtil::trim($label);
    }

    #[\Override]
    public function getLabel()
    {
        return $this->label;
    }

    #[\Override]
    public function appendChild(IFormElement $element)
    {
        $this->children[] = $element;
    }

    #[\Override]
    public function prependChild(IFormElement $element)
    {
        \array_unshift($this->children, $element);
    }

    #[\Override]
    public function getChildren()
    {
        return $this->children;
    }

    #[\Override]
    public function getValue(string $key)
    {
        foreach ($this->children as $element) {
            if ($element instanceof AbstractNamedFormElement) {
                if ($element->getName() == $key) {
                    return $element->getValue();
                }
            }
        }
    }

    #[\Override]
    public function handleRequest(array $variables)
    {
        foreach ($this->children as $element) {
            if (!($element instanceof AbstractNamedFormElement)) {
                continue;
            }

            if (isset($variables[$element->getName()])) {
                $element->setValue($variables[$element->getName()]);
            }
        }
    }

    #[\Override]
    public function setError(string $name, string $error)
    {
        foreach ($this->children as $element) {
            if (!($element instanceof AbstractNamedFormElement)) {
                continue;
            }

            if ($element->getName() == $name) {
                $element->setError($error);
            }
        }
    }
}
