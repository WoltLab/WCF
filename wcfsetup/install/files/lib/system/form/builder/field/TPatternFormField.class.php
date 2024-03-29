<?php

namespace wcf\system\form\builder\field;

/**
 * Provides default implementations of `IPatternFormField` methods.
 *
 * @author      Matthias Schmidt
 * @copyright   2001-2020 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       5.4
 */
trait TPatternFormField
{
    /** @var ?string */
    protected $pattern;

    /**
     * Returns the `pattern` attribute of the form field.
     *
     * If `null` is returned, no `pattern` attribute will be set.
     */
    public function getPattern(): ?string
    {
        return $this->pattern;
    }

    /**
     * Sets the `pattern` attribute of the form field.
     *
     * If `null` is given, the attribute is unset.
     *
     * @return      static      this form field
     */
    public function pattern(?string $pattern)
    {
        $this->pattern = $pattern;

        return $this;
    }
}
