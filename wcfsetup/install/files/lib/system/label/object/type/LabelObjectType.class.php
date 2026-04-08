<?php

namespace wcf\system\label\object\type;

/**
 * Label object type.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
class LabelObjectType
{
    /**
     * indentation level
     * @var int
     */
    public $depth = 0;

    /**
     * object type is a category
     * @var bool
     */
    public $isCategory = false;

    /**
     * object type label
     * @var string
     */
    public $label = '';

    /**
     * object id
     * @var int
     */
    public $objectID = 0;

    /**
     * option value
     * @var int
     */
    public $optionValue = 0;

    public function __construct(string $label, int $objectID = 0, int $depth = 0, bool $isCategory = false)
    {
        $this->depth = $depth;
        $this->isCategory = $isCategory;
        $this->label = $label;
        $this->objectID = $objectID;
    }

    /**
     * Returns the label.
     *
     * @return  string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Returns the object id.
     * @return  int
     */
    public function getObjectID()
    {
        return $this->objectID;
    }

    /**
     * Returns true, if object type is a category.
     *
     * @return  bool
     */
    public function isCategory()
    {
        return $this->isCategory;
    }

    /**
     * Returns indentation level.
     *
     * @return  int
     */
    public function getDepth()
    {
        return $this->depth;
    }

    /**
     * Sets option value.
     *
     * @return void
     */
    public function setOptionValue(int $optionValue)
    {
        $this->optionValue = $optionValue;
    }

    /**
     * Returns option value.
     *
     * @return  int
     */
    public function getOptionValue()
    {
        return $this->optionValue;
    }
}
