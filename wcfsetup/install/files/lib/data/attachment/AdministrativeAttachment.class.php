<?php

namespace wcf\data\attachment;

use wcf\data\DatabaseObjectDecorator;
use wcf\data\IUserContent;
use wcf\data\object\type\ObjectTypeCache;
use wcf\system\attachment\IAttachmentObjectType;

/**
 * Represents an attachment.
 *
 * @author  Marcel Werk
 * @copyright   2001-2019 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @deprecated 6.3 No longer in use.
 *
 * @mixin   Attachment
 * @property-read string $username
 * @extends DatabaseObjectDecorator<Attachment>
 */
class AdministrativeAttachment extends DatabaseObjectDecorator
{
    /**
     * @inheritDoc
     */
    protected static $baseClass = Attachment::class;

    /**
     * container object
     * @var ?IUserContent
     */
    protected $containerObject;

    /**
     * true if container object has been loaded
     * @var bool
     */
    protected $containerObjectLoaded = false;

    /**
     * Returns the container object of this attachment.
     *
     * @return ?IUserContent
     */
    public function getContainerObject()
    {
        if (!$this->containerObjectLoaded) {
            $this->containerObjectLoaded = true;

            $objectType = ObjectTypeCache::getInstance()->getObjectType($this->objectTypeID);
            $processor = $objectType->getProcessor();
            \assert($processor instanceof IAttachmentObjectType);
            $this->containerObject = $processor->getObject($this->objectID);
        }

        return $this->containerObject;
    }
}
