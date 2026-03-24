<?php

namespace wcf\system\html;

use wcf\data\object\type\ObjectTypeCache;
use wcf\system\exception\InvalidObjectTypeException;

/**
 * Default implementation for html processors.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2019 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @phpstan-type Context array{objectType: string, objectTypeID: int, objectID: int}
 */
abstract class AbstractHtmlProcessor implements IHtmlProcessor
{
    /**
     * message context data
     * @var Context
     */
    protected $context = [
        'objectType' => '',
        'objectTypeID' => 0,
        'objectID' => 0,
    ];

    /**
     * Sets the message context data.
     *
     * @param string $objectType object type identifier
     * @param int $objectID object id
     * @return void
     * @throws InvalidObjectTypeException
     */
    public function setContext($objectType, $objectID)
    {
        $objectTypeID = ObjectTypeCache::getInstance()->getObjectTypeIDByName('com.woltlab.wcf.message', $objectType);
        if ($objectTypeID === null) {
            throw new InvalidObjectTypeException($objectType, 'com.woltlab.wcf.message');
        }

        $this->context = [
            'objectType' => $objectType,
            'objectTypeID' => $objectTypeID,
            'objectID' => $objectID,
        ];
    }

    /**
     * Returns the message context data.
     *
     * @return Context message context data
     */
    public function getContext()
    {
        return $this->context;
    }
}
