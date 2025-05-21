<?php

namespace wcf\system\condition\type;

use wcf\system\form\builder\field\IFormField;

/**
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
interface IConditionType
{
    /**
     * Returns the form field for this condition type.
     */
    public function getFormField(string $id): IFormField;

    /**
     * Returns the identifier of this condition type.
     */
    public function getIdentifier(): string;

    /**
     * Returns the label of this condition type.
     */
    public function getLabel(): string;
}
