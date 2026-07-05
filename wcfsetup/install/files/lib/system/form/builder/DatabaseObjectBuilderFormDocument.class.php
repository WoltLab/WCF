<?php

namespace wcf\system\form\builder;

use wcf\data\DatabaseObjectBuilder;
use wcf\system\form\builder\field\IFormField;

/**
 * Represents a form document whose field values are written into a
 * `DatabaseObjectBuilder` instead of being collected into a parameters array
 * for a database object action.
 *
 * Fields contribute their value by registering a callback via
 * `IFormField::saveValueCallback()`:
 *
 *     $field->saveValueCallback(
 *         static function (DatabaseObjectBuilder $builder, IFormField $formField) {
 *             return $builder->setName($formField->getSaveValue());
 *         }
 *     )
 *
 * @author      Marcel Werk
 * @copyright   2001-2026 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.3
 */
class DatabaseObjectBuilderFormDocument extends FormDocument
{
    /**
     * Applies the save values of all available form fields that have registered
     * a save value callback to the given builder and returns the builder.
     *
     * @param DatabaseObjectBuilder<*> $builder
     * @throws \BadMethodCallException if the method is called before `readValues()` is called
     */
    public function applyValuesToBuilder(DatabaseObjectBuilder $builder): void
    {
        if (!$this->didReadValues()) {
            throw new \BadMethodCallException("Applying values to a builder is only possible after calling 'readValues()'.");
        }

        $this->applyNodeValues($this, $builder);
    }

    /**
     * Recursively applies the save value callbacks of the given node and its
     * children to the builder, mirroring the availability and dependency
     * handling of `DefaultFormDataProcessor`.
     *
     * @param DatabaseObjectBuilder<*> $builder
     */
    protected function applyNodeValues(IFormNode $node, DatabaseObjectBuilder $builder): void
    {
        if (!$node->isAvailable() || !$node->checkDependencies()) {
            return;
        }

        if ($node instanceof IFormParentNode) {
            foreach ($node as $childNode) {
                $this->applyNodeValues($childNode, $builder);
            }
        } elseif ($node instanceof IFormField) {
            $callback = $node->getSaveValueCallback();
            if ($callback !== null) {
                $callback($builder, $node);
            }
        }
    }
}
