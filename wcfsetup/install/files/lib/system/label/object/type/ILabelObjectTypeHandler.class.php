<?php

namespace wcf\system\label\object\type;

use wcf\data\object\type\ObjectType;

/**
 * Every label object type handler has to implement this interface.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
interface ILabelObjectTypeHandler
{
    /**
     * Provides a container object that groups all objects that can be assigned
     * a label group.
     *
     * Implementations must not rely on any state provided by `getObjectTypeID()`.
     *
     * @since 6.2
     */
    public function getContainerForObjectType(ObjectType $objectType): ?LabelObjectTypeContainer;

    /**
     * Sets object type id.
     *
     * @param int $objectTypeID
     * @return void
     * @deprecated 6.2 Use `getContainerForObjectType()` instead.
     */
    public function setObjectTypeID($objectTypeID);

    /**
     * Returns object type id.
     *
     * @return  int
     * @deprecated 6.2 Use `getContainerForObjectType()` instead.
     */
    public function getObjectTypeID();

    /**
     * Returns a label object type container.
     *
     * @return  LabelObjectTypeContainer
     * @deprecated 6.2 Use `getContainerForObjectType()` instead.
     */
    public function getContainer();

    /**
     * Performs save actions.
     *
     * @return void
     */
    public function save();
}
