<?php

namespace wcf\system\form\element;

use wcf\util\StringUtil;

/**
 * Basic implementation for named form elements.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class AbstractNamedFormElement extends AbstractFormElement
{
    /**
     * element description
     * @var string
     */
    protected $description = '';

    /**
     * element name
     * @var string
     */
    protected $name = '';

    /**
     * element value
     * @var string
     */
    protected $value = '';

    /**
     * Sets element description.
     *
     * @return void
     */
    #[\Override]
    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    /**
     * Returns element description.
     *
     * @return string
     */
    #[\Override]
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets element name.
     *
     * @return void
     */
    public function setName(string $name)
    {
        $this->name = StringUtil::trim($name);
    }

    /**
     * Returns element name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets element value.
     *
     * @return void
     */
    public function setValue(string $value)
    {
        if (!\is_string($value)) {
            exit(\print_r($value, true));
        }
        $this->value = StringUtil::trim($value);
    }

    /**
     * Returns element value.
     *
     * @return  string
     */
    public function getValue()
    {
        return $this->value;
    }
}
