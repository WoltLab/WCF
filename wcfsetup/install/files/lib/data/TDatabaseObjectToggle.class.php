<?php

namespace wcf\data;

/**
 * Default implementation of the `IToggleAction` interface.
 *
 * @author  Florian Gail
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.2
 *
 * @mixin AbstractDatabaseObjectAction<DatabaseObject, DatabaseObjectEditor<DatabaseObject>>
 */
trait TDatabaseObjectToggle
{
    /**
     * Validates the "toggle" action.
     *
     * @return void
     */
    public function validateToggle()
    {
        $this->validateUpdate();
    }

    /**
     * Toggles the "isDisabled" status of the relevant objects.
     *
     * @return void
     */
    public function toggle()
    {
        foreach ($this->getObjects() as $object) {
            $object->update([
                // @phpstan-ignore property.notFound
                'isDisabled' => $object->isDisabled !== 0 ? 0 : 1,
            ]);
        }
    }
}
