<?php

namespace wcf\system\label\object\type;

use wcf\data\object\type\ObjectType;
use wcf\system\SingletonFactory;

/**
 * Abstract implementation of a label object type handler.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
abstract class AbstractLabelObjectTypeHandler extends SingletonFactory implements ILabelObjectTypeHandler
{
    /**
     * label object type container
     * @var LabelObjectTypeContainer
     */
    public $container;

    /**
     * object type id
     * @var int
     */
    public $objectTypeID = 0;

    /**
     * @inheritDoc
     */
    public function setObjectTypeID($objectTypeID)
    {
        $this->objectTypeID = $objectTypeID;
    }

    /**
     * @inheritDoc
     */
    public function getObjectTypeID()
    {
        return $this->objectTypeID;
    }

    /**
     * @inheritDoc
     */
    public function getContainer()
    {
        return $this->container;
    }

    #[\Override]
    public function getContainerForObjectType(ObjectType $objectType): ?LabelObjectTypeContainer
    {
        // This exists for backwards-compatibility only; Implementations are
        // expected to implement this method themselves.
        return null;
    }
}
