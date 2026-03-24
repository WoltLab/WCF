<?php

namespace wcf\system\form\element;

use wcf\system\form\IFormElement;
use wcf\system\form\IFormElementContainer;
use wcf\util\StringUtil;

/**
 * Basic implementation for form elements.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class AbstractFormElement implements IFormElement
{
    /**
     * element description or help text
     * @var string
     */
    protected $description = '';

    /**
     * localized error string
     * @var string
     */
    protected $error = '';

    /**
     * element label
     * @var string
     */
    protected $label = '';

    /**
     * FormElementContainer object
     * @var IFormElementContainer
     */
    protected $parent;

    #[\Override]
    public function __construct(IFormElementContainer $parent)
    {
        $this->parent = $parent;
    }

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
    public function getParent()
    {
        return $this->parent;
    }

    #[\Override]
    public function setError(string $error)
    {
        $this->error = $error;
    }

    #[\Override]
    public function getError()
    {
        return $this->error;
    }

    /**
     * Returns class attribute if an error occurred.
     *
     * @return  string
     */
    protected function getErrorClass()
    {
        return $this->getError() ? ' class="formError"' : '';
    }

    /**
     * Returns an error message if occurred.
     *
     * @return  string
     */
    protected function getErrorField()
    {
        return $this->getError() ? '<small class="innerError">' . $this->getError() . '</small>' : '';
    }
}
