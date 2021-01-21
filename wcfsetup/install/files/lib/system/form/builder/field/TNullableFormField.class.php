<?php

namespace wcf\system\form\builder\field;

/**
 * Provides default implementations of `INullableFormField` methods.
 *
 * @author  Matthias Schmidt
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Form\Builder\Field
 * @since   5.2
 */
trait TNullableFormField
{
    /**
     * `true` if this field supports `null` as its value and `false` otherwise
     * @var bool
     */
    protected $nullable = false;

    /**
     * Returns `true` if this field supports `null` as its value and returns `false`
     * otherwise.
     *
     * Per default, fields do not support `null` as their value.
     *
     * @return  bool
     */
    public function isNullable()
    {
        return $this->nullable;
    }

    /**
     * Sets whether this field supports `null` as its value and returns this field.
     *
     * @param   bool    $nullable       determines if field supports `null` as its value
     * @return  static              this node
     */
    public function nullable($nullable = true)
    {
        $this->nullable = $nullable;

        return $this;
    }
}
