<?php

namespace wcf\system\condition\type;

use wcf\system\form\builder\container\IFormContainer;
use wcf\system\form\builder\field\IFormField;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 *
 * @template TFilter
 */
interface IConditionType
{
    /**
     * Returns the form field for this condition type.
     */
    public function getFormField(string $id): IFormField|IFormContainer;

    /**
     * Returns the identifier of this condition type.
     */
    public function getIdentifier(): string;

    /**
     * Returns the label of this condition type.
     */
    public function getLabel(): string;

    /**
     * Set the filter value for this condition type.
     *
     * @param TFilter $filter
     */
    public function setFilter(mixed $filter): void;

    /**
     * Get the name of the category for this condition type.
     * All condition types with the same category are grouped together.
     * The language variable for the category name is `wcf.condition.category.<category>`.
     */
    public function getCategory(): string;
}
