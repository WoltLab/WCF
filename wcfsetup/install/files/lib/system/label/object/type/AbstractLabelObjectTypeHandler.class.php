<?php

namespace wcf\system\label\object\type;

use wcf\system\SingletonFactory;

/**
 * Abstract implementation of a label object type handler.
 *
 * @author  Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\System\Label\Object\Type
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
}
